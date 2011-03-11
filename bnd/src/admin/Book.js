Ext.ns('Defitech.admin');

Defitech.admin.Book = Ext.extend(Defitech.Book, {

    doSave: function() {
        this._mask.show();
        this.getForm().getForm().submit({
            url: Defitech.Main.config().controller,
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
                Defitech.Main.failureForm(action.result);
            }
        });
    },

    addNiveau: function() {
        Ext.Msg.prompt(Defitech.wording.niveau_add_title, Defitech.wording.niveau_add, function(choice, txt){
            if (choice == 'ok' && txt) {
                this._mask.show();
                Ext.Ajax.request({
                    url: Defitech.Main.config().controller,
                    params: {
                        cmd: 'addNiveau',
                        text: txt
                    },
                    scope: this,
                    success: function(response) {
                        var json = Defitech.Main.getJson(response);
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
                        Defitech.Main.failure(response);
                    }
                });
            }
        }, this);
    },

    addEditor: function() {
        Ext.Msg.prompt(Defitech.wording.editor_add_title, Defitech.wording.editor_add, function(choice, txt){
            if (choice == 'ok' && txt) {
                this.addSubmit(this.comboEditor, {
                    cmd: 'addEditor',
                    text: txt
                });
            }
        }, this);
    },

    addType: function() {
        Ext.Msg.prompt(Defitech.wording.type_add_title, Defitech.wording.type_add, function(choice, txt){
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
            url: Defitech.Main.config().controller,
            params: params,
            scope: this,
            success: function(response) {
                var json = Defitech.Main.getJson(response);
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
                Defitech.Main.failure(response);
            }
        });
    },

    initBbar: function() {
        return [
            '->',
            {
                text: Defitech.wording.info_book_save,
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
            fieldLabel: Defitech.wording.niveau,
            id: this.niveaux_id,
            items: [
                Defitech.admin.Book.superclass.initFieldNiveaux.apply(this, [{flex: 1, columns: 6}]),
                {
                    xtype: 'button',
                    iconCls: 'book-relation-add',
                    scope: this,
                    handler: this.addNiveau
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
                fieldLabel: Defitech.wording.editor,
                items: [
                    this.initFieldEditor({hideTrigger: false, readOnly: false, flex: 1}),
                    {
                        xtype: 'button',
                        iconCls: 'book-relation-add',
                        scope: this,
                        handler: this.addEditor
                    }
                ]
            }, {
                xtype: 'compositefield',
                fieldLabel: Defitech.wording.type,
                items: [
                    this.initFieldType({hideTrigger: false, readOnly: false, flex: 1}),
                    {
                        xtype: 'button',
                        iconCls: 'book-relation-add',
                        scope: this,
                        handler: this.addType
                    }
                ]
            },
            this.initFieldNiveaux(),
            this.initFieldIsbn({xtype: 'textfield'})
        ];

        return items.concat([{
            xtype: 'textfield',
            name: 'tags',
            fieldLabel: Defitech.wording.tags,
            value: this.data.tags
        },{
            xtype: 'fieldset',
            title: Defitech.wording.file_fieldset,
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
                fieldLabel: Defitech.wording.thumb
            },{
                xtype: 'textfield',
                name: 'thumb',
                fieldLabel: Defitech.wording.currentThumb,
                value: this.data.thumb
            },{
                xtype: 'textfield',
                inputType: 'file',
                name: 'pdffile',
                fieldLabel: 'PDF'
            },{
                xtype: 'textfield',
                name: 'pdf',
                fieldLabel: Defitech.wording.currentPdf,
                value: this.data.filename
            }]
        }]);
    },

    initComponent: function() {
        this.addEvents('booksave');
        Ext.apply(this, {
            width: 900
        });
        Defitech.admin.Book.superclass.initComponent.apply(this, arguments);
    }

});