Ext.ns('Library');

Library.ContextMenu = Ext.extend(Ext.menu.Menu, {

    initMenuFilters: function() {
        return ['-', {
            text: Library.wording.filter_title,
            iconCls: 'book-filter',
            menu: {
                items: [{
                    text: String.format(Library.wording.filter_editor, this.record.get('editor_id')),
                    scope: this,
                    disabled: !this.record.get('editor_id'),
                    handler: function() {
                        this.fireEvent('bookfilter', this, this.record, 'editor_id', this.record.get('editorid'));
                    }
                },{
                    text: String.format(Library.wording.filter_type, this.record.get('type_id')),
                    scope: this,
                    disabled: !this.record.get('type_id'),
                    handler: function() {
                        this.fireEvent('bookfilter', this, this.record, 'type_id', this.record.get('typeid'));
                    }
                },
                    this.initMenuNiveaux({
                        disabled: !this.record.get('niveau_id')
                    })
                ]
            }
        }, {
            text: Library.wording.filter_undo,
            iconCls: 'book-filter-undo',
            disabled: this.disableUndoFilters,
            scope: this,
            handler: function() {
                this.fireEvent('bookfilterundo', this);
            }
        }];
    },

    initMenuNiveaux: function(config) {
        var niveaux = this.record.get('niveau_id').split(',');
        if (niveaux.length <= 1) {
            // menu de filtre s'il n'y a qu'un seul niveau
            return Ext.apply({
                text: String.format(Library.wording.filter_niveau, this.record.get('niveau_id')),
                scope: this,
                handler: function() {
                    this.fireEvent('bookfilter', this, this.record, 'niveau_id', this.record.get('niveauid'));
                }
            }, config || {});
        } else {
            // menu imbrique de filtres s'il y a plusieurs niveaux
            var ns = [];
            var nsid = this.record.get('niveauid').split(',');
            for (var i = 0; i < niveaux.length; i++) {
                ns.push({
                    xtype: 'menucheckitem',
                    text: niveaux[i],
                    niveauid: nsid[i],
                    scope: this,
                    handler: function(btn) {
                        this.fireEvent('bookfilter', this, this.record, 'niveau_id', btn.niveauid);
                    }
                });
            }
            return Ext.apply({
                text: String.format(Library.wording.filter_niveau, ''),
                menu: {
                    items: ns
                }
            }, config || {});
        }
    },

    initBookMenuItems: function(withFilters) {
        var items = [{
            xtype: 'panel',
            html: this.record.get('title'),
            cls: 'book-menu-title',
            border: false
        }, '-', {
            text: Library.wording.info_book_button,
            iconCls: 'book-info-small',
            scope: this,
            handler: function() {
                this.fireEvent('bookinfo', this, this.record);
            }
        },{
            text: Library.wording.download_book_button,
            iconCls: 'book-download-small',
            disabled: !this.record.get('filename'),
            scope: this,
            handler: function() {
                this.fireEvent('bookdownload', this, this.record);
            }
        }];
        if (withFilters) {
            items = items.concat(this.initMenuFilters())
        }
        return items;
    },

    initComponent: function() {
        this.addEvents('bookinfo', 'bookdownload', 'bookfilter', 'bookfilterundo');
        Ext.apply(this, {
            items: this.initBookMenuItems(true)
        });
        Library.ContextMenu.superclass.initComponent.apply(this, arguments);
    }

});
