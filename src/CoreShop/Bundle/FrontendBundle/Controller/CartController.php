<?php
/**
 * CoreShop.
 *
 * This source file is subject to the GNU General Public License version 3 (GPLv3)
 * For the full copyright and license information, please view the LICENSE.md and gpl-3.0.txt
 * files that are distributed with this source code.
 *
 * @copyright  Copyright (c) CoreShop GmbH (https://www.coreshop.org)
 * @license    https://www.coreshop.org/license     GNU General Public License version 3 (GPLv3)
 */

declare(strict_types=1);

namespace CoreShop\Bundle\FrontendBundle\Controller;

use CoreShop\Bundle\OrderBundle\DTO\AddToCartInterface;
use CoreShop\Bundle\OrderBundle\Factory\AddToCartFactoryInterface;
use CoreShop\Bundle\OrderBundle\Form\Type\AddToCartType;
use CoreShop\Bundle\OrderBundle\Form\Type\CartType;
use CoreShop\Bundle\OrderBundle\Form\Type\ShippingCalculatorType;
use CoreShop\Component\Address\Model\AddressInterface;
use CoreShop\Component\Core\Order\Modifier\CartItemQuantityModifier;
use CoreShop\Component\Order\Cart\CartModifierInterface;
use CoreShop\Component\Order\Cart\Rule\CartPriceRuleProcessorInterface;
use CoreShop\Component\Order\Cart\Rule\CartPriceRuleUnProcessorInterface;
use CoreShop\Component\Order\Context\CartContextInterface;
use CoreShop\Component\Order\Manager\CartManagerInterface;
use CoreShop\Component\Order\Model\CartPriceRuleVoucherCodeInterface;
use CoreShop\Component\Order\Model\OrderInterface;
use CoreShop\Component\Order\Model\OrderItemInterface;
use CoreShop\Component\Order\Model\PurchasableInterface;
use CoreShop\Component\Order\Repository\CartPriceRuleVoucherRepositoryInterface;
use CoreShop\Component\Shipping\Calculator\TaxedShippingCalculatorInterface;
use CoreShop\Component\Shipping\Resolver\CarriersResolverInterface;
use CoreShop\Component\Tracking\Tracker\TrackerInterface;
use Symfony\Component\EventDispatcher\GenericEvent;
use Symfony\Component\Form\FormError;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Validator\ConstraintViolationListInterface;

class CartController extends FrontendController
{
    public function widgetAction(Request $request): Response
    {
        return $this->render($this->templateConfigurator->findTemplate('Cart/_widget.html'), [
            'cart' => $this->getCart(),
        ]);
    }

    public function summaryAction(Request $request): Response
    {
        $cart = $this->getCart();
        $form = $this->get('form.factory')->createNamed('coreshop', CartType::class, $cart);
        $form->handleRequest($request);

        if (in_array($request->getMethod(), ['POST', 'PUT', 'PATCH']) && $form->isSubmitted() && $form->isValid()) {
            $cart = $form->getData();
            $code = $form->get('cartRuleCoupon')->getData();

            if ($code) {
                $voucherCode = $this->getCartPriceRuleVoucherRepository()->findByCode($code);

                if (!$voucherCode instanceof CartPriceRuleVoucherCodeInterface) {
                    $this->addFlash('error', $this->get('translator')->trans('coreshop.ui.error.voucher.not_found'));

                    return $this->render($this->templateConfigurator->findTemplate('Cart/summary.html'), [
                        'cart' => $this->getCart(),
                        'form' => $form->createView(),
                    ]);
                }

                $priceRule = $voucherCode->getCartPriceRule();

                if ($this->getCartPriceRuleProcessor()->process($cart, $priceRule, $voucherCode)) {
                    $this->getCartManager()->persistCart($cart);
                    $this->addFlash('success', $this->get('translator')->trans('coreshop.ui.success.voucher.stored'));
                } else {
                    $this->addFlash('error', $this->get('translator')->trans('coreshop.ui.error.voucher.invalid'));
                }
            } else {
                $this->addFlash('success', $this->get('translator')->trans('coreshop.ui.cart_updated'));
            }

            $this->get('event_dispatcher')->dispatch(new GenericEvent($cart), 'coreshop.cart.update');
            $this->getCartManager()->persistCart($cart);
        } else {
            if ($cart->getId()) {
                $cart = $this->get('coreshop.repository.order')->forceFind($cart->getId());
            }
        }

        return $this->render($this->templateConfigurator->findTemplate('Cart/summary.html'), [
            'cart' => $cart,
            'form' => $form->createView(),
        ]);
    }

