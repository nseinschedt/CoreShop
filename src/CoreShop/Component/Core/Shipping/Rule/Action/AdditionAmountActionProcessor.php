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

namespace CoreShop\Component\Core\Shipping\Rule\Action;

use CoreShop\Component\Address\Model\AddressInterface;
use CoreShop\Component\Currency\Converter\CurrencyConverterInterface;
use CoreShop\Component\Currency\Model\CurrencyInterface;
use CoreShop\Component\Currency\Repository\CurrencyRepositoryInterface;
use CoreShop\Component\Shipping\Model\CarrierInterface;
use CoreShop\Component\Shipping\Model\ShippableInterface;
use CoreShop\Component\Shipping\Rule\Action\CarrierPriceModificationActionProcessorInterface;
use Webmozart\Assert\Assert;

class AdditionAmountActionProcessor implements CarrierPriceModificationActionProcessorInterface
{
    public function __construct(protected CurrencyRepositoryInterface $currencyRepository, protected CurrencyConverterInterface $moneyConverter)
    {
    }

    public function getModification(CarrierInterface $carrier, ShippableInterface $shippable, AddressInterface $address, int $price, array $configuration, array $context): int
    {
        Assert::keyExists($context, 'currency');
        Assert::isInstanceOf($context['currency'], CurrencyInterface::class);

        /**
         * @var CurrencyInterface $contextCurrency
         */
        $contextCurrency = $context['currency'];
        $amount = $configuration['amount'];

        $currency = $this->currencyRepository->find($configuration['currency']);

        Assert::isInstanceOf($currency, CurrencyInterface::class);

        return $this->moneyConverter->convert($amount, $currency->getIsoCode(), $contextCurrency->getIsoCode());
    }
}
