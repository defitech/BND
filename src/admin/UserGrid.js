Ext.ns('Library.admin');

Library.admin.UserGrid = Ext.extend(Ext.grid.EditorGridPanel, {
    
    /**
     * Envoie par mail une proposition de changer son mot de passe à l'adresse
     * configurée dans le record
     * 
     * @param {Ext.data.Record} record
     * @return void
     */
    sendPasswordDemand: function(record) {
        if (!record.get('email'))
            return;
        
        var msg = String.format(Library.wording.user_passsend_msg, record.get('login'), record.get('email'));
        var buttonText = Ext.apply({}, Ext.Msg.buttonText);
        Ext.Msg.buttonText.yes = Library.wording.user_passsend_btn_mail;
        Ext.Msg.buttonText.no = Library.wording.user_passsend_btn_link;
        Ext.Msg.show({
            title : Library.wording.user_passsend,
            msg : msg,
            buttons: Ext.Msg.YESNOCANCEL,
            scope : this,
            icon: Ext.Msg.QUESTION,
            fn: function(choice) {
                Ext.Msg.buttonText = buttonText;
                if (choice === 'cancel')
                    return;

                var holdmail = choice === 'no';
                // on cree une fenetre d'attente. Normalement, on utilise Ext.Msg.wait()
                // mais la, y'a rien a faire...
                var box = new Ext.Window({
                    modal: true,
                    title: Library.wording.user_passsend,
                    height: 70,
                    width: 210,
                    closable: false,
                    plain: true,
                    border: false,
                    cls: 'window-wait-debug',
                    onEsc: Ext.emptyFn,
                    html: Library.wording.user_passsend_wait
                });
                box.show();

                Ext.Ajax.request({
                    url: Library.Main.config().controller,
                    scope: this,
                    params: {
                        cmd: 'remindPasswordCreate',
                        askforpass: record.get('email'),
                        holdmail: holdmail ? 1 : 0
                    },
                    success: function(response) {
                        box.close();
                        var json = Library.Main.getJson(response);
                        if (json.success) {
                            Ext.Msg.alert(Library.wording.user_passsend, json.msg);
                        }
                    },
                    failure: function(response) {
                        box.close();
                        Library.Main.failure(response);
                    }
                });
            }
        });
    },
    
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

    /**
     * Envoie au serveur l'information d'ajout ou de suppression d'un type
     * d'utilisateur
     * 
     * @param {String} action
     * @param {Ext.Window} win
     * @param {String} newValue la nouvelle valeur dans un cas d'edition
     * @return void
     */
    manageUserDeficiency: function(action, win, newValue) {
        win._mask.show();
        var form = win.getComponent(0).getForm();
        var combo = form.findField('deficiency_id');
        // preparation de la requete ajax
        Ext.Ajax.request({
            url: Library.Main.config().controller,
            params: {
                // formation de la commande en fonction de l'action
                cmd: action + 'UserDeficiency',
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
                    win.fireEvent('userdeficiencyaction', win, action, json);
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
     * Cree la fenetre qui permet d'ajouter, modifier ou supprimer une affection
     * 
     * @return {Ext.Window}
     */
    getUserDeficiencyWindow: function() {
        var me = this;
        // definition du store du combo des affections
        var store = {
            xtype: 'arraystore',
            proxy: new Ext.data.HttpProxy({
                url: Library.Main.config().controller
            }),
            baseParams: {
                cmd: 'getUserDeficiencies'
            },
            fields: ['id', 'value']
        };
        // definition de la fenetre de gestion des affections
        var win = new Ext.Window({
            title: Library.wording.user_deficiency_title,
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
                    fieldLabel: Library.wording.user_deficiency_edit,
                    items: [{
                        xtype: 'combo',
                        hiddenName: 'deficiency_id',
                        mode: 'remote',
                        triggerAction: 'all',
                        store: store,
                        displayField: 'value',
                        valueField: 'id',
                        emptyText: Library.wording.user_deficiency_choose,
                        flex: 1
                    }, {
                        xtype: 'button',
                        iconCls: 'book-relation-add',
                        handler: function() {
                            Ext.Msg.prompt(Library.wording.user_deficiency_title, Library.wording.user_deficiency_add, function(choice, txt){
                                if (choice == 'ok' && txt) {
                                    me.manageUserDeficiency('add', win, txt);
                                }
                            });
                        }
                    }, {
                        xtype: 'button',
                        iconCls: 'book-relation-edit',
                        handler: function() {
                            var combo = win.getComponent(0).getForm().findField('deficiency_id');
                            if (!combo.getValue()) return;
                            
                            Ext.Msg.prompt(Library.wording.user_deficiency_title, Library.wording.user_deficiency_edit, function(choice, txt){
                                if (choice == 'ok' && txt) {
                                    me.manageUserDeficiency('edit', win, txt);
                                }
                            }, this, false, combo.getRawValue());
                        }
                    }, {
                        xtype: 'button',
                        iconCls: 'book-relation-remove',
                        handler: function() {
                            var combo = win.getComponent(0).getForm().findField('deficiency_id');
                            if (!combo.getValue()) return;
                            
                            Ext.Msg.confirm(Library.wording.user_deficiency_title, Library.wording.user_deficiency_remove, function(choice){
                                if (choice == 'yes')
                                    me.manageUserDeficiency('remove', win);
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
                userdeficiencyaction: function(w, action, json) {
                    // ici, on est juste apres la reussite du serveur quant a
                    // l'action sur une affection
                    var i = 0;
                    // on va recuperer la position de la colonne du type
                    while (me.getColumnModel().getDataIndex(i)) {
                        if (me.getColumnModel().getDataIndex(i) == 'deficiency_id')
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
            type_id: null,
            deficiency_id: null
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
                    {name: 'email'},
                    {name: 'type_id'},
                    {name: 'type_text'},
                    {name: 'deficiency_id'},
                    {name: 'deficiency_text'},
                    {name: 'confirmed'},
                    {name: 'inactive'}
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
                    width: 55,
                    dataIndex : 'pass',
                    editor: new Ext.form.TextField({
                        allowBlank: false,
                        inputType: 'password'
                    })
                },
                {
                    header : Library.wording.user_right,
                    dataIndex : 'right',
                    hidden: true,
                    editor: new Ext.form.NumberField({
                        allowBlank: false,
                        allowNegative: false,
                        maxValue: 10
                    })
                },
                {
                    header : Library.wording.user_email,
                    width: 55,
                    dataIndex : 'email',
                    editor: new Ext.form.TextField({
                        allowBlank: true,
                        vtype: 'email'
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
                },
                {
                    header : '&nbsp;',
                    dataIndex : 'deficiency_text',
                    hidden: true,
                    hideable: false
                },
                {
                    header: Library.wording.user_deficiency,
                    dataIndex: 'deficiency_id',
                    editor: new Ext.form.ComboBox({
                        allowBlank: false,
                        mode: 'local',
                        displayField: 'value',
                        valueField: 'id',
                        store: new Ext.data.ArrayStore({
                            fields: ['id', 'value'],
                            data: Library.Main.config().userDeficiencies
                        })
                    }),
                    renderer: function(value, metaData, record, rowIndex, colIndex){
                        if (!value) return '';
                        
                        var combo = me.getColumnModel().getCellEditor(colIndex, rowIndex).field;
                        var index = combo.getStore().find('id', value);
                        var r = combo.getStore().getAt(index);
                        return r ? r.get('value') : '';
                    }
                },
                {
                    xtype: 'actioncolumn',
                    width: 44,
                    height: 22,
                    resizeable: false,
                    sortable: false,
                    hideable: false,
                    items: [{
                        getClass: function(v, meta, record) {
                            var cls = 'book-user-form';
                            var tip = Library.wording.user_sent_form;
                            if (record.get('confirmed') != 1) {
                                cls += '-no';
                                tip = Library.wording.user_sent_form_no;
                            }
                            this.items[0].tooltip = tip;
                            return cls;
                        },
                        handler: function(grid, row, col) {
                            var record = grid.getStore().getAt(row);
                            var newval;
                            if ( record.get('confirmed') != 1 ) {
                                newval = 1;
                            } else {
                                newval = 0;
                            }
                            record.set('confirmed', newval);
                            Ext.Ajax.request({
                                url: Library.Main.config().controller,
                                scope: this,
                                params: {
                                    cmd: 'saveUser',
                                    id: record.get('id'),
                                    field: 'confirmed',
                                    value: newval
                                },
                                success: function(response) {
                                    var json = Library.Main.getJson(response);
                                    if (json.success) {
                                        record.set('id', json.id);
                                        record.commit();
                                    }
                                },
                                failure: function(response) {
                                    Library.Main.failure(response);
                                }
                            });
                        },
                    },{
                        getClass: function(v, meta, record) {
                            var cls = 'book-user-sendpass';
                            var tip = String.format(Library.wording.user_passsend_tip, record.get('email'));
                            if (!record.get('email')) {
                                cls += '-off';
                                tip = Library.wording.user_passsend_tip_no;
                            }
                            this.items[1].tooltip = tip;
                            return cls;
                        },
                        handler: function(grid, row, col) {
                            var record = grid.getStore().getAt(row);
                            me.sendPasswordDemand(record);
                        }
                    },{
                        getClass: function(v, meta, record) {
                            var cls = 'book-user-active';
                            var tip = Library.wording.user_active;
                            if (record.get('inactive') == 1) {
                                cls += '-no';
                                tip = Library.wording.user_inactive;
                            }
                            this.items[0].tooltip = tip;
                            return cls;
                        },
                        handler: function(grid, row, col) {
                            var record = grid.getStore().getAt(row);
                            var newval;
                            if ( record.get('inactive') != 1 ) {
                                newval = 1;
                            } else {
                                newval = 0;
                            }
                            record.set('inactive', newval);
                            Ext.Ajax.request({
                                url: Library.Main.config().controller,
                                scope: this,
                                params: {
                                    cmd: 'saveUser',
                                    id: record.get('id'),
                                    field: 'inactive',
                                    value: newval
                                },
                                success: function(response) {
                                    var json = Library.Main.getJson(response);
                                    if (json.success) {
                                        record.set('id', json.id);
                                        record.commit();
                                    }
                                },
                                failure: function(response) {
                                    Library.Main.failure(response);
                                }
                            });
                        },
                    }]
                }
            ]
        });
    },

    initFilters: function() {
        return new Ext.ux.grid.GridFilters({
            filters: [{
                type: 'string',
                dataIndex: 'login'
            }, {
                type: 'string',
                dataIndex: 'email'
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
                iconCls: 'book-user-deficiency',
                tooltip: Library.wording.user_deficiency_title,
                scope: this,
                handler: function() {
                    var win = this.getUserDeficiencyWindow();
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
