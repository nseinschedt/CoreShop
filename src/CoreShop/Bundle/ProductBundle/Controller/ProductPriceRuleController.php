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

namespace CoreShop\Bundle\ProductBundle\Controller;

use CoreShop\Bundle\ResourceBundle\Controller\ResourceController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class ProductPriceRuleController extends ResourceController
{
    public function getConfigAction(Request $request): Response
    {
        $actions = $this->getConfigActions();
        $conditions = $this->getConfigConditions();

        return $this->viewHandler->handle(['actions' => array_keys($actions), 'conditions' => array_keys($conditions)]);
    }

    /**
     * @return array<string, string>
     */
    protected function getConfigActions(): array
    {
        return $this->container->getParameter('coreshop.product_price_rule.actions');
    }

    /**
     * @return array<string, string>
     */
    protected function getConfigConditions(): array
    {
        return $this->container->getParameter('coreshop.product_price_rule.conditions');
    }
}
