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

namespace CoreShop\Bundle\IndexBundle\DependencyInjection\Compiler;

use CoreShop\Component\Registry\RegisterRegistryTypePass;

class RegisterFilterUserConditionTypesPass extends RegisterRegistryTypePass
{
    public const INDEX_FILTER_USER_CONDITION_TAG = 'coreshop.filter.user_condition_type';

    public function __construct()
    {
        parent::__construct(
            'coreshop.registry.filter.user_condition_types',
            'coreshop.form_registry.filter.user_condition_types',
            'coreshop.filter.user_condition_types',
            self::INDEX_FILTER_USER_CONDITION_TAG
        );
    }
}
