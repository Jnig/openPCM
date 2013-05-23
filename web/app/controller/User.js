/*
 * File: app/controller/User.js
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

Ext.define('MyApp.controller.User', {
    extend: 'Ext.app.Controller',

    stores: [
        'User',
        'Profile'
    ],

    refs: [
        {
            ref: 'grid',
            selector: '#userlistgrid'
        },
        {
            ref: 'userfield',
            selector: '#useraddpassword'
        },
        {
            ref: 'label',
            selector: '#profiletext'
        }
    ],

    onButtonClick: function(button, e, options) {
        var form = button.up('window').down('form');

        var values = form.getValues();
        var store = this.getUserStore();



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

    onButtonClickDelete: function(button, e, options) {
        var selected = this.getGrid().getSelectionModel().getSelection();


        if (selected.length > 0 ) {
            Ext.Msg.confirm('Remove User', 'You are about to remove <span style="color:red;font-weight:bold;">' + selected.length+ '</span> Users. Are you sure?', function (button) {
                if (button == 'yes') {
                    this.getUserStore().remove(selected);
                }
            }, this);

        }
    },

    onGridpanelItemDblClick: function(tablepanel, record, item, index, e, options) {

        var window = Ext.widget('useradd');
        var form = window.down('form');

        this.getUserfield().minLength = 0;
        this.getUserfield().allowBlank = true;

        form.loadRecord(record);


    },

    onLaunch: function() {
        var t = this; // for scope in interval function

        this.getProfileStore().load(function(r, options, success) {

            t.getLabel().setText('Logged in as '+ r[0].get('firstName')+ ' ' +r[0].get('secondName'));


            userId = r[0].get('id');

        });


    },

    getUserId: function() {
        return userId;
    },

    init: function(application) {
        this.control({
            "useradd button[action=save]": {
                click: this.onButtonClick
            },
            "#userlistdelete": {
                click: this.onButtonClickDelete
            },
            "#userlistgrid": {
                itemdblclick: this.onGridpanelItemDblClick
            }
        });
    }

});
