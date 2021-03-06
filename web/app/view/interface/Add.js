/*
 * File: app/view/interface/Add.js
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

Ext.define('MyApp.view.interface.Add', {
    extend: 'Ext.window.Window',
    alias: 'widget.interfaceadd',

    autoShow: true,
    border: false,
    height: 160,
    width: 400,
    layout: {
        type: 'fit'
    },
    title: 'Network Interface',

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
                            xtype: 'combobox',
                            anchor: '100%',
                            fieldLabel: 'Network',
                            name: 'network',
                            displayField: 'name',
                            store: 'Network',
                            valueField: 'id'
                        },
                        {
                            xtype: 'combobox',
                            anchor: '100%',
                            fieldLabel: 'Model Type',
                            name: 'modelType',
                            displayField: 'name',
                            store: 'interface.modelType',
                            valueField: 'name'
                        },
                        {
                            xtype: 'textfield',
                            anchor: '100%',
                            fieldLabel: 'MAC Address',
                            name: 'macAddress',
                            value: '[auto]'
                        }
                    ]
                }
            ]
        });

        me.callParent(arguments);
    }

});