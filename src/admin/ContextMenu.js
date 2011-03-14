Ext.ns('Library.admin');

Library.admin.ContextMenu = Ext.extend(Library.ContextMenu, {

    initBookMenuItems: function() {
        var items = Library.admin.ContextMenu.superclass.initBookMenuItems.apply(this, [false]);
        return items.concat([{
            text: Library.wording.delete_book_button,
            iconCls: 'book-delete-small',
            scope: this,
            handler: function() {
                this.fireEvent('bookdelete', this, this.record);
            }
        }])
        .concat(this.initMenuFilters())
        .concat(['-', {
            text: Library.wording.add_book,
            iconCls: 'book-add-small',
            scope: this,
            handler: function() {
                this.fireEvent('bookadd', this, this.record);
            }
        }]);
    },

    initComponent: function() {
        this.addEvents('bookdelete', 'bookadd');
        Library.admin.ContextMenu.superclass.initComponent.apply(this, arguments);
    }

});
