Ext.ns('Library.admin')

Library.admin.App = Ext.extend(Library.App, {

    addBook: function(btn) {
        this.getGrid().getBookInfo(null, {modal: true});
    },

    importBooks: function(btn) {
        btn.disable();
        var win = new Ext.Window({
            title: Library.wording.import_book_button,
            modal: true,
            resizable: false,
            width: 400,
            height: 120,
            items: {
                xtype: 'form',
                bodyStyle: 'padding: 10px;',
                fileUpload: true,
                border: false,
                items: {
                    xtype: 'textfield',
                    inputType: 'file',
                    name: 'csv',
                    fieldLabel: 'Fichier'
                },
                buttons: [{
                    text: 'Importer',
                    scale: 'medium',
                    iconCls: 'book-import',
                    scope: this,
                    handler: function() {
                        win._mask.show();
                        win.getComponent(0).getForm().submit({
                            url: Library.Main.config().controller,
                            params: {
                                cmd: 'import'
                            },
                            scope: this,
                            success: function(form, action) {
                                win._mask.hide();
                                this.getGrid().getStore().reload();
                                win.close();
                            },
                            failure: function(form, action) {
                                win._mask.hide();
                                Library.Main.failureForm(action.result);
                            }
                        });
                    }
                }]
            },
            listeners: {
                afterrender: function(cmp) {
                    cmp._mask = new Ext.LoadMask(cmp.bwrap);
                },
                close: function() {
                    btn.enable();
                }
            }
        });
        win.show();
    },

    removeBooks: function(btn) {
        var grid = this.getGrid();
        var count = grid.getSelectionModel().getCount();
        if (count > 0) {
            Ext.Msg.confirm(Library.wording.delete_book_title, Library.wording.delete_book, function(choice){
                if (choice == 'yes') {
                    btn.disable();
                    var selected = grid.getSelectionModel().getSelections();
                    var ids = {};
                    for (var i = 0; i < selected.length; i++) {
                        ids['ids[' + i + ']'] = selected[i].get('id');
                    }
                    grid.loadMask.show();
                    Ext.Ajax.request({
                        url: Library.Main.config().controller,
                        params: Ext.apply(ids, {
                            cmd: 'removeBook'
                        }),
                        scope: this,
                        success: function(response) {
                            var json = Library.Main.getJson(response);
                            if (json.success) {
                                this.afterRemoveBooks(btn, selected, json);
                            } else {
                                grid.loadMask.hide();
                                btn.enable();
                            }
                        },
                        failure: function(response) {
                            grid.loadMask.hide();
                            btn.enable();
                            Library.Main.failure(response);
                        }
                    });
                }
            }, this);
        }
    },

    afterRemoveBooks: function(btn, selected, json) {
        this.getGrid().getStore().reload();
        btn.enable();
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

    initLoginButton: function() {
        return {
            text: Library.wording.connect_logout,
            scale: 'medium',
            iconCls: 'book-deconnect',
            scope: this,
            handler: this.logout
        };
    },

    initActionButtons: function() {
        return [{
            text: Library.wording.add_book_button,
            iconCls: 'book-add',
            scale: 'medium',
            scope: this,
            handler: this.addBook
        },{
            text: Library.wording.delete_book_button,
            iconCls: 'book-delete',
            scale: 'medium',
            scope: this,
            handler: this.removeBooks
        },{
            text: Library.wording.import_book_button,
            iconCls: 'book-import',
            scale: 'medium',
            scope: this,
            handler: this.importBooks
        }];
    },

    initComponent: function() {
        this.bookgridxtype = 'bookgridadmin';
        Library.admin.App.superclass.initComponent.apply(this, arguments);
    }

});
