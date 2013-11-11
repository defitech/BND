
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
    
    removeBg: function(record) {
        this._mask.show();
        Ext.Ajax.request({
            url: Library.Main.config().controller,
            scope: this,
            params: {
                cmd: 'removeBackground',
                bg: record.get('bg')
            },
            success: function(response) {
                this._mask.hide();
                var json = Library.Main.getJson(response);
                if (json.success)
                    this.getComponent(1).getStore().reload();
            },
            failure: function(response) {
                this._mask.hide();
                Library.Main.failure(response);
            }
        });
    },
    
    showContextMenu: function(view, index, node, e) {
        var record = view.getStore().getAt(index);
        var menu = new Ext.menu.Menu({
            items: [{
                text: Library.wording.bg_set,
                scope: this,
                handler: function() {
                    this.backgroundClicked(view, index);
                }
            },{
                text: Library.wording.bg_remove,
                scope: this,
                handler: function() {
                    var msg = String.format(Library.wording.bg_remove_msg, record.get('bg'));
                    Ext.Msg.confirm(Library.wording.bg_title, msg, function(choice){
                        if (choice != 'yes')
                            return;
                        this.removeBg(record);
                    }, this);
                }
            }]
        });
        menu.showAt(e.getXY());
        e.stopPropagation();
        e.stopEvent();
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
                fieldLabel: Library.wording.bg_add,
                items: [{
                    xtype: 'field',
                    inputType: 'file',
                    name: 'bgfile',
                    allowBlank: false
                }, {
                    hideLabel: true,
                    xtype: 'button',
                    text: Library.wording.add_book_button,
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
            emptyText: Library.wording.bg_empty,
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
                dblclick: {scope: this, fn: this.backgroundClicked},
                contextmenu: {scope: this, fn: this.showContextMenu}
            }
        }, config);
    },
    
    initComponent: function() {
        Ext.apply(this, {
            modal: true,
            width: 700,
            height: 420,
            title: Library.wording.bg_title,
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
