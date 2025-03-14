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

namespace CoreShop\Component\Notification\Processor;

use CoreShop\Component\Notification\Model\NotificationRuleInterface;
use CoreShop\Component\Notification\Rule\Action\NotificationRuleProcessorInterface;
use CoreShop\Component\Registry\ServiceRegistryInterface;

class RuleApplier implements RuleApplierInterface
{
    public function __construct(private ServiceRegistryInterface $actionServiceRegistry)
    {
    }

    public function applyRule(NotificationRuleInterface $rule, $subject, array $params): void
    {
        foreach ($rule->getActions() as $action) {
            $processor = $this->actionServiceRegistry->get($action->getType());

            if ($processor instanceof NotificationRuleProcessorInterface) {
                $processor->apply($subject, $rule, $action->getConfiguration(), $params);
            }
        }
    }
}
