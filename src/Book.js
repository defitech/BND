Ext.ns('Defitech');

Defitech.Book = Ext.extend(Ext.Window, {

    tplImage: new Ext.XTemplate(
        '<img class="book-thumb-big" src="{src}" title="{title}" alt="{title}" />'
    ),

    getForm: function() {
        return this.getComponent(1);
    },

    initCloseButton: function() {
        return {
            text: Defitech.wording.info_book_close,
            iconCls: 'book-window-close',
            scale: 'medium',
            scope: this,
            handler: function() {
                this.close();
            }
        };
    },

    initBbar: function() {
        return ['->', this.initCloseButton()];
    },

    initFieldId: function(config) {
        return Ext.apply({
            xtype: 'hidden',
            name: 'id',
            fieldLabel: 'ID',
            value: this.data.id
        }, config || {})
    },

    initFieldTitle: function(config) {
        return Ext.apply({
            xtype: 'displayfield',
            name: 'title',
            fieldLabel: Defitech.wording.title,
            value: this.data.title
        }, config || {})
    },

    initFieldIsbn: function(config) {
        return Ext.apply({
            xtype: 'displayfield',
            name: 'isbn',
            fieldLabel: Defitech.wording.isbn,
            value: this.data.isbn
        }, config || {})
    },

    initFieldEditor: function(config) {
        return Ext.apply({
            xtype: 'combo',
            name: 'editor_id',
            fieldLabel: Defitech.wording.editor,
            value: this.data.editor_id,
            triggerAction: 'all',
            mode: 'local',
            forceSelection: true,
            valueField: 'id',
            displayField: 'text',
            ref: '../../comboEditor',
            store: new Ext.data.JsonStore({
                root: 'items',
                fields: [
                    {name: 'id'},
                    {name: 'text'}
                ],
                data: this.data.editors
            }),
            hideTrigger: true,
            readOnly: true
        }, config || {})
    },

    initFieldType: function(config) {
        return Ext.apply({
            xtype: 'combo',
            name: 'type_id',
            fieldLabel: Defitech.wording.type,
            value: this.data.type_id,
            triggerAction: 'all',
            mode: 'local',
            valueField: 'id',
            forceSelection: true,
            displayField: 'text',
            ref: '../../comboType',
            store: new Ext.data.JsonStore({
                root: 'items',
                fields: [
                    {name: 'id'},
                    {name: 'text'}
                ],
                data: this.data.types
            }),
            hideTrigger: true,
            readOnly: true
        }, config || {})
    },

    initFieldNiveaux: function(config, cniveau) {
        for (var i = 0; i < this.data.niveaux.length; i++) {
            Ext.apply(this.data.niveaux[i], cniveau || {});
        }
        return Ext.apply({
            xtype: 'checkboxgroup',
            fieldLabel: Defitech.wording.niveau,
            columns: 4,
            items: this.data.niveaux
        }, config || {});
    },

    initBookItems: function() {
        return [
            this.initFieldId(),
            this.initFieldTitle(),
            this.initFieldEditor(),
            this.initFieldType(),
            this.initFieldNiveaux(),
            this.initFieldIsbn()
        ];
    },

    initComponent: function() {
        Ext.applyIf(this, {
            width: 700,
            height: 480
        });
        Ext.apply(this, {
            title: this.record ? this.record.get('title') : Defitech.wording.add_book,
            layout: 'border',
            resizable: false,
            bbar: this.initBbar(),
            onEsc: Ext.emptyFn,
            items: [{
                xtype: 'panel',
                region: 'west',
                width: 270,
                border: false,
                bodyStyle: 'padding: 4px;',
                html: this.tplImage.apply({
                    src: this.data.thumb || 'resources/images/empty.jpg',
                    title: this.data.title
                })
            },{
                xtype: 'form',
                region: 'center',
                border: false,
                fileUpload: true,
                bodyStyle: 'padding:10px; padding-top: 15px;',
                defaults: {
                    anchor: '95%'
                },
                items: this.initBookItems()
            }],
            listeners: Ext.apply({
                afterrender: function(cmp) {
                    cmp._mask = new Ext.LoadMask(cmp.bwrap);
                }
            },this.listeners || {})
        });
        Defitech.Book.superclass.initComponent.apply(this, arguments);
    }

});
