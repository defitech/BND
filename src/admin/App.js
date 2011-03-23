Ext.ns('Library.admin')

Library.admin.App = Ext.extend(Library.App, {

    checkForNewBooksTpl: new Ext.XTemplate(
        '<div class="book-help">',
            '<p>{text}</p><br/>',
            '<ul>',
                '<tpl for="data">',
                '<li class="book-new-check-{success}">{title} ({file}). Miniature: {thumb}</li>',
                '</tpl>',
            '</ul>',
        '</div>'
    ),

    addBook: function(btn) {
        this.getGrid().getBookInfo(null, {modal: true});
    },

    checkForNewBooks: function(mask, start, total, result) {
        if (!mask) {
            // creation de la progressbar si elle n'existe pas
            mask = Ext.Msg.progress(Library.wording.search_for_new_books_title, '', Library.wording.book_moved_first);
        }
        // element a traiter dans la pile
        start = start || 0;
        result = result || [];
        // envoi de la requete
        Ext.Ajax.request({
            url: Library.Main.config().controller,
            params: {
                cmd: 'checkNewBooks',
                start: start
            },
            scope: this,
            success: function(response) {
                var json = Library.Main.getJson(response);
                // recuperation du total. Il est pris sur le nombre de pdf presents
                // dans le dossier temporaire. Ce nombre va en diminuant a chaque
                // nouvelle requete car les fichiers sont deplaces au fur et a
                // mesure dans le dossier d'upload. On ne prend donc en compte
                // que le premier total qui correspond au nombre total courant
                // de pdf, avant traitement
                if (!total) total = json.total;
                // nombre d'elements traites jusqu'a present
                start++;
                result = result.concat(json.data || []);
                if (json.success) {
                    if (json.next && !json.stop) {
                        // on continue, donc on modifie la progressbar
                        mask.updateProgress(start / total || 1, String.format(Library.wording.book_moved, start, total));
                        // on modifie le tableau de resultat affiche a la fin du processus
                        // on lance une nouvelle fois la requete
                        this.checkForNewBooks(mask, start, total, result);
                    } else {
                        // on a fini le processus. On affiche le resultat
                        mask.hide();
                        Ext.Msg.alert(Library.wording.search_for_new_books_title, this.checkForNewBooksTpl.apply({
                            data: result,
                            text: String.format(Library.wording.book_moved_finish, total)
                        }));
                    }
                }
            },
            failure: function(response) {
                mask.hide();
                Library.Main.failure(response);
            }
        })
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
                    fieldLabel: Library.wording.file
                },
                buttons: [{
                    text: Library.wording.import_book_button,
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

    removeBooks: function(btn, e, selected) {
        var grid = this.getGrid();
        var count = grid.getSelectionModel().getCount();
        if (count > 0) {
            Ext.Msg.confirm(Library.wording.delete_book_title, Library.wording.delete_book, function(choice){
                if (choice == 'yes') {
                    if (!selected) {
                        selected = grid.getSelectionModel().getSelections();
                    }
                    var ids = {};
                    for (var i = 0; i < selected.length; i++) {
                        ids['ids[' + i + ']'] = selected[i].get('id');
                    }
                    this.removeBooksSubmit(ids, btn, selected);
                }
            }, this);
        }
    },

    removeBooksSubmit: function(ids, btn, selected, forceConfirm) {
        var grid = this.getGrid();
        grid.loadMask.show();
        if (btn) btn.disable();
        Ext.Ajax.request({
            url: Library.Main.config().controller,
            params: Ext.apply(ids, {
                cmd: 'removeBook',
                forceConfirm: forceConfirm
            }),
            scope: this,
            success: function(response) {
                var json = Library.Main.getJson(response);
                grid.loadMask.hide();
                if (btn) btn.enable();
                if (json.success) {
                    if (json.confirm) {
                        var str = grid.getSelectionModel().getCount() > 1 ? Library.wording.delete_book_confirmmany : Library.wording.delete_book_confirmone;
                        Ext.Msg.confirm(Library.wording.delete_book_title, str, function(choice){
                            if (choice == 'yes') {
                                this.removeBooksSubmit(ids, btn, selected, true);
                            }
                        }, this);
                    } else {
                        grid.getStore().reload();
                    }
                }
            },
            failure: function(response) {
                grid.loadMask.hide();
                if (btn) btn.enable();
                Library.Main.failure(response);
            }
        });
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
        var users = null;
        return [{
            text: Library.wording.user_button,
            iconCls: 'book-user',
            scale: 'medium',
            scope: this,
            handler: function() {
                if (!users) {
                    users = new Ext.Window({
                        modal: true,
                        title: Library.wording.user_title,
                        width: 700,
                        height: 300,
                        layout: 'fit',
                        closeAction: 'hide',
                        items: new Library.admin.UserPanel({
                            listeners: {
                                bookget: {scope: this, fn: function(grid, record){
                                    this.getGrid().getBookInfo(record, {modal: true, forceReadOnly: true});
                                }}
                            }
                        })
                    });
                }
                users.show();
            }
        }, '-', {
            xtype: 'splitbutton',
            text: Library.wording.add_book_button,
            iconCls: 'book-add',
            scale: 'medium',
            scope: this,
            handler: this.addBook,
            menu: {
                items: [{
                    text: Library.wording.search_for_new_books,
                    scope: this,
                    iconCls: 'book-search-import',
                    handler: function() {
                        Ext.Msg.confirm(Library.wording.search_for_new_books_title, Library.wording.search_for_new_books_confirm, function(choice){
                            if (choice == 'yes') {
                                this.checkForNewBooks();
                            }
                        }, this);
                    }
                }]
            }
        },{
            text: Library.wording.delete_book_button,
            iconCls: 'book-delete',
            scale: 'medium',
            scope: this,
            handler: this.removeBooks
        }, '-', {
            text: Library.wording.import_book_button,
            iconCls: 'book-import',
            scale: 'medium',
            scope: this,
            handler: this.importBooks
        }, '-'];
    },

    initComponent: function() {
        this.bookgridxtype = 'bookgridadmin';
        this.booklisteners = {
            bookdelete: {scope: this, fn: function(grid, record){
                this.removeBooks(null, null, [record]);
            }}
        };
        Library.admin.App.superclass.initComponent.apply(this, arguments);
    }

});
