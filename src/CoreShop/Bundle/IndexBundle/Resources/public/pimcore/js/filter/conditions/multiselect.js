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

pimcore.registerNS('coreshop.filter.conditions.multiselect');

coreshop.filter.conditions.multiselect = Class.create(coreshop.filter.conditions.abstract, {

    type: 'multiselect',

    getItems: function () {
        return [
            this.getFieldsComboBox(),
            {
                xtype: 'combo',
                fieldLabel: t('coreshop_filters_values'),
                name: 'preSelects',
                width: 400,
                store: this.valueStore,
                displayField: 'value',
                multiSelect: true,
                valueField: 'key',
                triggerAction: 'all',
                typeAhead: false,
                editable: false,
                forceSelection: true,
                queryMode: 'local',
                value: this.data.configuration.preSelects
            }
        ];
    }
});
