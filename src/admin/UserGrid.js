Ext.ns('Library.admin');

Library.admin.UserGrid = Ext.extend(Ext.grid.EditorGridPanel, {

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
                        return r.get('value');
                    }
                }
            ]
        });
    },

    initComponent: function() {
        this.addEvents('showdownloads');
        Ext.apply(this, {
            store: this.initUserStore(),
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
