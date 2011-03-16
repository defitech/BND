Ext.ns('Library.admin');

Library.admin.Keys = Ext.extend(Library.Keys, {

    get: function(grid, e) {
        Library.admin.Keys.superclass.get.apply(this, arguments);
        var k = e.getKey();
        var records = grid.getSelectionModel().getSelections();
        var record = records[0];
        if (e.shiftKey) {
            if (!e.altKey) {
                if (k == e.D) {
                    grid.fireEvent('bookdelete', grid, record);
                    e.stopEvent()
                }
                else if (k == e.N) {
                    grid.getBookInfo(null);
                    e.stopEvent()
                }
            }
        }
    }

});
