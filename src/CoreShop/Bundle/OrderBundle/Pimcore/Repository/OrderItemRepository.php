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

namespace CoreShop\Bundle\OrderBundle\Pimcore\Repository;

use CoreShop\Bundle\ResourceBundle\Pimcore\PimcoreRepository;
use CoreShop\Component\Order\Repository\OrderItemRepositoryInterface;

class OrderItemRepository extends PimcoreRepository implements OrderItemRepositoryInterface
{
    public function findOrderItemsByProductId(int $productId): array
    {
        $list = $this->getList();
        $list->setCondition('product__id = ?', [$productId]);
        $list->load();

        return $list->getObjects();
    }
}
