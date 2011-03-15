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
        if (this.comboType.getValue()) {
            Ext.Msg.confirm(Library.wording.typeremove_title, String.format(Library.wording.type_remove, this.comboType.getRawValue()), function(choice){
                if (choice == 'yes') {
                    this.removeSubmit(this.comboType, {
                        cmd: 'removeType'
                    });
                }
            }, this);
        }
    },

    removeEditor: function() {
        if (this.comboEditor.getValue()) {
            Ext.Msg.confirm(Library.wording.editor_remove_title, String.format(Library.wording.editor_remove, this.comboEditor.getRawValue()), function(choice){
                if (choice == 'yes') {
                    this.removeSubmit(this.comboEditor, {
                        cmd: 'removeEditor'
                    });
                }
            }, this);
        }
    },

    removeSubmit: function(combo, params, forceConfirm) {
        this._mask.show();
        Ext.Ajax.request({
            url: Library.Main.config().controller,
            params: Ext.apply(params, {
                id: combo.getValue(),
                forceConfirm: forceConfirm
            }),
            scope: this,
            success: function(response) {
                var json = Library.Main.getJson(response);
                this._mask.hide();
                if (json.success) {
                    if (json.confirm) {
                        Ext.Msg.confirm(Library.wording.remove_confirm_title, String.format(Library.wording.remove_confirm, json.nb), function(choice){
                            if (choice == 'yes') {
                                this.removeSubmit(combo, params, true);
                            }
                        }, this);
                    } else {
                        var store = combo.getStore();
                        store.removeAt(store.find('id', params.id));
                        combo.setValue(null);
                    }
                }
            },
            failure: function(response) {
                this._mask.hide();
                Library.Main.failure(response);
            }
        });
    },

    editType: function() {
        if (this.comboType.getValue()) {
            Ext.Msg.prompt(Library.wording.type_edit_title, Library.wording.type_edit, function(choice, txt){
                if (choice == 'ok' && txt) {
                    this.editSubmit(this.comboType, txt, {
                        cmd: 'editType'
                    });
                }
            }, this, false, this.comboType.getRawValue());
        }
    },

    editEditor: function() {
        if (this.comboEditor.getValue()) {
            Ext.Msg.prompt(Library.wording.editor_edit_title, Library.wording.editor_edit, function(choice, txt){
                if (choice == 'ok' && txt) {
                    this.editSubmit(this.comboEditor, txt, {
                        cmd: 'editEditor'
                    });
                }
            }, this, false, this.comboEditor.getRawValue());
        }
    },

    editSubmit: function(combo, txt, params) {
        this._mask.show();
        Ext.Ajax.request({
            url: Library.Main.config().controller,
            params: Ext.apply(params, {
                id: combo.getValue(),
                text: txt
            }),
            scope: this,
            success: function(response) {
                var json = Library.Main.getJson(response);
                this._mask.hide();
                if (json.success) {
                    var store = combo.getStore();
                    var record = store.getAt(store.find('id', combo.getValue()));
                    record.set('text', params.text);
                    record.commit(true);
                    combo.setValue(combo.getValue());
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
                this.addSubmit(this.comboEditor, txt, {
                    cmd: 'addEditor'
                });
            }
        }, this);
    },

    addType: function() {
        Ext.Msg.prompt(Library.wording.type_add_title, Library.wording.type_add, function(choice, txt){
            if (choice == 'ok' && txt) {
                this.addSubmit(this.comboType, txt, {
                    cmd: 'addType'
                });
            }
        }, this);
    },

    addSubmit: function(combo, txt, params) {
        this._mask.show();
        Ext.Ajax.request({
            url: Library.Main.config().controller,
            params: Ext.apply(params, {
                text: txt
            }),
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
            this.initDownloadButton(),
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
            items: 
                [Library.admin.Book.superclass.initFieldNiveaux.apply(this, [{flex: 1, columns: 6}])]
                .concat(this.initFieldsActions(this.addNiveau, null, null))
        };
    },

    initBookItems: function() {
        var items = [
            this.initFieldId({xtype: 'textfield', cls: 'book-item-id', readOnly: true}),
            this.initFieldTitle({xtype: 'textfield'}),
            {
                xtype: 'compositefield',
                fieldLabel: Library.wording.editor,
                items: 
                    [this.initFieldEditor({hideTrigger: false, readOnly: false, flex: 1})]
                    .concat(this.initFieldsActions(this.addEditor, this.editEditor, this.removeEditor))
            }, {
                xtype: 'compositefield',
                fieldLabel: Library.wording.type,
                items: 
                    [this.initFieldType({hideTrigger: false, readOnly: false, flex: 1})]
                    .concat(this.initFieldsActions(this.addType, this.editType, this.removeType))
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
            collapsed: true,
            anchor: '100%',
            defaults: {
                anchor: '95%'
            },
            items: [{
                xtype: 'compositefield',
                fieldLabel: Library.wording.thumb,
                items: [{
                    xtype: 'textfield',
                    inputType: 'file',
                    name: 'thumbfile',
                    flex: 1
                }, {
                    xtype: 'button',
                    iconCls: 'book-relation-remove',
                    scope: this,
                    handler: function(){
                        this.getForm().getForm().findField('thumbfile').setValue(null);
                    }
                }]
            },{
                xtype: 'textfield',
                name: 'thumb',
                fieldLabel: Library.wording.currentThumb,
                value: this.data.thumb
            }]
        },{
            xtype: 'fieldset',
            title: Library.wording.filepdf_fieldset,
            autoHeight: true,
            collapsible: true,
            anchor: '100%',
            defaults: {
                anchor: '95%'
            },
            items: [{
                xtype: 'compositefield',
                fieldLabel: 'PDF',
                items: [{
                    xtype: 'textfield',
                    inputType: 'file',
                    name: 'pdffile',
                    flex: 1
                }, {
                    xtype: 'button',
                    iconCls: 'book-relation-remove',
                    scope: this,
                    handler: function(){
                        this.getForm().getForm().findField('pdffile').setValue(null);
                    }
                }]
            },{
                xtype: 'textfield',
                name: 'pdf',
                fieldLabel: Library.wording.currentPdf,
                value: this.data.filename
            }]
        }]);
    },

    initFieldsActions: function(add, edit, remove) {
        return [{
            xtype: 'button',
            iconCls: 'book-relation-add',
            scope: this,
            handler: add
        },{
            xtype: 'button',
            iconCls: 'book-relation-edit',
            scope: this,
            handler: edit,
            disabled: !edit
        },{
            xtype: 'button',
            iconCls: 'book-relation-remove',
            scope: this,
            handler: remove,
            disabled: !remove
        }];
    },

    initComponent: function() {
        this.addEvents('booksave');
        Ext.apply(this, {
            width: 900,
            // on annule Esc, histoire d'eviter de quitter la fenetre inopinement
            onEsc: Ext.emptyFn
        });
        Library.admin.Book.superclass.initComponent.apply(this, arguments);
    }

});