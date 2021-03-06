/*
 * File: app/view/network/List.js
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

Ext.define('MyApp.view.network.List', {
    extend: 'Ext.grid.Panel',
    alias: 'widget.networklist',

    height: 250,
    width: 400,
    iconCls: 'icon-network',
    title: 'Network',
    store: 'Network',

    initComponent: function() {
        var me = this;

        Ext.applyIf(me, {
            viewConfig: {
                id: 'networklistgrid'
            },
            dockedItems: [
                {
                    xtype: 'toolbar',
                    dock: 'top',
                    items: [
                        {
                            xtype: 'button',
                            iconCls: 'icon-add',
                            text: 'Add',
                            listeners: {
                                click: {
                                    fn: me.onButtonClick,
                                    scope: me
                                }
                            }
                        },
                        {
                            xtype: 'button',
                            id: 'networklistdelete',
                            iconCls: 'icon-delete',
                            text: 'Delete'
                        }
                    ]
                }
            ],
            listeners: {
                afterrender: {
                    fn: me.onGridpanelAfterRender,
                    scope: me
                }
            },
            columns: [
                {
                    xtype: 'gridcolumn',
                    dataIndex: 'id',
                    text: 'Id'
                },
                {
                    xtype: 'gridcolumn',
                    dataIndex: 'name',
                    text: 'Name'
                },
                {
                    xtype: 'gridcolumn',
                    dataIndex: 'forwardMode',
                    text: 'ForwardMode'
                },
                {
                    xtype: 'gridcolumn',
                    dataIndex: 'bridgeName',
                    text: 'BridgeName'
                }
            ],
            selModel: Ext.create('Ext.selection.CheckboxModel', {

            })
        });

        me.callParent(arguments);
    },

    onButtonClick: function(button, e, eOpts) {
        Ext.widget('networkadd');
    },

    onGridpanelAfterRender: function(component, eOpts) {
        var Mask = new Ext.LoadMask(this.getEl(), {"msg":"Please wait..", "store": this.getStore()});
        Mask.show();
        this.getStore().load();
    }

});