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

namespace CoreShop\Bundle\RuleBundle\DependencyInjection\Compiler;

use CoreShop\Component\Registry\RegisterSimpleRegistryTypePass;

class RuleAvailabilityAssessorPass extends RegisterSimpleRegistryTypePass
{
    public const RULE_AVAILABILITY_ASSESSOR_TAG = 'coreshop.registry.rule_availability_assessor';

    public function __construct()
    {
        parent::__construct(
            'coreshop.registry.rule_availability_assessor',
            'coreshop.registry.rule_availability_assessors',
            self::RULE_AVAILABILITY_ASSESSOR_TAG
        );
    }
}
