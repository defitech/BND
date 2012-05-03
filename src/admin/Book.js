Ext.ns('Library.admin');

Library.admin.Book = Ext.extend(Library.Book, {

    /**
     * Position, dans la liste des items du formulaire, du checkboxgroup des niveaux
     * @var integer
     */
    niveauPos: 5,

    /**
     * Sauve les informations du livre
     *
     * @return void
     */
    doSave: function() {
        this._mask.show();
        this.getForm().getForm().submit({
            url: Library.Main.config().controller,
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

    doApply: function() {
        this._mask.show();
        this.getForm().getForm().submit({
            url: Library.Main.config().controller,
            scope: this,
            success: function(form, action) {
                this._mask.hide();
                if (action.result.success) {
                    var frm = this.getForm().getForm();
                    Ext.apply(this.data, action.result.infos);
                    frm.findField('id').setValue(this.data.id);
                    frm.findField('pdf').setValue(this.data.filename);
                    frm.findField('tags').setValue(action.result.infos.tags);
                    if (this.data.thumb) {
                        this.setThumbInfo(action.result.infos.thumb);
                    }
                    this.fireEvent('bookapply', this, Ext.apply(this.data, {
                        editorid: this.data.editor_id,
                        editor_id: frm.findField('editor_id').getRawValue(),
                        typeid: this.data.type_id,
                        type_id: frm.findField('type_id').getRawValue(),
                        niveauid: Ext.getCmp(this.niveaux_id).getValue()
                    }));
                } else {
                    this.fireEvent('booksave', this, form, action);
                }
            },
            failure: function(form, action) {
                this._mask.hide();
                Library.Main.failureForm(action.result);
            }
        });
    },

    setThumbInfo: function(thumb) {
        this.data.thumb = thumb;
        this.getForm().getForm().findField('thumb').setValue(thumb);
        var img = Ext.get(this.thumbId);
        img.set({src: String.format(Library.Main.config().image, thumb)});
        this.fireEvent('bookthumbchange', this, thumb);
    },

    createThumbFromPdf: function() {
        var pdf = this.getForm().getForm().findField('pdf').getValue();
        if (pdf) {
            Ext.Msg.confirm(Library.wording.book_thumb_create_title, Library.wording.book_thumb_create, function(choice){
                if (choice == 'yes') {
                    this._mask.show();
                    Ext.Ajax.request({
                        url: Library.Main.config().controller,
                        params: {
                            cmd: 'generatePdfThumb',
                            pdf: pdf,
                            book_id: this.data.id
                        },
                        scope: this,
                        success: function(response) {
                            var json = Library.Main.getJson(response);
                            this._mask.hide();
                            if (json.success) {
                                this.setThumbInfo(json.thumb);
                            }
                        },
                        failure: function(response) {
                            this._mask.hide();
                            Library.Main.failure(response);
                        }
                    });
                }
            }, this);
        }
    },






    /**
     * --------------------------------------------------------------
     *              Fonctions de suppressions
     * --------------------------------------------------------------
     */

    removeNiveau: function() {
        var values = this.checkniveau.getValue();
        if (values && values.length > 0) {
            var params = {};
            var vtext = [];
            for (var i = 0; i < values.length; i++) {
                vtext.push(values[i].boxLabel);
                params[values[i].getName()] = 1;
            }
            Ext.Msg.confirm(Library.wording.niveau_remove_title, String.format(Library.wording.niveau_remove, vtext.join(', ')), function(choice){
                if (choice == 'yes') {
                    this.removeNiveauSubmit(params);
                }
            }, this);
        }
    },

    removeNiveauSubmit: function(params, forceConfirm) {
        this._mask.show();
        Ext.Ajax.request({
            url: Library.Main.config().controller,
            params: Ext.apply(params, {
                cmd: 'removeNiveau',
                book_id: this.data.id,
                forceConfirm: forceConfirm
            }),
            scope: this,
            success: function(response) {
                var json = Library.Main.getJson(response);
                this._mask.hide();
                if (json.success) {
                    if (json.confirm) {
                        var c = [];
                        var str = '{0} ({1}x)';
                        for (var i = 0; i < json.nb.length; i++) {
                            c.push(String.format(str, json.nb[i].txt, json.nb[i].nbd));
                        }
                        c = c.join(', ');
                        Ext.Msg.confirm(Library.wording.remove_confirm_title, String.format(Library.wording.niveau_remove_confirm, c), function(choice){
                            if (choice == 'yes') {
                                this.removeNiveauSubmit(params, true);
                            }
                        }, this);
                    } else {
                        this.getForm().remove(Ext.getCmp(this.niveaux_id));
                        this.data.niveaux = json.niveaux;
                        this.getForm().insert(this.niveauPos, this.initFieldNiveaux());
                        this.getForm().doLayout();
                    }
                }
            },
            failure: function(response) {
                this._mask.hide();
                Library.Main.failure(response);
            }
        });
    },

    removeType: function() {
        if (this.comboType.getValue()) {
            Ext.Msg.confirm(Library.wording.type_remove_title, String.format(Library.wording.type_remove, this.comboType.getRawValue()), function(choice){
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








    /**
     * --------------------------------------------------------------
     *              Fonction d'edition
     * --------------------------------------------------------------
     */

    editNiveau: function() {
        var values = this.checkniveau.getValue();
        if (values && values.length > 0) {
            var items = [{
                xtype: 'displayfield',
                anchor: '95%',
                hideLabel: true,
                value: Library.wording.niveau_edit
            }];
            for (var i = 0; i < values.length; i++) {
                items.push({
                    xtype: 'textfield',
                    anchor: '95%',
                    hideLabel: true,
                    name: values[i].getName(),
                    value: values[i].boxLabel
                })
            }
            var win = new Ext.Window({
                title: Library.wording.niveau_edit_title,
                modal: true,
                layout: 'fit',
                width: 200,
                height: 250,
                items: {
                    xtype: 'form',
                    bodyStyle: 'padding:10px;',
                    items: items,
                    border: false,
                    waitMsgTarget: true,
                    buttonAlign: 'center',
                    autoScroll: true,
                    buttons: [{
                        text: 'OK',
                        scope: this,
                        handler: function() {
                            win.getComponent(0).getForm().submit({
                                url: Library.Main.config().controller,
                                waitMsg: Library.wording.loading,
                                params: {
                                    cmd: 'editNiveau',
                                    book_id: this.data.id
                                },
                                scope: this,
                                success: function(form, action) {
                                    var json = action.result;
                                    if (json.success) {
                                        this.getForm().remove(Ext.getCmp(this.niveaux_id));
                                        this.data.niveaux = json.niveaux;
                                        this.getForm().insert(this.niveauPos, this.initFieldNiveaux());
                                        this.getForm().doLayout();
                                    }
                                    win.close();
                                },
                                failure: function(form, action) {
                                    Library.Main.failureForm(action.result);
                                }
                            });
                        }
                    }, {
                        text: Library.wording.info_book_cancel,
                        handler: function() {
                            win.close();
                        }
                    }]
                }
            });
            win.show();
        }
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








    /**
     * --------------------------------------------------------------
     *              Fonctions d'ajout
     * --------------------------------------------------------------
     */


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
                            this.getForm().insert(this.niveauPos, this.initFieldNiveaux());
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







    /**
     * --------------------------------------------------------------
     *              Fonctions d'initialisation
     * --------------------------------------------------------------
     */

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
            {
                text: Library.wording.info_book_apply,
                iconCls: 'book-apply',
                scale: 'medium',
                scope: this,
                handler: this.doApply
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
                .concat(this.initFieldsActions(this.addNiveau, this.editNiveau, this.removeNiveau))
        };
    },

    initRights: function() {
        return {
            xtype: 'fieldset',
            title: Library.wording.right,
            collapsed: true,
            autoHeight: true,
            collapsible: true,
            anchor: '95%',
            defaults: {
                anchor: '100%'
            },
            items: [{
                xtype: 'displayfield',
                value: Library.wording.right_description
            },{
                xtype: 'checkboxgroup',
                ref: '../../checkright',
                fieldLabel: Library.wording.right_users,
                columns: 2,
                items: this.data.rights
            }]
        };
    },

    initBookItems: function() {
        var me = this;
        var items = [
            this.initFieldId({xtype: 'textfield', cls: 'book-item-id', readOnly: true}),
        ];

        items = items.concat([
            this.initFieldTitle({xtype: 'textfield'}),
            {xtype: 'hidden', name: 'cmd', value: 'saveBook'},
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
            this.initFieldIsbn({xtype: 'textfield'}),
            {
                xtype: 'textfield',
                name: 'tags',
                fieldLabel: Library.wording.tags,
                value: this.data.tags
            }
        ]);

        // bouton d'upload PlUpload
        var button = {
            xtype: 'flashpdfbutton',
            data: Ext.apply({}, this.data),
            fieldLabel: String.format(Library.wording.pdfLabel, this.data.maxpostsize),
            flex: 1,
            height: 25,
            listeners: {
                uploadsuccess: {scope: this, fn: function(button, json){
                    this.getForm().getForm().findField('pdf').setValue(json.name);
                }}
            }
        };

        // bouton normal (HTML) d'upload (input type="file")
//        var button = {
//            xtype: 'textfield',
//            inputType: 'file',
//            name: 'pdffile',
//            flex: 1
//        };

        // bouton d'upload Flash SwfUpload
//        var button = {
//            xtype: 'flashpdfbutton',
//            flex: 1,
//            height: 25,
//            listeners: {
//                uploadsuccess: {scope: this, fn: function(button, json){
//                    this.getForm().getForm().findField('pdf').setValue(json.name);
//                }}
//            }
//        };

        items = items.concat([{
            xtype: 'fieldset',
            title: Library.wording.file_fieldset,
            autoHeight: true,
            collapsible: true,
            collapsed: true,
            anchor: '95%',
            defaults: {
                anchor: '100%'
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
            anchor: '95%',
            defaults: {
                anchor: '100%'
            },
            items: [
                button,
            {
                xtype: 'compositefield',
                fieldLabel: Library.wording.currentPdf,
                items: [{
                    xtype: 'textfield',
                    name: 'pdf',
                    flex: 1,
                    fieldLabel: Library.wording.currentPdf,
                    value: this.data.filename
                },{
                    xtype: 'button',
                    iconCls: 'book-thumb-small',
                    scope: this,
                    handler: this.createThumbFromPdf
                }]
            }]
        }]);

        if (Library.Main.right(1)) {
            items.push(this.initRights());
        }

        return items;
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
        this.addEvents('booksave', 'bookapply', 'bookthumbchange');
        Ext.apply(this, {
            width: 900,
            // on annule Esc, histoire d'eviter de quitter la fenetre inopinement
            onEsc: Ext.emptyFn
        });
        Library.admin.Book.superclass.initComponent.apply(this, arguments);
    }

});