/*
 * CoreShop.
 *
 * This source file is subject to the GNU General Public License version 3 (GPLv3)
 * For the full copyright and license information, please view the LICENSE.md and gpl-3.0.txt
 * files that are distributed with this source code.
 *
 * @copyright  Copyright (c) CoreShop GmbH (https://www.coreshop.org)
 * @license    https://www.coreshop.org/license     GNU General Public License version 3 (GPLv3)
 *
 */

pimcore.registerNS('coreshop.notification.rule.conditions.orderPaymentTransition');

coreshop.notification.rule.conditions.orderPaymentTransition = Class.create(coreshop.notification.rule.conditions.abstractTransition, {
    type: 'orderPaymentTransition ',

    getRepoName: function() {
        return 'coreshop_transitions_order_payment';
    }
});
