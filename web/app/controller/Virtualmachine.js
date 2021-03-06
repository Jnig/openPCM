/*
 * File: app/controller/Virtualmachine.js
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

Ext.define('MyApp.controller.Virtualmachine', {
    extend: 'Ext.app.Controller',

    stores: [
        'Virtualmachine',
        'Storage'
    ],

    refs: [
        {
            ref: 'grid',
            selector: '#vmlistgrid'
        },
        {
            ref: 'vmedit',
            selector: 'vmedit'
        },
        {
            ref: 'pathField',
            selector: '#pathField'
        }
    ],

    onButtonClick: function(button, e, eOpts) {
        var selected = this.getGrid().getSelectionModel().getSelection();


        if (selected.length > 0 ) {
            Ext.Msg.confirm('Remove Virtual Machine', 'You are about to remove <span style="color:red;font-weight:bold;">' + selected.length+ '</span> Virtual Machines. Are you sure?', function (button) {
                if (button == 'yes') {
                    this.getVirtualmachineStore().remove(selected);
                }
            }, this);

        }
    },

    onButtonClick1: function(button, e, eOpts) {
        var win = button.up('window'),
            form = win.down('form');

        if (this.save(form)) {
            win.close();
        }

    },

    onGridpanelItemDblClick: function(dataview, record, item, index, e, eOpts) {
        this.switchVmEdit(record);
    },

    onButtonClick2: function(button, e, eOpts) {
        var form = button.up('form');


        this.save(form);
    },

    vmstart: function(button, e, eOpts) {
        this.vmaction('start');
    },

    vmshutdown: function(button, e, eOpts) {
        this.vmaction('shutdown');
    },

    vmreset: function(button, e, eOpts) {
        this.vmaction('reset');
    },

    vmdestroy: function(button, e, eOpts) {
        this.vmaction('destroy');
    },

    vnc: function(button, e, eOpts) {

        var window = Ext.widget('vmvnc');
        var component = window.down('component');

        window.height = Ext.getBody().getViewSize().height*0.8;
        window.width = Ext.getBody().getViewSize().width*0.8;

        component.autoEl.src = '/vnc/' + currentVm.get('id');
        window.show();




    },

    onTabpanelShow: function(component, eOpts) {
        console.log(component);

        component.removeAll();

        component.add({
            xtype: 'component',
            autoEl: {
                tag: 'iframe',
                src: '',
                style: 'border:0px;'
            },
            id: 'vnciframe'
        });

        var iframe = component.down('#vnciframe');

        iframe.autoEl.src = '/vnc/' + currentVm.get('id');


    },

    init: function(application) {
        currentVmId = 0;

        this.control({
            "#deletevm": {
                click: this.onButtonClick
            },
            "vmadd button[action=save]": {
                click: this.onButtonClick1
            },
            "#vmlistgrid": {
                itemdblclick: this.onGridpanelItemDblClick
            },
            "vmedit button[action=save]": {
                click: this.onButtonClick2
            },
            "#vmstart": {
                click: this.vmstart
            },
            "#vmshutdown": {
                click: this.vmshutdown
            },
            "#vmreset": {
                click: this.vmreset
            },
            "#vmdestroy": {
                click: this.vmdestroy
            },
            "#vmeditvnc": {
                click: this.vnc
            },
            "#vnctab": {
                show: this.onTabpanelShow
            }
        });
    },

    save: function(form) {

        var values = form.getValues();
        var store = this.getVirtualmachineStore();
        if (form.getForm().isValid()) {
            var record = form.getRecord()

            if (typeof(record) != 'object' ) {
                store.add(values);      
            } else {       
                record.set(values);
            }

            return 1;
        } else {
            return 0;
        }

    },

    vmaction: function(action) {
        Ext.Ajax.request({
            url: '/api/vms/' + currentVm.get('id') + '/' + action
        });
    },

    getId: function() {
        return currentVm.get('id');
    },

    getVm: function() {
        return currentVm;
    },

    switchVmEdit: function(record) {
        this.getController('Navigation').switchContent('vmedit');
        currentVm = record;



        view = this.getVmedit().down('form');
        view.loadRecord(record);

        this.getVmedit().setTitle('Virtual Machine - ' + currentVm.get('name'));

    }

});
