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

namespace CoreShop\Component\ProductQuantityPriceRules\Rule\Action;

use CoreShop\Component\ProductQuantityPriceRules\Model\QuantityRangeInterface;
use CoreShop\Component\ProductQuantityPriceRules\Model\QuantityRangePriceAwareInterface;

class PercentageDecreaseAction implements ProductQuantityPriceRuleActionInterface
{
    public function calculate(QuantityRangeInterface $range, QuantityRangePriceAwareInterface $subject, int $realItemPrice, array $context): int
    {
        return max($realItemPrice - ((int)round(($range->getPercentage() / 100) * $realItemPrice)), 0);
    }
}
