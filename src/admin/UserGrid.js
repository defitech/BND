Ext.ns('Library.admin');

Library.admin.UserGrid = Ext.extend(Ext.grid.EditorGridPanel, {
    
    /**
     * Envoie au serveur l'information d'ajout ou de suppression d'un type
     * d'utilisateur
     * 
     * @param {String} action
     * @param {Ext.Window} win
     * @param {String} newValue la nouvelle valeur dans un cas d'edition
     * @return void
     */
    manageUserType: function(action, win, newValue) {
        win._mask.show();
        var form = win.getComponent(0).getForm();
        var combo = form.findField('type_id');
        // preparation de la requete ajax
        Ext.Ajax.request({
            url: Library.Main.config().controller,
            params: {
                // formation de la commande en fonction de l'action
                cmd: action + 'UserType',
                // l'id du type selectionne, si suppression
                id: combo.getValue(),
                // la valeur du nouveau type, si ajout
                value: newValue
            },
            success: function(response) {
                win._mask.hide();
                var json = Library.Main.getJson(response);
                if (json.success) {
                    // on envoie un event pour informer que l'action s'est bien
                    // deroulee cote serveur
                    win.fireEvent('usertypeaction', win, action, json);
                    // on recupere le record selectionne
                    var r = combo.findRecord('id', combo.getValue());
                    switch (action) {
                        case 'add':
                            // on ajoute le record dans le store de la popup
                            combo.getStore().add(new Ext.data.Record({
                                id: json.id,
                                value: json.value
                            }));
                            // on vide le champ
                            combo.reset()
                            break;
                        case 'edit':
                            r.set('value', json.value);
                            combo.setValue(json.id);
                            break;
                        case 'remove':
                            // on supprime le record du store de la popup
                            combo.getStore().remove(r);
                            // on remet a zero l'affichage du combo
                            combo.reset();
                    }
                }
            },
            failure: function(response) {
                win._mask.hide();
                Library.Main.failure(response);
            }
        });
    },
    
    /**
     * Cree la fenetre qui permet d'ajouter, modifier ou supprimer un type
     * d'utilisateur
     * 
     * @return {Ext.Window}
     */
    getUserTypeWindow: function() {
        var me = this;
        // definition du store du combo des types d'utilisateur
        var store = {
            xtype: 'arraystore',
            proxy: new Ext.data.HttpProxy({
                url: Library.Main.config().controller
            }),
            baseParams: {
                cmd: 'getUserTypes'
            },
            fields: ['id', 'value']
        };
        // definition de la fenetre de gestion des types d'utilisateur
        var win = new Ext.Window({
            title: Library.wording.user_type_title,
            modal: true,
            width: 380,
            height: 120,
            layout: 'fit',
            items: [{
                xtype: 'form',
                border: false,
                bodyStyle: 'padding: 10px',
                items: [{
                    xtype: 'compositefield',
                    fieldLabel: Library.wording.user_type_edit,
                    items: [{
                        xtype: 'combo',
                        hiddenName: 'type_id',
                        mode: 'remote',
                        triggerAction: 'all',
                        store: store,
                        displayField: 'value',
                        valueField: 'id',
                        emptyText: Library.wording.user_type_choose,
                        flex: 1
                    }, {
                        xtype: 'button',
                        iconCls: 'book-relation-add',
                        handler: function() {
                            Ext.Msg.prompt(Library.wording.user_type_title, Library.wording.user_type_add, function(choice, txt){
                                if (choice == 'ok' && txt) {
                                    me.manageUserType('add', win, txt);
                                }
                            });
                        }
                    }, {
                        xtype: 'button',
                        iconCls: 'book-relation-edit',
                        handler: function() {
                            var combo = win.getComponent(0).getForm().findField('type_id');
                            if (!combo.getValue()) return;
                            
                            Ext.Msg.prompt(Library.wording.user_type_title, Library.wording.user_type_edit, function(choice, txt){
                                if (choice == 'ok' && txt) {
                                    me.manageUserType('edit', win, txt);
                                }
                            }, this, false, combo.getRawValue());
                        }
                    }, {
                        xtype: 'button',
                        iconCls: 'book-relation-remove',
                        handler: function() {
                            var combo = win.getComponent(0).getForm().findField('type_id');
                            if (!combo.getValue()) return;
                            
                            Ext.Msg.confirm(Library.wording.user_type_title, Library.wording.user_type_remove, function(choice){
                                if (choice == 'yes')
                                    me.manageUserType('remove', win);
                            });
                        }
                    }]
                }]
            }],
            buttons: [{
                text: Library.wording.info_book_close,
                iconCls: 'book-window-close',
                scale: 'medium',
                handler: function() {
                    win.close();
                }
            }],
            listeners: {
                afterrender: function(cmp) {
                    cmp._mask = new Ext.LoadMask(cmp.bwrap);
                },
                destroy: function(cmp) {
                    delete cmp._mask;
                },
                usertypeaction: function(w, action, json) {
                    // ici, on est juste apres la reussite du serveur quant a
                    // l'action sur un type d'utiliateur
                    var i = 0;
                    // on va recuperer la position de la colonne du type
                    while (me.getColumnModel().getDataIndex(i)) {
                        if (me.getColumnModel().getDataIndex(i) == 'type_id')
                            break;
                        i++;
                    }
                    // on recupere le combo de la cellule (CellEditor)
                    var field = me.getColumnModel().getCellEditor(i, 0).field;
                    var r = field.findRecord('id', json.id);
                    switch (action) {
                        case 'add':
                            // si on ajoutait un type, on le met dans le store
                            field.getStore().add(new Ext.data.Record({
                                id: json.id,
                                value: json.value
                            }));
                            break;
                        case 'edit':
                            r.set('value', json.value);
                            break;
                        case 'remove':
                            // si on supprimait un type, on l'enleve du store
                            field.getStore().remove(r);
                    }
                }
            }
        });
        return win;
    },
    
    
    
    

    addUser: function() {
        var User = this.getStore().recordType;
        var u = new User({
            login: '',
            pass: '',
            right: 0,
            type_id: null
        });
        this.stopEditing();
        this.getStore().insert(0, u);
        this.startEditing(0, 0);
    },

    removeUser: function(record) {
        if (!record || !record.data) {
            var row = this.getSelectionModel().getSelectedCell();
            record = this.getStore().getAt(row[0]);
        }
        Ext.Msg.confirm(Library.wording.user_delete_title, String.format(Library.wording.user_delete, record.get('login')), function(choice){
            if (choice == 'yes') {
                this.sendRemoveUser(record);
            }
        }, this);
    },

    sendRemoveUser: function(record, forceConfirm) {
        Ext.Ajax.request({
            url: Library.Main.config().controller,
            scope: this,
            params: {
                cmd: 'removeUser',
                forceConfirm: forceConfirm,
                id: record.get('id')
            },
            success: function(response) {
                var json = Library.Main.getJson(response);
                if (json.success) {
                    if (json.confirm) {
                        Ext.Msg.confirm(Library.wording.user_delete_title, json.msg, function(choice){
                            if (choice == 'yes') {
                                this.sendRemoveUser(record, true);
                            }
                        }, this)
                    } else {
                        this.removebutton.disable();
                        this.getStore().reload();
                    }
                }
            },
            failure: function(response) {
                Library.Main.failure(response);
            }
        });
    },

    saveUserData: function(e) {
        if (e.value != e.originalValue) {
            Ext.Ajax.request({
                url: Library.Main.config().controller,
                scope: this,
                params: {
                    cmd: 'saveUser',
                    id: e.record.get('id'),
                    field: e.field,
                    value: e.value
                },
                success: function(response) {
                    var json = Library.Main.getJson(response);
                    if (json.success) {
                        e.record.set('id', json.id);
                        e.record.commit();
                    }
                },
                failure: function(response) {
                    Library.Main.failure(response);
                }
            });
        }
    },

    showDownloads: function(record) {
        if (!record || !record.data) {
            var row = this.getSelectionModel().getSelectedCell();
            record = this.getStore().getAt(row[0]);
        }
        this.fireEvent('showdownloads', this, record)
    },

    initContextMenu: function(record) {
        return new Ext.menu.Menu({
            items: [{
                xtype: 'panel',
                html: record.get('login'),
                cls: 'book-menu-title',
                border: false
            },{
                text: Library.wording.delete_book_button,
                iconCls: 'book-delete-small',
                scope: this,
                handler: function() {
                    this.removeUser(record);
                }
            }, {
                text: Library.wording.user_button_dl,
                iconCls: 'book-download-small',
                scope: this,
                handler: function() {
                    this.showDownloads(record);
                }
            }]
        });
    },

    initUserStore: function() {
        return new Ext.data.GroupingStore({
            reader: new Ext.data.JsonReader({
                root: 'users',
                fields: [
                    {name: 'id'},
                    {name: 'login'},
                    {name: 'pass'},
                    {name: 'right'},
                    {name: 'type_id'},
                    {name: 'type_text'}
                ]
            }),
            url: Library.Main.config().controller,
            baseParams: {
                cmd: 'getUserList'
            },
            sortInfo   : {
                field     : 'login',
                direction : 'ASC'
            },
            remoteSort: true,
            groupField: 'type_text'
        });
    },

    initUsersHeader: function() {
        var me = this;
        return new Ext.grid.ColumnModel({
            defaults: {
                sortable: true
            },
            columns: [
                {
                    header : 'ID',
                    hidden: true,
                    dataIndex : 'id'
                },
                {
                    header : Library.wording.connect_login,
                    dataIndex : 'login',
                    editor: new Ext.form.TextField({
                        allowBlank: false
                    })
                },
                {
                    header : Library.wording.connect_password,
                    dataIndex : 'pass',
                    editor: new Ext.form.TextField({
                        allowBlank: false,
                        inputType: 'password'
                    })
                },
                {
                    header : Library.wording.user_right,
                    dataIndex : 'right',
                    editor: new Ext.form.NumberField({
                        allowBlank: false,
                        allowNegative: false,
                        maxValue: 10
                    })
                },
                {
                    header : '&nbsp;',
                    dataIndex : 'type_text',
                    hidden: true,
                    hideable: false
                },
                {
                    header: Library.wording.user_type,
                    dataIndex: 'type_id',
                    editor: new Ext.form.ComboBox({
                        allowBlank: false,
                        mode: 'local',
                        displayField: 'value',
                        valueField: 'id',
                        store: new Ext.data.ArrayStore({
                            fields: ['id', 'value'],
                            data: Library.Main.config().userTypes
                        })
                    }),
                    renderer: function(value, metaData, record, rowIndex, colIndex){
                        if (!value) return '';
                        
                        var combo = me.getColumnModel().getCellEditor(colIndex, rowIndex).field;
                        var index = combo.getStore().find('id', value);
                        var r = combo.getStore().getAt(index);
                        return r ? r.get('value') : '';
                    }
                }
            ]
        });
    },

    initFilters: function() {
        return new Ext.ux.grid.GridFilters({
            filters: [{
                type: 'string',
                dataIndex: 'login'
            }]
        });
    },

    initComponent: function() {
        this.addEvents('showdownloads');
        var filters = this.initFilters();
        Ext.apply(this, {
            store: this.initUserStore(),
            plugins: [filters],
            loadMask: true,
            columnLines: false,
            colModel: this.initUsersHeader(),
            view: new Ext.grid.GroupingView({
                forceFit: true,
                stripeRows: true,
                enableGroupingMenu: false,
                enableNoGroups: false,
                groupTextTpl: Library.wording.user_group_text
            }),
            tbar: [{
                text: Library.wording.add_book_button,
                iconCls: 'book-add-small',
                scope: this,
                handler: this.addUser
            }, '-', {
                text: Library.wording.delete_book_button,
                disabled: true,
                iconCls: 'book-delete-small',
                ref: '../removebutton',
                scope: this,
                handler: this.removeUser
            }, '->', {
                text: Library.wording.user_button_dl,
                iconCls: 'book-download-small',
                scope: this,
                handler: this.showDownloads
            }, '-', {
                iconCls: 'book-user-type',
                tooltip: Library.wording.user_type_title,
                scope: this,
                handler: function() {
                    var win = this.getUserTypeWindow();
                    win.show();
                }
            }, '-', {
                iconCls: 'book-refresh',
                scope: this,
                handler: function() {
                    this.getStore().load();
                }
            }],
            listeners: {
                afterrender: {scope: this, fn: function(grid){
                    grid.getStore().load();
                }},
                afteredit: {scope: this, fn: this.saveUserData},
                rowclick: {scope: this, fn: function(){
                    this.removebutton.enable();
                }},
                cellclick: {scope: this, fn: function(grid, row, column){
                    // si on est sur la colonne du login, on affiche les livres
                    // telecharges de la personne
                    if (grid.getColumnModel().getDataIndex(column) == 'login')
                        this.showDownloads(this.getStore().getAt(row));
                }},
                rowcontextmenu: {scope: this, fn: function(grid, index, e){
                    var contextmenu = this.initContextMenu(grid.getStore().getAt(index));
                    contextmenu.showAt(e.getXY());
                    e.stopEvent();
                }}
            }
        });
        Library.admin.UserGrid.superclass.initComponent.apply(this, arguments);
    }

});
