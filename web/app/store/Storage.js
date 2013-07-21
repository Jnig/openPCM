/*
 * File: app/store/Storage.js
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

Ext.define('MyApp.store.Storage', {
    extend: 'Ext.data.Store',

    requires: [
        'MyApp.model.Storage'
    ],

    constructor: function(cfg) {
        var me = this;
        cfg = cfg || {};
        me.callParent([Ext.apply({
            autoLoad: false,
            autoSync: true,
            model: 'MyApp.model.Storage',
            storeId: 'MyJsonStore1',
            proxy: {
                type: 'rest',
                url: '/api/storages',
                reader: {
                    type: 'json'
                }
            }
        }, cfg)]);
    },

    setVirtualmachineId: function(id) {
        this.getProxy().url = '/api/storages/'+ id +'/virtualmachines';


    }

});