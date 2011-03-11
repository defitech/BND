Ext.ns('Library');

Library.App = Ext.extend(Ext.Viewport, {

    doBookSearch: function() {
        var search = this.searchbox.getValue();
        var grid = this.getGrid();
        grid.getStore().setBaseParam('filters[fullsearch]', search);
        grid.filters.reload();
    },

    undoBookSearch: function() {
        var grid = this.getGrid();
        this.searchbox.setValue(null);
        grid.getStore().setBaseParam('filters[fullsearch]', '');
        var current_filters = grid.filters.getFilterData();
        grid.filters.clearFilters();
        if (current_filters.length == 0) {
            grid.getStore().reload();
        }
    },

    getBookInfo: function() {
        this.getGrid().getBookInfo(this.getGrid().getSelectionModel().getSelected());
    },

    getGrid: function() {
        return this.getComponent(0).getComponent(0);
    },

    login: function() {
        var win = new Ext.Window({
            modal: true,
            width: 300,
            height: 150,
            resizable: false,
            layout: 'fit',
            title: Library.wording.connect_title,
            items: {
                xtype: 'form',
                border: false,
                bodyStyle: 'padding: 10px;',
                items: [{
                    xtype: 'textfield',
                    fieldLabel: Library.wording.connect_login,
                    name: 'login'
                },{
                    xtype: 'textfield',
                    fieldLabel: Library.wording.connect_password,
                    name: 'pass',
                    inputType: 'password',
                    enableKeyEvents: true,
                    listeners: {
                        keypress: {scope: this, fn: function(field, e){
                            if (e.getKey() == e.ENTER) this.doLogin(win);
                        }}
                    }
                }],
                bbar: ['->', {
                    text: Library.wording.connect_title,
                    scale: 'medium',
                    iconCls: 'book-connect',
                    scope: this,
                    handler: function() {
                        this.doLogin(win);
                    }
                }]
            },
            listeners: {
                afterrender: function(cmp) {
                    cmp.bmask = new Ext.LoadMask(cmp.bwrap);
                },
                show: function(cmp) {
                    cmp.getComponent(0).getForm().findField('login').focus();
                }
            }
        });
        win.show();
    },

    doLogin: function(win) {
        var login = win.getComponent(0).getForm().findField('login');
        var pass = win.getComponent(0).getForm().findField('pass');
        win.bmask.show();
        Ext.Ajax.request({
            url: Library.Main.config().controller,
            params: {
                cmd: 'login',
                login: login.getValue(),
                pass: pass.getValue()
            },
            scope: this,
            success: function(response) {
                var json = Library.Main.getJson(response);
                if (json.success) {
                    window.location.reload();
                } else {
                    win.bmask.hide();
                }
            },
            failure: function(response) {
                win.bmask.hide();
                Library.Main.failure(response);
            }
        });
    },

    initActionButtons: function() {
        return [];
    },

    initLoginButton: function() {
        return {
            text: Library.wording.connect_title,
            scale: 'medium',
            iconCls: 'book-connect',
            scope: this,
            handler: this.login
        };
    },

    initComponent: function() {
        Ext.apply(this, {
            layout: {
                type: 'border'
            },
            items: [{
                region: 'center',
                iconCls: 'book-main',
                title: Library.wording.library_title,
                layout: 'fit',
                margins: '50',
                items: [{
                    xtype: this.bookgridxtype || 'bookgrid',
                    border: false,
                    listeners: {
                        selectionchange: {scope: this, fn: function(grid, model) {
                            if (model.getCount() == 1) {
                                this.bookinfo.enable();
                            } else {
                                this.bookinfo.disable();
                            }
                        }}
                    }
                }],
                tbar: this.initActionButtons().concat([
                    {
                        text: Library.wording.info_book_button,
                        iconCls: 'book-info',
                        scale: 'medium',
                        ref: '../../bookinfo',
                        disabled: true,
                        scope: this,
                        handler: this.getBookInfo
                    },
                    '->',
                    {
                        xtype: 'textfield',
                        name: 'book-search',
                        ref: '../../searchbox',
                        enableKeyEvents: true,
                        listeners: {
                            keypress: {scope: this, fn: function(field, e){
                                if (e.getKey() == e.ENTER) {
                                    this.doBookSearch();
                                }
                            }}
                        }
                    },{
                        xtype: 'button',
                        scale: 'medium',
                        iconCls: 'book-search-go',
                        scope: this,
                        handler: this.doBookSearch
                    },{
                        xtype: 'button',
                        scale: 'medium',
                        iconCls: 'book-search-undo',
                        scope: this,
                        handler: this.undoBookSearch
                    }
                ])
            },{
                region: 'south',
                border: false,
                buttons: [this.initLoginButton()]
            }]
        });
        Library.App.superclass.initComponent.apply(this, arguments);
    }

});
