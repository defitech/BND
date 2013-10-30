Ext.ns('Library');

Library.App = Ext.extend(Ext.Viewport, {

    showHelpScreen: function() {
        var win = new Ext.Window({
            modal: true,
            width: 500,
            height: 400,
            title: Library.wording.help,
            layout: 'fit',
            items: [{
                xtype: 'panel',
                cls: 'book-help',
                autoLoad: 'help.php',
                border: false,
                autoScroll: true
            }],
            buttons: ['->', {
                text: Library.wording.info_book_close,
                iconCls: 'book-window-close',
                scale: 'medium',
                tabIndex: 1,
                handler: function() {
                    win.close();
                }
            }]
        });
        win.show();
    },
    
    showProfile: function() {
        var pid = Ext.id();
        var win = new Ext.Window({
            modal: true,
            width: 300,
            height: 240,
            title: Library.wording.button_profile,
            border: false,
            layout: 'fit',
            items: {
                xtype: 'form',
                url: Library.Main.config().controller,
                baseParams: {
                    cmd: 'saveCurrentUser'
                },
                bodyStyle: 'padding: 10px;',
                defaults: {
                    anchor: '100%'
                },
                items: [{
                    xtype: 'displayfield',
                    readOnly: true,
                    name: 'login',
                    fieldLabel: Library.wording.connect_login
                }, {
                    xtype: 'textfield',
                    fieldLabel: Library.wording.user_email,
                    allowBlank: false,
                    name: 'email',
                    vtype: 'email'
                }, {
                    xtype: 'fieldset',
                    title: Library.wording.connect_password,
                    collapsible: true,
                    collapsed: true,
                    items: [{
                        xtype: 'textfield',
                        inputType: 'password',
                        id: pid,
                        fieldLabel: Library.wording.connect_password,
                        name: 'pass'
                    }, {
                        xtype: 'textfield',
                        inputType: 'password',
                        initialPassField: pid,
                        fieldLabel: Library.wording.connect_password_confirm,
                        name: 'pass_confirm',
                        vtype: 'password'
                    }]
                }]
            },
            buttons: [
            {
                text: Library.wording.info_book_save,
                iconCls: 'book-save',
                scale: 'medium',
                handler: function() {
                    if (!win.getComponent(0).getForm().isValid())
                        return;
                    
                    win.getComponent(0).getForm().submit({
                        success: function() {
                            Ext.Msg.alert(Library.wording.button_profile, Library.wording.profile_ok, function(){
                                win.close();
                            });
                        },
                        failure: function(action, result) {
                            Library.Main.failureForm(result.action);
                        }
                    });
                }
            },{
                text: Library.wording.info_book_close,
                iconCls: 'book-window-close',
                scale: 'medium',
                handler: function() {
                    win.close();
                }
            }],
            listeners: {
                show: function() {
                    Ext.Ajax.request({
                        url: Library.Main.config().controller,
                        params: {
                            cmd: 'getUser'
                        },
                        success: function(response) {
                            var json = Library.Main.getJson(response);
                            if (!json.success)
                                return;
                            
                            win.getComponent(0).getForm().setValues(json.record);
                        },
                        failure: function(response) {
                            Library.Main.failure(response);
                        }
                    });
                }
            }
        });
        win.show();
    },

    doBookSearch: function() {
        var search = this.searchbox.getValue();
        var grid = this.getGrid();
        grid.getStore().setBaseParam('filters[fullsearch]', search);
        grid.filters.reload();
        // s'il y a un fullsearch, on informe la grid pour pouvoir faire un
        // undo grace au clic-droite
        grid.hasFullsearch = search ? true : false;
    },

    undoBookSearch: function() {
        var grid = this.getGrid();
        this.searchbox.setValue(null);
        grid.getStore().setBaseParam('filters[fullsearch]', '');
        var current = grid.filters.getFilterData();
        grid.filters.clearFilters();
        if (current.length == 0) {
            grid.getStore().reload();
        }
        // on informe la grid pour desactiver le undo du clic-droite
        grid.hasFullsearch = false;
    },

    downloadBook: function() {
        this.getGrid().launchDownload(this.getGrid().getSelectionModel().getSelected());
    },

    getBookInfo: function() {
        this.getGrid().getBookInfo(this.getGrid().getSelectionModel().getSelected());
    },

    getGrid: function() {
        return this.getComponent(0).getComponent(0);
    },

    initActionButtons: function() {
        return [];
    },

    logout: function() {
        Ext.Ajax.request({
            url: Library.Main.config().controller,
            params: {
                cmd: 'logout'
            },
            scope: this,
            success: function(response) {
                var json = Library.Main.getJson(response);
                if (json.success) {
                    window.location.reload();
                }
            },
            failure: function(response) {
                Library.Main.failure(response);
            }
        });
    },
    
    initProfileButton: function() {
        return {
            text: Library.wording.button_profile,
            iconCls: 'user-profile',
            scale: 'medium',
            scope: this,
            handler: this.showProfile
        };
    },

    initLoginButton: function() {
        return {
            text: Library.wording.connect_logout,
            scale: 'medium',
            iconCls: 'book-deconnect',
            scope: this,
            handler: this.logout
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
                margins: '50 100',
                collapsible: true,
                tools: [{
                    id: 'help',
                    qtip: Library.wording.help,
                    scope: this,
                    handler: this.showHelpScreen
                }],
                items: [{
                    xtype: this.bookgridxtype || 'bookgrid',
                    border: false,
                    listeners: Ext.apply(this.booklisteners || {}, {
                        selectionchange: {scope: this, fn: function(grid, model) {
                            if (model.getCount() == 1) {
                                this.bookinfo.enable();
                                var record = model.getSelected();
                                if (record.get('filename'))
                                    this.bookdownload.enable();
                                else
                                    this.bookdownload.disable();
                            } else {
                                this.bookinfo.disable();
                                this.bookdownload.disable();
                            }
                        }},
                        focusfullsearch: {scope: this, fn: function() {
                            this.searchbox.focus();
                        }},
                        filterundo: {scope: this, fn: this.undoBookSearch}
                    })
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
                    {
                        text: Library.wording.download_book_button,
                        iconCls: 'book-download',
                        scale: 'medium',
                        ref: '../../bookdownload',
                        disabled: true,
                        scope: this,
                        handler: this.downloadBook
                    },
                    '->',
                    {
                        xtype: 'button',
                        scale: 'medium',
                        iconCls: 'book-search-go',
                        text: Library.wording.filter_title,
                        scope: this,
                        handler: this.doBookSearch
                    },{
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
                        iconCls: 'book-search-undo',
                        scope: this,
                        handler: this.undoBookSearch
                    }
                ])
            },{
                region: 'south',
                border: false,
                buttons: [
                    this.initProfileButton(),
                    this.initLoginButton()
                ]
            }]
        });
        Library.App.superclass.initComponent.apply(this, arguments);
    }

});
