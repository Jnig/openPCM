/*
 * File: app/view/storage/disk/List.js
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

Ext.define('MyApp.view.storage.disk.List', {
    extend: 'Ext.grid.Panel',
    alias: 'widget.storagedisklist',

    height: 250,
    width: 400,
    title: 'My Grid Panel',
    store: 'Disk',

    initComponent: function() {
        var me = this;

        Ext.applyIf(me, {
            viewConfig: {

            },
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
                    defaultWidth: 300,
                    dataIndex: 'path',
                    text: 'Path'
                },
                {
                    xtype: 'gridcolumn',
                    dataIndex: 'driverType',
                    text: 'DriverType'
                },
                {
                    xtype: 'gridcolumn',
                    dataIndex: 'allocation',
                    text: 'Allocation'
                },
                {
                    xtype: 'gridcolumn',
                    dataIndex: 'capacity',
                    text: 'Capacity'
                },
                {
                    xtype: 'gridcolumn',
                    dataIndex: 'node',
                    text: 'Node'
                }
            ]
        });

        me.callParent(arguments);
    },

    onGridpanelAfterRender: function(abstractcomponent, options) {
        var Mask = new Ext.LoadMask(this.getEl(), {"msg":"Please wait..", "store": this.getStore()});
        Mask.show();
        this.getStore().load();

    }

});