    public function shipmentCalculationAction(Request $request): Response
    {
        $cart = $this->getCart();
        $form = $this->get('form.factory')->createNamed('coreshop', ShippingCalculatorType::class, null, [
                'action' => $this->generateUrl('coreshop_cart_check_shipment'),
            ]);

        $availableCarriers = [];
        $form->handleRequest($request);

        //check if there is a shipping calculation request
        if (in_array($request->getMethod(), ['POST', 'PUT', 'PATCH']) && $form->isSubmitted() && $form->isValid()) {
            $shippingCalculatorFormData = $form->getData();
            $carrierPriceCalculator = $this->get(TaxedShippingCalculatorInterface::class);
            $carriersResolver = $this->get(CarriersResolverInterface::class);

            /** @var AddressInterface $virtualAddress */
            $virtualAddress = $this->get('coreshop.factory.address')->createNew();
            $virtualAddress->setCountry($shippingCalculatorFormData['country']);
            $virtualAddress->setPostcode($shippingCalculatorFormData['zip']);

            $carriers = $carriersResolver->resolveCarriers($cart, $virtualAddress);
            foreach ($carriers as $carrier) {
                $price = $carrierPriceCalculator->getPrice($carrier, $cart, $virtualAddress);
                $priceWithoutTax = $carrierPriceCalculator->getPrice($carrier, $cart, $virtualAddress, false);
                $availableCarriers[] = [
                    'name' => $carrier->getTitle(),
                    'isFreeShipping' => $price === 0,
                    'price' => $price,
                    'priceWithoutTax' => $priceWithoutTax,
                    'data' => $carrier,
                ];
            }
            uasort($availableCarriers, function ($a, $b) {
                return $a['price'] > $b['price'];
            });
        }

        return $this->render($this->templateConfigurator->findTemplate('Cart/ShipmentCalculator/_widget.html'), [
            'cart' => $cart,
            'form' => $form->createView(),
            'availableCarriers' => $availableCarriers,
        ]);
    }

    public function addItemAction(Request $request): Response
    {
        $redirect = $this->getParameterFromRequest($request, '_redirect', $this->generateUrl('coreshop_index'));

        $product = $this->get('coreshop.repository.stack.purchasable')->find($this->getParameterFromRequest($request, 'product'));

        if (!$product instanceof PurchasableInterface) {
            if ($request->isXmlHttpRequest()) {
                return new JsonResponse([
                    'success' => false,
                ]);
            }

            return $this->redirect($redirect);
        }

        $cartItem = $this->get('coreshop.factory.order_item')->createWithPurchasable($product);

        $this->getQuantityModifer()->modify($cartItem, 1);

        $addToCart = $this->createAddToCart($this->getCart(), $cartItem);

        $form = $this->get('form.factory')->createNamed('coreshop-' . $product->getId(), AddToCartType::class, $addToCart);

        if ($request->isMethod('POST')) {
            $redirect = $this->getParameterFromRequest($request, '_redirect', $this->generateUrl('coreshop_cart_summary'));

            $form->handleRequest($request);

            if ($form->isSubmitted() && $form->isValid()) {
                /**
                 * @var AddToCartInterface $addToCart
                 */
                $addToCart = $form->getData();

                $this->getCartModifier()->addToList($addToCart->getCart(), $addToCart->getCartItem());
                $this->getCartManager()->persistCart($this->getCart());

                $this->get(TrackerInterface::class)->trackCartAdd(
                    $addToCart->getCart(),
                    $addToCart->getCartItem()->getProduct(),
                    $addToCart->getCartItem()->getQuantity()
                );

                $this->addFlash('success', $this->get('translator')->trans('coreshop.ui.item_added'));

                if ($request->isXmlHttpRequest()) {
                    return new JsonResponse([
                        'success' => true,
                    ]);
                }

                return $this->redirect($redirect);
            }

            foreach ($form->getErrors(true, true) as $error) {
                $this->addFlash('error', $error->getMessage());
            }

            if ($request->isXmlHttpRequest()) {
                return new JsonResponse([
                    'success' => false,
                    'errors' => array_map(function (FormError $error) {
                        return $error->getMessage();
                    }, iterator_to_array($form->getErrors(true))),
                ]);
            }

            return $this->redirect($redirect);
        }

        if ($request->isXmlHttpRequest()) {
            return new JsonResponse([
                'success' => false,
            ]);
        }

        return $this->render(
            $this->getParameterFromRequest($request, 'template', $this->templateConfigurator->findTemplate('Product/_addToCart.html')),
            [
                'form' => $form->createView(),
                'product' => $product,
            ]
        );
    }

