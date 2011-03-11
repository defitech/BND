Ext.ns('Library.admin');

Library.admin.Book = Ext.extend(Library.Book, {

    doSave: function() {
        this._mask.show();
        this.getForm().getForm().submit({
            url: Library.Main.config().controller,
            params: {
                cmd: 'saveBook'
            },
            scope: this,
            success: function(form, action) {
                this._mask.hide();
                this.fireEvent('booksave', this, form, action);
            },
            failure: function(form, action) {
                this._mask.hide();
                Library.Main.failureForm(action.result);
            }
        });
    },

    removeType: function() {
        var v = this.comboType.getValue();
        if (v) {
            Ext.Msg.confirm('Remove', 'Matiere?', function(choice){
                if (choice == 'yes') {
                    this.removeSubmit(this.comboType, {
                        cmd: 'removeType',
                        id: v
                    });
                }
            }, this);
        }
    },

    removeEditor: function() {
        var v = this.comboEditor.getValue();
        if (v) {
            Ext.Msg.confirm('Remove', 'Editeur?', function(choice){
                if (choice == 'yes') {
                    this.removeSubmit(this.comboEditor, {
                        cmd: 'removeEditor',
                        id: v
                    });
                }
            }, this);
        }
    },

    removeSubmit: function(combo, params) {
        this._mask.show();
        Ext.Ajax.request({
            url: Library.Main.config().controller,
            params: params,
            scope: this,
            success: function(response) {
                var json = Library.Main.getJson(response);
                this._mask.hide();
                if (json.success) {
                    var store = combo.getStore();
                    store.removeAt(store.find('id', params.id));
                }
            },
            failure: function(response) {
                this._mask.hide();
                Library.Main.failure(response);
            }
        });
    },

    addNiveau: function() {
        Ext.Msg.prompt(Library.wording.niveau_add_title, Library.wording.niveau_add, function(choice, txt){
            if (choice == 'ok' && txt) {
                this._mask.show();
                Ext.Ajax.request({
                    url: Library.Main.config().controller,
                    params: {
                        cmd: 'addNiveau',
                        text: txt
                    },
                    scope: this,
                    success: function(response) {
                        var json = Library.Main.getJson(response);
                        if (json.success) {
                            this.getForm().remove(Ext.getCmp(this.niveaux_id));
                            this.data.niveaux.push({
                                name: 'niveau-' + json.id,
                                boxLabel: txt
                            });
                            this.getForm().insert(4, this.initFieldNiveaux());
                            this.getForm().doLayout();
                        }
                        this._mask.hide();
                    },
                    failure: function(response) {
                        this._mask.hide();
                        Library.Main.failure(response);
                    }
                });
            }
        }, this);
    },

    addEditor: function() {
        Ext.Msg.prompt(Library.wording.editor_add_title, Library.wording.editor_add, function(choice, txt){
            if (choice == 'ok' && txt) {
                this.addSubmit(this.comboEditor, {
                    cmd: 'addEditor',
                    text: txt
                });
            }
        }, this);
    },

    addType: function() {
        Ext.Msg.prompt(Library.wording.type_add_title, Library.wording.type_add, function(choice, txt){
            if (choice == 'ok' && txt) {
                this.addSubmit(this.comboType, {
                    cmd: 'addType',
                    text: txt
                });
            }
        }, this);
    },

    addSubmit: function(combo, params) {
        this._mask.show();
        Ext.Ajax.request({
            url: Library.Main.config().controller,
            params: params,
            scope: this,
            success: function(response) {
                var json = Library.Main.getJson(response);
                this._mask.hide();
                if (json.success) {
                    var store = combo.getStore();
                    var r = new store.recordType({
                        id: json.id,
                        text: params.text
                    }, json.id);
                    store.insert(0, r);
                    store.sort('text', 'ASC');
                    combo.setValue(json.id);
                }
            },
            failure: function(response) {
                this._mask.hide();
                Library.Main.failure(response);
            }
        });
    },

    initBbar: function() {
        return [
            '->',
            {
                text: Library.wording.info_book_save,
                iconCls: 'book-save',
                scale: 'medium',
                scope: this,
                handler: this.doSave
            },
            this.initCloseButton()
        ];
    },

    initFieldNiveaux: function() {
        this.niveaux_id = Ext.id();
        return {
            xtype: 'compositefield',
            fieldLabel: Library.wording.niveau,
            id: this.niveaux_id,
            items: [
                Library.admin.Book.superclass.initFieldNiveaux.apply(this, [{flex: 1, columns: 6}]),
                {
                    xtype: 'button',
                    iconCls: 'book-relation-add',
                    scope: this,
                    handler: this.addNiveau
                },{
                    xtype: 'button',
                    iconCls: 'book-relation-remove',
                    scope: this,
                    handler: this.removeNiveau
                }
            ]
        };
    },

    initBookItems: function() {
        var items = [
            this.initFieldId({xtype: 'textfield', cls: 'book-item-id', readOnly: true}),
            this.initFieldTitle({xtype: 'textfield'}),
            {
                xtype: 'compositefield',
                fieldLabel: Library.wording.editor,
                items: [
                    this.initFieldEditor({hideTrigger: false, readOnly: false, flex: 1}),
                    {
                        xtype: 'button',
                        iconCls: 'book-relation-add',
                        scope: this,
                        handler: this.addEditor
                    },{
                        xtype: 'button',
                        iconCls: 'book-relation-remove',
                        scope: this,
                        handler: this.removeEditor
                    }
                ]
            }, {
                xtype: 'compositefield',
                fieldLabel: Library.wording.type,
                items: [
                    this.initFieldType({hideTrigger: false, readOnly: false, flex: 1}),
                    {
                        xtype: 'button',
                        iconCls: 'book-relation-add',
                        scope: this,
                        handler: this.addType
                    },{
                        xtype: 'button',
                        iconCls: 'book-relation-remove',
                        scope: this,
                        handler: this.removeType
                    }
                ]
            },
            this.initFieldNiveaux(),
            this.initFieldIsbn({xtype: 'textfield'})
        ];

        return items.concat([{
            xtype: 'textfield',
            name: 'tags',
            fieldLabel: Library.wording.tags,
            value: this.data.tags
        },{
            xtype: 'fieldset',
            title: Library.wording.file_fieldset,
            autoHeight: true,
            collapsible: true,
            anchor: '100%',
            defaults: {
                anchor: '95%'
            },
            items: [{
                xtype: 'textfield',
                inputType: 'file',
                name: 'thumbfile',
                fieldLabel: Library.wording.thumb
            },{
                xtype: 'textfield',
                name: 'thumb',
                fieldLabel: Library.wording.currentThumb,
                value: this.data.thumb
            },{
                xtype: 'textfield',
                inputType: 'file',
                name: 'pdffile',
                fieldLabel: 'PDF'
            },{
                xtype: 'textfield',
                name: 'pdf',
                fieldLabel: Library.wording.currentPdf,
                value: this.data.filename
            }]
        }]);
    },

    initComponent: function() {
        this.addEvents('booksave');
        Ext.apply(this, {
            width: 900
        });
        Library.admin.Book.superclass.initComponent.apply(this, arguments);
    }

});