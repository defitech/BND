Ext.ns('Library.admin');

Library.admin.Keys = Ext.extend(Library.Keys, {

    get: function(grid) {
        var map = Library.admin.Keys.superclass.get.apply(this, arguments);
        return map.concat([
            {
                key: [Ext.EventObject.D],
                fn: function(key, e) {
                    grid.fireEvent('bookdelete', grid, grid.getRecordFromContextMenu());
                    e.stopEvent();
                }
            },
            {
                key: [Ext.EventObject.N],
                shift: false,
                alt: false,
                fn: function(key, e) {
                    grid.getBookInfo(null);
                    e.stopEvent();
                }
            }
        ]);
    }

});
