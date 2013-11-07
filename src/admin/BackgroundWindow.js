
Ext.ns('Library.admin');

Library.admin.BackgroundWindow = Ext.extend(Ext.Window, {
    
    addBg: function() {
        var form = this.getComponent(0).getForm();
        if (!form.isValid())
            return;
        
        this._mask.show();
        form.submit({
            url: Library.Main.config().controller,
            scope: this,
            params: {
                cmd: 'addBackground'
            },
            success: function(frm, action) {
                this._mask.hide();
                this.getComponent(1).getStore().reload();
            },
            failure: function(frm, action) {
                this._mask.hide();
                Library.Main.failureForm(action.result);
            }
        });
    },
            
    backgroundClicked: function(data, index) {
        var record = data.getStore().getAt(index);
        this._mask.show();
        Ext.Ajax.request({
            url: Library.Main.config().controller,
            scope: this,
            params: {
                cmd: 'changeBackground',
                bg: record.get('bg')
            },
            success: function(response) {
                this._mask.hide();
                var json = Library.Main.getJson(response);
                if (json.success) {
                    Library.Main.setBackground(record.get('bg'));
                }
            },
            failure: function(response) {
                this._mask.hide();
                Library.Main.failure(response);
            }
        });
    },
    
    initButtons: function() {
        return [
            {
                text: 'Fermer',
                scale: 'medium',
                iconCls: 'book-window-close',
                scope: this,
                handler: function() {
                    this.close();
                }
            }
        ];
    },
    
    initForm: function(config) {
        return Ext.apply({
            xtype: 'form',
            bodyStyle: 'padding: 10px;',
            border: false,
            fileUpload: true,
            items: {
                xtype: 'compositefield',
                fieldLabel: 'Ajouter un fond',
                items: [{
                    xtype: 'field',
                    inputType: 'file',
                    name: 'bgfile',
                    allowBlank: false
                }, {
                    hideLabel: true,
                    xtype: 'button',
                    text: 'Ajouter',
                    scope: this,
                    handler: this.addBg
                }]
            }
        }, config);
    },
            
    initDataView: function(config) {
        return Ext.apply({
            xtype: 'dataview',
            store: {
                xtype: 'jsonstore',
                url: Library.Main.config().controller,
                baseParams: {
                    cmd: 'getBackgrounds'
                },
                autoLoad: true,
                root: 'items',
                idProperty: 'bg',
                fields: [
                    'bg', 'thumb'
                ]
            },
            autoScroll: true,
            multiSelect: true,
            emptyText: 'aucun fond d ecran',
            overClass:'x-view-over',
            itemSelector:'div.thumb-wrap',
            tpl: new Ext.XTemplate(
                '<tpl for=".">',
                    '<div class="thumb-wrap" id="{bg}">',
                    '<div class="thumb"><img src="{thumb}" title="{bg}" alt="{bg}"></div>',
                    '<span class="">{bg}</span></div>',
                '</tpl>',
                '<div class="x-clear"></div>'
            ),
            listeners: {
                dblclick: {scope: this, fn: this.backgroundClicked}
            }
        }, config);
    },
    
    initComponent: function() {
        Ext.apply(this, {
            modal: true,
            width: 482,
            height: 420,
            title: "Fond d'écran",
            layout: 'border',
            cls: 'backgrounds',
            items: [
                this.initForm({
                    region: 'north',
                    height: 45
                }),
                this.initDataView({
                    region: 'center'
                })
            ],
            buttons: this.initButtons(),
            listeners: {
                afterrender: function(cmp) {
                    cmp._mask = new Ext.LoadMask(cmp.getEl());
                }
            }
        });
        Library.admin.BackgroundWindow.superclass.initComponent.apply(this, arguments);
    }   
    
});
