/*
 * File: app/view/disk/Add.js
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

Ext.define('MyApp.view.disk.Add', {
    extend: 'Ext.window.Window',
    alias: 'widget.diskadd',

    autoShow: true,
    border: false,
    height: 217,
    width: 400,
    layout: {
        type: 'fit'
    },
    title: 'My Window',

    initComponent: function() {
        var me = this;

        Ext.applyIf(me, {
            buttons: [
                {
                    text: 'Other Files',
                    id: 'openDiskExisting'
                },
                {
                    text: 'Save',
                    action: 'save'
                },
                {
                    text: 'Cancel',
                    scope: this,
                    handler: this.close
                }
            ],
            items: [
                {
                    xtype: 'form',
                    bodyPadding: 5,
                    items: [
                        {
                            xtype: 'radiogroup',
                            width: 400,
                            fieldLabel: 'Use',
                            items: [
                                {
                                    xtype: 'radiofield',
                                    name: 'action',
                                    boxLabel: 'Existing Disk',
                                    checked: true,
                                    inputValue: 'existing',
                                    listeners: {
                                        change: {
                                            fn: me.onRadiofieldChange,
                                            scope: me
                                        }
                                    }
                                },
                                {
                                    xtype: 'radiofield',
                                    name: 'action',
                                    boxLabel: 'Create New Disk',
                                    inputValue: 'create',
                                    listeners: {
                                        change: {
                                            fn: me.onRadiofieldChange1,
                                            scope: me
                                        }
                                    }
                                }
                            ]
                        },
                        {
                            xtype: 'combobox',
                            anchor: '100%',
                            id: 'diskaddstorage',
                            fieldLabel: 'Storage',
                            name: 'storage',
                            displayField: 'name',
                            store: 'Storage',
                            valueField: 'id',
                            listeners: {
                                change: {
                                    fn: me.onDiskaddstorageChange,
                                    scope: me
                                }
                            }
                        },
                        {
                            xtype: 'textfield',
                            anchor: '100%',
                            hidden: true,
                            id: 'diskaddsize',
                            fieldLabel: 'Size in GB',
                            name: 'capacity'
                        },
                        {
                            xtype: 'combobox',
                            anchor: '100%',
                            id: 'diskaddvolume',
                            fieldLabel: 'Volume',
                            name: 'volume',
                            displayField: 'path',
                            store: 'DiskAdd',
                            valueField: 'id'
                        },
                        {
                            xtype: 'combobox',
                            anchor: '100%',
                            fieldLabel: 'Disk Device',
                            name: 'diskDevice',
                            displayField: 'name',
                            store: 'disk.Device',
                            valueField: 'name'
                        }
                    ]
                }
            ]
        });

        me.callParent(arguments);
    },

    onRadiofieldChange: function(field, newValue, oldValue, eOpts) {
        Ext.getCmp('diskaddsize').show();
        Ext.getCmp('diskaddvolume').hide();
    },

    onRadiofieldChange1: function(field, newValue, oldValue, eOpts) {


        Ext.getCmp('diskaddsize').hide();
        Ext.getCmp('diskaddvolume').show();

    },

    onDiskaddstorageChange: function(field, newValue, oldValue, eOpts) {
        Ext.getCmp('diskaddvolume').reset();
    }

});