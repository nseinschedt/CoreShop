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

namespace CoreShop\Component\Core\Provider;

use CoreShop\Component\Address\Context\CountryNotFoundException;
use CoreShop\Component\Address\Model\AddressInterface;
use CoreShop\Component\Resource\Factory\PimcoreFactoryInterface;

class ContextBasedDefaultTaxAddressProvider implements DefaultTaxAddressProviderInterface
{
    public function __construct(private PimcoreFactoryInterface $addressFactory)
    {
    }

    public function getAddress(array $context = []): ?AddressInterface
    {
        $address = $this->addressFactory->createNew();

        if (array_key_exists('country', $context)) {
            $country = $context['country'];
        } elseif (array_key_exists('store', $context)) {
            $country = $context['store']->getBaseCountry();
        } else {
            throw new CountryNotFoundException();
        }

        $address->setCountry($country);

        return $address;
    }
}
