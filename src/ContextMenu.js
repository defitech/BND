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
                    handler: function() {
                        this.fireEvent('bookfilter', this, this.record, 'editor_id');
                    }
                },{
                    text: String.format(Library.wording.filter_type, this.record.get('type_id')),
                    scope: this,
                    handler: function() {
                        this.fireEvent('bookfilter', this, this.record, 'type_id');
                    }
                },{
                    text: String.format(Library.wording.filter_niveau, this.record.get('niveau_id')),
                    scope: this,
                    handler: function() {
                        this.fireEvent('bookfilter', this, this.record, 'niveau_id');
                    }
                }]
            }
        }];
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
        this.addEvents('bookinfo', 'bookdownload', 'bookfilter');
        Ext.apply(this, {
            items: this.initBookMenuItems(true)
        });
        Library.ContextMenu.superclass.initComponent.apply(this, arguments);
    }

});
