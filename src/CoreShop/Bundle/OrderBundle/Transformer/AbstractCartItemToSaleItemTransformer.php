<?php
/**
 * CoreShop.
 *
 * This source file is subject to the GNU General Public License version 3 (GPLv3)
 * For the full copyright and license information, please view the LICENSE.md and gpl-3.0.txt
 * files that are distributed with this source code.
 *
 * @copyright  Copyright (c) 2015-2017 Dominik Pfaffenbauer (https://www.pfaffenbauer.at)
 * @license    https://www.coreshop.org/license     GNU General Public License version 3 (GPLv3)
 */

namespace CoreShop\Bundle\OrderBundle\Transformer;

use CoreShop\Component\Order\Model\SaleInterface;
use CoreShop\Component\Order\Model\SaleItemInterface;
use CoreShop\Component\Order\Taxation\ProposalItemTaxCollectorInterface;
use CoreShop\Component\Resource\Pimcore\ObjectServiceInterface;
use CoreShop\Component\Order\Model\CartItemInterface;
use CoreShop\Component\Order\Model\OrderItemInterface;
use CoreShop\Component\Order\Model\ProposalInterface;
use CoreShop\Component\Order\Model\ProposalItemInterface;
use CoreShop\Component\Order\Transformer\ProposalItemTransformerInterface;
use Pimcore\Model\Object\Fieldcollection;
use Webmozart\Assert\Assert;

abstract class AbstractCartItemToSaleItemTransformer implements ProposalItemTransformerInterface
{
    /**
     * @var ObjectServiceInterface
     */
    protected $objectService;

    /**
     * @var string
     */
    protected $pathForItems;

    /**
     * @var TransformerEventDispatcherInterface
     */
    protected $eventDispatcher;

    /**
     * @var ProposalItemTaxCollectorInterface
     */
    protected $cartItemTaxCollector;

    /**
     * @param ObjectServiceInterface $objectService
     * @param string $pathForItems
     * @param ProposalItemTaxCollectorInterface $cartItemTaxCollector
     * @param TransformerEventDispatcherInterface $eventDispatcher
     */
    public function __construct(
        ObjectServiceInterface $objectService,
        $pathForItems,
        ProposalItemTaxCollectorInterface $cartItemTaxCollector,
        TransformerEventDispatcherInterface $eventDispatcher
    )
    {
        $this->objectService = $objectService;
        $this->pathForItems = $pathForItems;
        $this->cartItemTaxCollector = $cartItemTaxCollector;
        $this->eventDispatcher = $eventDispatcher;
    }

    /**
     * {@inheritdoc}
     */
    public function transformSaleItem(ProposalInterface $sale, ProposalItemInterface $cartItem, ProposalItemInterface $saleItem, $type, $exchangeRate)
    {
        /**
         * @var $sale SaleInterface
         * @var $cartItem CartItemInterface
         * @var $saleItem OrderItemInterface
         */
        Assert::isInstanceOf($cartItem, CartItemInterface::class);
        Assert::isInstanceOf($saleItem, SaleItemInterface::class);
        Assert::isInstanceOf($sale, SaleInterface::class);

        $this->eventDispatcher->dispatchPreEvent($type, $cartItem, ['sale' => $sale, 'cart' => $cartItem->getCart(), 'item' => $saleItem]);

        $itemFolder = $this->objectService->createFolderByPath($sale->getFullPath() . '/' . $this->pathForItems);

        $this->objectService->copyObject($cartItem, $saleItem);

        $saleItem->setKey($cartItem->getKey());
        $saleItem->setParent($itemFolder);
        $saleItem->setPublished(true);

        $fieldCollection = new Fieldcollection();
        $fieldCollection->setItems($this->cartItemTaxCollector->getTaxes($cartItem));

        $saleItem->setTaxes($fieldCollection);

        $saleItem->setProduct($cartItem->getProduct());
        $saleItem->setItemWholesalePrice($cartItem->getItemWholesalePrice() * $exchangeRate);
        $saleItem->setItemRetailPrice($cartItem->getItemRetailPrice(true) * $exchangeRate, true);
        $saleItem->setItemRetailPrice($cartItem->getItemRetailPrice(false) * $exchangeRate, false);
        $saleItem->setTotal($cartItem->getTotal(true) * $exchangeRate, true);
        $saleItem->setTotal($cartItem->getTotal(false) * $exchangeRate, false);
        $saleItem->setItemPrice($cartItem->getItemPrice(true) * $exchangeRate, true);
        $saleItem->setItemPrice($cartItem->getItemPrice(false) * $exchangeRate, false);
        $saleItem->setTotalTax($cartItem->getTotalTax() * $exchangeRate);
        $saleItem->setItemTax($cartItem->getItemTax() * $exchangeRate);
        $saleItem->setItemWeight($cartItem->getItemWeight());
        $saleItem->setTotalWeight($cartItem->getTotalWeight());
        $saleItem->save();

        $sale->addItem($saleItem);

        $this->eventDispatcher->dispatchPostEvent($type, $cartItem, ['sale' => $sale, 'cart' => $cartItem->getCart(), 'item' => $saleItem]);

        return $saleItem;
    }
}
