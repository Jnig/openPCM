/*
 * File: app/view/user/List.js
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

Ext.define('MyApp.view.user.List', {
    extend: 'Ext.grid.Panel',
    alias: 'widget.userlist',

    height: 250,
    id: '',
    width: 671,
    iconCls: 'icon-user',
    title: 'User',
    store: 'User',

    initComponent: function() {
        var me = this;

        Ext.applyIf(me, {
            viewConfig: {
                id: 'userlistgrid'
            },
            listeners: {
                afterrender: {
                    fn: me.onGridpanelAfterRender,
                    scope: me
                }
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
                            id: 'userlistdelete',
                            iconCls: 'icon-delete',
                            text: 'Delete'
                        }
                    ]
                }
            ],
            selModel: Ext.create('Ext.selection.CheckboxModel', {

            }),
            columns: [
                {
                    xtype: 'gridcolumn',
                    dataIndex: 'id',
                    text: 'Id',
                    flex: 0.5
                },
                {
                    xtype: 'gridcolumn',
                    dataIndex: 'firstName',
                    text: 'FirstName'
                },
                {
                    xtype: 'gridcolumn',
                    dataIndex: 'secondName',
                    text: 'SecondName'
                },
                {
                    xtype: 'gridcolumn',
                    dataIndex: 'username',
                    text: 'Username'
                },
                {
                    xtype: 'gridcolumn',
                    dataIndex: 'email',
                    text: 'Email',
                    flex: 2
                },
                {
                    xtype: 'gridcolumn',
                    dataIndex: 'roles',
                    text: 'Roles',
                    flex: 2
                },
                {
                    xtype: 'gridcolumn',
                    dataIndex: 'ldap',
                    text: 'Ldap'
                }
            ]
        });

        me.callParent(arguments);
    },

    onGridpanelAfterRender: function(component, eOpts) {
        var Mask = new Ext.LoadMask(this.getEl(), {"msg":"Please wait..", "store": this.getStore()});
        Mask.show();
        this.getStore().load();
    },

    onButtonClick: function(button, e, eOpts) {
        var window = Ext.widget('useradd');
    }

});