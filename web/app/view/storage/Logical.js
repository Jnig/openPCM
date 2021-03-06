/*
 * File: app/view/storage/Logical.js
 *
 * This file was generated by Sencha Architect version 2.2.2.
 * http://www.sencha.com/products/architect/
 *
 * This file requires use of the Ext JS 4.1.x library, under independent license.
 * License of Sencha Architect does not include license for Ext JS 4.1.x. For more
 * details see http://www.sencha.com/license or contact license@sencha.com.
 *
 * This file will be auto-generated each and everytime you save your project.
 *
 * Do NOT hand edit this file.
 */

Ext.define('MyApp.view.storage.Logical', {
    extend: 'Ext.window.Window',
    alias: 'widget.storagelogical',

    autoShow: true,
    border: false,
    height: 150,
    width: 348,
    layout: {
        type: 'fit'
    },
    title: 'Storage LVM',

    initComponent: function() {
        var me = this;

        Ext.applyIf(me, {
            buttons: [
                {
                    text: 'Save',
                    action: 'save'
                },
                {
                    text: 'Cancel',
                    scope: this,
                    handler: this.close
                }
            ],
            items: [
                {
                    xtype: 'form',
                    bodyPadding: 10,
                    items: [
                        {
                            xtype: 'textfield',
                            anchor: '100%',
                            fieldLabel: 'Name',
                            name: 'name'
                        },
                        {
                            xtype: 'textfield',
                            anchor: '100%',
                            fieldLabel: 'Volume Group',
                            name: 'volumeGroup'
                        },
                        {
                            xtype: 'hiddenfield',
                            anchor: '100%',
                            fieldLabel: 'Label',
                            name: 'entity',
                            value: 'StorageLogical'
                        }
                    ]
                }
            ]
        });

        me.callParent(arguments);
    }

});