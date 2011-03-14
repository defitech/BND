Ext.ns('Library.admin');

Library.admin.UserGrid = Ext.extend(Ext.grid.EditorGridPanel, {

    addUser: function() {
        var User = this.getStore().recordType;
        var u = new User({
            login: '',
            pass: '',
            right: 0
        });
        this.stopEditing();
        this.getStore().insert(0, u);
        this.startEditing(0, 0);
    },

    removeUser: function() {
        var row = this.getSelectionModel().getSelectedCell();
        var record = this.getStore().getAt(row[0]);
        Ext.Msg.confirm(Library.wording.user_delete_title, String.format(Library.wording.user_delete, record.get('login')), function(choice){
            if (choice == 'yes') {
                Ext.Ajax.request({
                    url: Library.Main.config().controller,
                    scope: this,
                    params: {
                        cmd: 'removeUser',
                        id: record.get('id')
                    },
                    success: function(response) {
                        var json = Library.Main.getJson(response);
                        if (json.success) {
                            this.removebutton.disable();
                            this.getStore().reload();
                        }
                    },
                    failure: function(response) {
                        Library.Main.failure(response);
                    }
                });
            }
        }, this);
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

    initUserStore: function() {
        return new Ext.data.JsonStore({
            url: Library.Main.config().controller,
            baseParams: {
                cmd: 'getUserList'
            },
            sortInfo   : {
                field     : 'login',
                direction : 'ASC'
            },
            remoteSort: true,
            root: 'users',
            fields: [
                {name: 'id'},
                {name: 'login'},
                {name: 'pass'},
                {name: 'right'}
            ]
        });
    },

    initUsersHeader: function() {
        return new Ext.grid.ColumnModel({
            defaults: {
                sortable: true
            },
            columns: [
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
                }
            ]
        });
    },

    initComponent: function() {
        Ext.apply(this, {
            store: this.initUserStore(),
            loadMask: true,
            border: false,
            columnLines: false,
            colModel: this.initUsersHeader(),
            viewConfig: {
                forceFit: true,
                stripeRows: true
            },
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
            }],
            listeners: {
                afterrender: {scope: this, fn: function(grid){
                    grid.getStore().load();
                }},
                afteredit: {scope: this, fn: this.saveUserData},
                rowclick: {scope: this, fn: function(){
                    this.removebutton.enable();
                }}
            }
        });
        Library.admin.UserGrid.superclass.initComponent.apply(this, arguments);
    }

});
