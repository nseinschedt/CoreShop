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

pimcore.registerNS('pimcore.object.classes.data.coreShopStoreValues');
pimcore.object.classes.data.coreShopStoreValues = Class.create(pimcore.object.classes.data.data, {
    type: 'coreShopStoreValues',

    allowIn: {
        object: true,
        objectbrick: false,
        fieldcollection: true,
        localizedfield: true,
        classificationstore: false,
        block: true
    },

    initialize: function (treeNode, initData) {
        this.type = 'coreShopStoreValues';
        this.treeNode = treeNode;

        this.initData(initData);
    },

    getTypeName: function () {
        return t('coreshop_store_values');
    },

    getGroup: function () {
        return 'coreshop';
    },

    getIconClass: function () {
        return 'coreshop_icon_store_values';
    },

    getLayout: function ($super) {
        $super();

        this.specificPanel.removeAll();
        this.specificPanel.add([
            {
                xtype: 'numberfield',
                fieldLabel: t('width'),
                name: 'width',
                value: this.datax.width
            },
            {
                xtype: 'numberfield',
                fieldLabel: t('default_value'),
                name: 'defaultValue',
                value: this.datax.defaultValue
            }, {
                xtype: 'panel',
                bodyStyle: 'padding-top: 3px',
                style: 'margin-bottom: 10px',
                html: '<span class="object_field_setting_warning">' + t('default_value_warning') + '</span>'
            }
        ]);

        if (!this.isInCustomLayoutEditor()) {
            this.specificPanel.add([
                {
                    xtype: 'numberfield',
                    fieldLabel: t('min_value'),
                    name: 'minValue',
                    value: this.datax.minValue
                }, {
                    xtype: 'numberfield',
                    fieldLabel: t('max_value'),
                    name: 'maxValue',
                    value: this.datax.maxValue
                }
            ]);
        }

        return this.layout;
    },

    applySpecialData: function (source) {
        if (source.datax) {
            if (!this.datax) {
                this.datax = {};
            }

            Ext.apply(this.datax, {
                width: source.datax.width,
                defaultValue: source.datax.defaultValue,
                minValue: source.datax.minValue,
                maxValue: source.datax.maxValue,
            });
        }
    }
});
