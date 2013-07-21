/*
 * File: app/controller/Drbd.js
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

Ext.define('MyApp.controller.Drbd', {
    extend: 'Ext.app.Controller',

    stores: [
        'Disk',
        'Node',
        'IpA',
        'IpB'
    ],

    refs: [
        {
            ref: 'grid',
            selector: '#vmdiskgrid'
        },
        {
            ref: 'nodeB',
            selector: '#diskDrbdNodeB'
        },
        {
            ref: 'nodeA',
            selector: '#diskDrbdNodeA'
        }
    ],

    onMenuitemClickEnableDrbd: function(item, e, eOpts) {
        var window = Ext.widget('diskdrbd');
        var node = this.getController('Virtualmachine').getVm().get('node');
        var form = window.down('form');

        form.getForm().setValues({
            nodeA: node
        });

        this.getNodeStore().loadDrbdNodes(this.getController('Virtualmachine').getId());




    },

    onComboboxBeforeQueryNodeA: function(queryEvent, eOpts) {

        var nodeA = this.getNodeA().getValue();

        if (nodeA != null) {
            this.getIpAStore().loadByNode(nodeA);
        }
    },

    onComboboxBeforeQueryNodeB: function(queryEvent, eOpts) {
        var nodeB = this.getNodeB().getValue();

        if (nodeB != null) {

            this.getIpBStore().loadByNode(nodeB);
        }
    },

    onButtonClickSave: function(button, e, eOpts) {
        var form = button.up('window').down('form');

        var values = form.getValues();
        var store = this.getDiskStore();



        if (form.getForm().isValid()) {
            var record = form.getRecord();

            if (typeof(record) != 'object' ) {
                store.suspendAutoSync();
                store.add(values);


                store.sync({
                    success: function () {


                        this.getDiskStore().load();
                    },
                    scope: this
                });
                store.resumeAutoSync();
            } else {

                record.set(values);
            }

            this.getDiskStore().load();
            button.up('window').close();
        } 
    },

    init: function(application) {
        this.control({
            "#enabledrbd": {
                click: this.onMenuitemClickEnableDrbd
            },
            "#diskDrbdIpA": {
                beforequery: this.onComboboxBeforeQueryNodeA
            },
            "#diskDrbdIpB": {
                beforequery: this.onComboboxBeforeQueryNodeB
            },
            "diskdrbd button[action=save]": {
                click: this.onButtonClickSave
            }
        });
    }

});
