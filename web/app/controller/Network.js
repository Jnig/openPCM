/*
 * File: app/controller/Network.js
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

Ext.define('MyApp.controller.Network', {
    extend: 'Ext.app.Controller',

    stores: [
        'Network'
    ],

    refs: [
        {
            ref: 'grid',
            selector: '#networklistgrid'
        }
    ],

    onButtonClick: function(button, e, options) {
        var form = button.up('window').down('form');

        var values = form.getValues();
        var store = this.getNetworkStore();



        if (form.getForm().isValid()) {
            var record = form.getRecord();

            if (typeof(record) != 'object' ) {
                store.add(values);      
            } else {

                record.set(values);
            }

            button.up('window').close();
        } 
    },

    onGridpanelItemDblClick: function(tablepanel, record, item, index, e, options) {
        var window = Ext.widget('networkadd');
        var form = window.down('form');

        form.loadRecord(record);
    },

    onButtonClickDelete: function(button, e, options) {
        var selected = this.getGrid().getSelectionModel().getSelection();


        if (selected.length > 0 ) {
            Ext.Msg.confirm('Remove Network', 'You are about to remove <span style="color:red;font-weight:bold;">' + selected.length+ '</span> Networks. Are you sure?', function (button) {
                if (button == 'yes') {
                    this.getNetworkStore().remove(selected);
                }
            }, this);

        }
    },

    init: function(application) {
        this.control({
            "networkadd button[action=save]": {
                click: this.onButtonClick
            },
            "#networklistgrid": {
                itemdblclick: this.onGridpanelItemDblClick
            },
            "#networklistdelete": {
                click: this.onButtonClickDelete
            }
        });
    }

});
