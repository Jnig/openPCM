/*
 * File: app/controller/Node.js
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

Ext.define('MyApp.controller.Node', {
    extend: 'Ext.app.Controller',

    stores: [
        'Node',
        'IpA',
        'Cluster',
        'Storage',
        'Network',
        'Stonith',
        'StonithMeta'
    ],

    refs: [
        {
            ref: 'grid',
            selector: '#nodelistgrid'
        },
        {
            ref: 'tab',
            selector: '#nodeaddhatab'
        },
        {
            ref: 'stonithParameters',
            selector: '#stonithParameters'
        },
        {
            ref: 'stonithHelp',
            selector: '#stonithHelp'
        }
    ],

    onButtonClick: function(button, e, eOpts) {
        var window = button.up('window');

        var form = window.down('form');

        var values = form.getValues();

        if (form.getForm().isValid()) {
            var store = this.getNodeStore();    
            var record = form.getRecord();


            if (typeof(record) != 'object' ) {
                store.add(values);      
            } else {
                record.set(values);

            }
            window.close();
        }


    },

    onNodeDelete: function(button, e, eOpts) {
        var selected = this.getGrid().getSelectionModel().getSelection();


        if (selected.length > 0 ) {
            Ext.Msg.confirm('Remove Node', 'You are about to remove <span style="color:red;font-weight:bold;">' + selected.length+ '</span> Nodes. Are you sure?', function (button) {
                if (button == 'yes') {
                    this.getNodeStore().remove(selected);
                }
            }, this);

        }
    },

    onGridpanelItemDblClick: function(dataview, record, item, index, e, eOpts) {
        var window = Ext.widget('nodeadd');

        window.down('form').loadRecord(record);

        this.getIpAStore().loadByNode(record.get('id'));


        this.getStonithStore().loadByNode(record.get('id'));

        if (record.get('ha')) {
            var tab = this.getTab();
            tab.enable();
        }
    },

    onGridpanelAfterRender: function(component, eOpts) {
        this.getClusterStore().load();
        this.getNetworkStore().load();
        this.getStorageStore().load();
    },

    onComboboxSelect: function(combo, records, eOpts) {

        var node = combo.up('form').getRecord();




        this.getStonithMetaStore().setNodeAndStonith(node.get('id'), records[0].get('stonith'));

        this.getStonithMetaStore().load({scope: this,
            callback: function(records, operation, success) {
                var parameters = records[0].get('params')
                var help = records[0].get('description')

                this.getStonithParameters().setValue(parameters);
                this.getStonithHelp().setValue(help);
            }
        });



    },

    onButtonStandbyOffClick: function(button, e, eOpts) {
        var selected = this.getGrid().getSelectionModel().getSelection();


        if (selected.length > 0 ) {
            Ext.Msg.confirm('Standby Node', 'You are about to set  <span style="color:red;font-weight:bold;">' + selected.length+ '</span> nodes into standy mode. The cluster will migrate resources back to this node. Are you sure?', function (button) {
                if (button == 'yes') {
                    Ext.each(selected, function(value) {
                        this.nodeAction(value.get('id'), 'standby/off');

                    }, this);
                }
            }, this);

        }
    },

    onButtonStandbyOnClick: function(button, e, eOpts) {
        var selected = this.getGrid().getSelectionModel().getSelection();


        if (selected.length > 0 ) {
            Ext.Msg.confirm('Standby Node', 'You are about to set  <span style="color:red;font-weight:bold;">' + selected.length+ '</span> nodes into standy mode. The nodes will migrate all their running resources to other nodes. Are you sure?', function (button) {
                if (button == 'yes') {
                    Ext.each(selected, function(value) {
                        this.nodeAction(value.get('id'), 'standby/on');

                    }, this);
                }
            }, this);

        }
    },

    nodeAction: function(nodeId, action) {
        Ext.Ajax.request({
            url: '/api/nodes/' + nodeId + '/' + action
        });
    },

    init: function(application) {
        this.control({
            "nodeadd button[action=save]": {
                click: this.onButtonClick
            },
            "#nodedelete": {
                click: this.onNodeDelete
            },
            "nodelist": {
                itemdblclick: this.onGridpanelItemDblClick,
                afterrender: this.onGridpanelAfterRender
            },
            "#stonith": {
                select: this.onComboboxSelect
            },
            "#button-standby-off": {
                click: this.onButtonStandbyOffClick
            },
            "#button-standby-on": {
                click: this.onButtonStandbyOnClick
            }
        });
    }

});
