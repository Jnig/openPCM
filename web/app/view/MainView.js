/*
 * File: app/view/MainView.js
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

Ext.define('MyApp.view.MainView', {
    extend: 'Ext.container.Viewport',

    layout: {
        padding: '0 5 5 5',
        type: 'border'
    },

    initComponent: function() {
        var me = this;

        Ext.applyIf(me, {
            items: [
                {
                    xtype: 'panel',
                    region: 'center',
                    split: false,
                    id: '',
                    minWidth: 200,
                    layout: {
                        type: 'absolute'
                    },
                    title: 'My Panel'
                },
                {
                    xtype: 'container',
                    region: 'north',
                    height: 36,
                    html: '<div id="app-header">openPCM</div>',
                    id: '',
                    layout: {
                        type: 'absolute'
                    },
                    items: [
                        {
                            xtype: 'container',
                            height: 30,
                            items: [
                                {
                                    xtype: 'button',
                                    cls: 'pull-right',
                                    margin: '10 0 0 0',
                                    text: 'Logout',
                                    listeners: {
                                        click: {
                                            fn: me.onButtonClick,
                                            scope: me
                                        }
                                    }
                                },
                                {
                                    xtype: 'label',
                                    cls: 'pull-right',
                                    id: 'profiletext',
                                    margin: '15 10 0 0',
                                    width: 300,
                                    text: ''
                                }
                            ]
                        }
                    ]
                },
                {
                    xtype: 'treepanel',
                    region: 'west',
                    split: true,
                    width: 150,
                    title: 'Navigation',
                    store: 'Navigation',
                    rootVisible: false,
                    viewConfig: {
                        id: 'Navigation',
                        plugins: [
                            Ext.create('Ext.tree.plugin.TreeViewDragDrop', {
                                ptype: 'treeviewdragdrop',
                                allowContainerDrops: true
                            })
                        ],
                        listeners: {
                            beforedrop: {
                                fn: me.onTreedragdroppluginBeforeDrop,
                                scope: me
                            },
                            drop: {
                                fn: me.onTreedragdroppluginDrop,
                                scope: me
                            }
                        }
                    }
                },
                {
                    xtype: 'gridpanel',
                    region: 'south',
                    split: true,
                    weight: -150,
                    height: 150,
                    id: 'jobgrid',
                    collapsible: false,
                    title: 'Log',
                    store: 'Job',
                    viewConfig: {
                        loadMask: false
                    },
                    columns: [
                        {
                            xtype: 'gridcolumn',
                            dataIndex: 'id',
                            text: 'Id'
                        },
                        {
                            xtype: 'gridcolumn',
                            renderer: function(value, metaData, record, rowIndex, colIndex, store, view) {

                                if (value == 'finished') {
                                    return '<img src="/icons/silk/accept.png" alt="finished" />';
                                } else if (value == 'pending') {
                                    return '<img src="/icons/silk/loading.gif" alt="pending" />';        
                                } else if (value == 'failed') {
                                    return '<img src="/icons/silk/cancel.png" alt="failed" />';        
                                } else if (value == 'running') {
                                    return '<img src="/icons/silk/loading.gif" alt="running" />';           
                                }
                                return value;
                            },
                            dataIndex: 'state',
                            text: 'State'
                        },
                        {
                            xtype: 'gridcolumn',
                            dataIndex: 'name',
                            flex: 1,
                            text: 'Name'
                        },
                        {
                            xtype: 'gridcolumn',
                            weight: 200,
                            dataIndex: 'createdAt',
                            flex: 1,
                            text: 'CreatedAt'
                        },
                        {
                            xtype: 'gridcolumn',
                            dataIndex: 'owner',
                            text: 'Owner'
                        },
                        {
                            xtype: 'gridcolumn',
                            dataIndex: 'command',
                            flex: 0.5,
                            text: 'Command'
                        }
                    ],
                    listeners: {
                        afterrender: {
                            fn: me.onJobgridAfterRender,
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
                                    id: 'userJobs',
                                    text: 'User'
                                },
                                {
                                    xtype: 'button',
                                    id: 'systemJobs',
                                    text: 'System'
                                }
                            ]
                        }
                    ]
                }
            ]
        });

        me.callParent(arguments);
    },

    onButtonClick: function(button, e, options) {
        window.location = '/logout';
    },

    onTreedragdroppluginBeforeDrop: function(node, data, overModel, dropPosition, dropFunction, options) {

        if (data.records[0].get('clusterId') != overModel.get('clusterId')) {
            return false;
        }
    },

    onTreedragdroppluginDrop: function(node, data, overModel, dropPosition, options) {
        var vmId = data.records[0].get('vmId');
        var nodeId = overModel.get('nodeId');

        Ext.Ajax.request({
            method: 'GET',
            url: '/api/vms/'+ vmId +'/migrates/'+nodeId,
            success: function( result, request ){

            }
        });
    },

    onJobgridAfterRender: function(abstractcomponent, options) {


        var Mask = new Ext.LoadMask(this.getEl(), {"msg":"Please wait..", "store": abstractcomponent.getStore()});

        Mask.show();


    }

});