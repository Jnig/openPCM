/*
 * File: app/view/vm/List.js
 *
 * This file was generated by Sencha Architect version 2.1.0.
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

Ext.define('MyApp.view.vm.List', {
    extend: 'Ext.grid.Panel',
    alias: 'widget.vmlist',

    height: 272,
    id: 'vmlist',
    width: 846,
    iconCls: 'icon-vm',
    title: 'Virtualmachine',
    store: 'Virtualmachine',

    initComponent: function() {
        var me = this;

        Ext.applyIf(me, {
            viewConfig: {
                id: 'vmlistgrid'
            },
            dockedItems: [
                {
                    xtype: 'toolbar',
                    dock: 'top',
                    items: [
                        {
                            xtype: 'button',
                            iconCls: 'icon-add',
                            text: 'Create',
                            listeners: {
                                click: {
                                    fn: me.onButtonClick,
                                    scope: me
                                }
                            }
                        },
                        {
                            xtype: 'button',
                            id: 'deletevm',
                            iconCls: 'icon-delete',
                            text: 'Delete'
                        }
                    ]
                }
            ],
            selModel: Ext.create('Ext.selection.CheckboxModel', {
                checkOnly: true
            }),
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
                    dataIndex: 'vcpu',
                    text: 'Vcpu'
                },
                {
                    xtype: 'gridcolumn',
                    dataIndex: 'memory',
                    text: 'Memory in mb'
                },
                {
                    xtype: 'gridcolumn',
                    dataIndex: 'nodeName',
                    text: 'Node'
                },
                {
                    xtype: 'gridcolumn',
                    dataIndex: 'nodeDefaultName',
                    text: 'Default Node'
                },
                {
                    xtype: 'gridcolumn',
                    dataIndex: 'state',
                    text: 'State'
                }
            ],
            listeners: {
                afterrender: {
                    fn: me.onVmlistAfterRender,
                    scope: me
                }
            }
        });

        me.callParent(arguments);
    },

    onButtonClick: function(button, e, options) {

        var view = Ext.widget('vmadd');

    },

    onVmlistAfterRender: function(abstractcomponent, options) {
        var Mask = new Ext.LoadMask(this.getEl(), {"msg":"Please wait..", "store": this.getStore()});
        Mask.show();
        this.getStore().load();

    }

});