    public function removeItemAction(Request $request): Response
    {
        $cartItem = $this->get('coreshop.repository.order_item')->find($this->getParameterFromRequest($request, 'cartItem'));

        if (!$cartItem instanceof OrderItemInterface) {
            return $this->redirectToRoute('coreshop_index');
        }

        if ($cartItem->getOrder()->getId() !== $this->getCart()->getId()) {
            return $this->redirectToRoute('coreshop_index');
        }

        $this->addFlash('success', $this->get('translator')->trans('coreshop.ui.item_removed'));

        $this->getCartModifier()->removeFromList($this->getCart(), $cartItem);
        $this->getCartManager()->persistCart($this->getCart());

        $this->get(TrackerInterface::class)->trackCartRemove($this->getCart(), $cartItem->getProduct(), $cartItem->getQuantity());

        return $this->redirectToRoute('coreshop_cart_summary');
    }

    public function removePriceRuleAction(Request $request): Response
    {
        $code = $this->getParameterFromRequest($request, 'code');
        $cart = $this->getCart();

        $voucherCode = $this->getCartPriceRuleVoucherRepository()->findByCode($code);

        if (!$voucherCode instanceof CartPriceRuleVoucherCodeInterface) {
            return $this->redirectToRoute('coreshop_cart_summary');
        }

        $priceRule = $voucherCode->getCartPriceRule();

        $this->getCartPriceRuleUnProcessor()->unProcess($cart, $priceRule, $voucherCode);
        $this->getCartManager()->persistCart($cart);

        return $this->redirectToRoute('coreshop_cart_summary');
    }

    protected function createAddToCart(OrderInterface $cart, OrderItemInterface $cartItem): AddToCartInterface
    {
        return $this->get(AddToCartFactoryInterface::class)->createWithCartAndCartItem($cart, $cartItem);
    }

    protected function getCartPriceRuleProcessor(): CartPriceRuleProcessorInterface
    {
        return $this->get(CartPriceRuleProcessorInterface::class);
    }

    protected function getCartPriceRuleUnProcessor(): CartPriceRuleUnProcessorInterface
    {
        return $this->get(CartPriceRuleUnProcessorInterface::class);
    }

    protected function getCartModifier(): CartModifierInterface
    {
        return $this->get(CartModifierInterface::class);
    }

    protected function getQuantityModifer(): CartItemQuantityModifier
    {
        return $this->get(CartItemQuantityModifier::class);
    }

    protected function getCartPriceRuleVoucherRepository(): CartPriceRuleVoucherRepositoryInterface
    {
        return $this->get('coreshop.repository.cart_price_rule_voucher_code');
    }

    protected function getCart(): OrderInterface
    {
        return $this->getCartContext()->getCart();
    }

    protected function getCartContext(): CartContextInterface
    {
        return $this->get(CartContextInterface::class);
    }

    protected function getCartManager(): CartManagerInterface
    {
        return $this->get(CartManagerInterface::class);
    }

    private function getCartItemErrors(OrderItemInterface $cartItem): ConstraintViolationListInterface
    {
        return $this
            ->get('validator')
            ->validate($cartItem, null, $this->container->getParameter('coreshop.form.type.cart_item.validation_groups'));
    }
}
