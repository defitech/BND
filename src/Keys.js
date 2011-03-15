Ext.ns('Library');

Library.Keys = Ext.extend(Ext.util.Observable, {

    get: function(grid, e) {
        var k = e.getKey();
        var records = grid.getSelectionModel().getSelections();
        var record = records[0];
        var m = grid.getColumnModel();
        if (e.shiftKey) {
            if (e.altKey) {
                var col;
                if (k == e.E) { // editeur
                    col = m.getColumnAt(m.findColumnIndex('editor_id'));
                    col.filter.getField().setValue(record.get('editor_id'));
                    col.filter.fireChangeEvent();
                    e.stopEvent()
                } else if (k == e.M) { // matiere
                    col = m.getColumnAt(m.findColumnIndex('type_id'));
                    col.filter.getField().setValue(record.get('type_id'));
                    col.filter.fireChangeEvent();
                    e.stopEvent()
                } else if (k == e.N) { // niveaux
                    col = m.getColumnAt(m.findColumnIndex('niveau_id'));
                    col.filter.getField().setValue(record.get('niveau_ids'));
                    col.filter.fireChangeEvent();
                    e.stopEvent()
                }
            } else {
                if (k == e.S) {
                    grid.launchDownload(record);
                    e.stopEvent()
                }
                else if (k == e.ENTER) {
                    var contextmenu = initBookContextMenu(record);
                    contextmenu.showAt(e.getXY());
                    e.stopEvent()
                }
                else if (k == e.F) {
                    grid.fireEvent('focusfullsearch', this, e);
                    e.stopEvent()
                }
                else if (k == e.PAGE_DOWN || k == e.RIGHT || k == e.END) {
                    grid.getBottomToolbar().moveLast();
                    e.stopEvent()
                }
                else if (k == e.PAGE_UP || k == e.LEFT || k == e.HOME) {
                    grid.getBottomToolbar().moveFirst();
                    e.stopEvent()
                }
            }
        } else {
            if (k == e.END) {
                grid.getSelectionModel().selectLastRow();
            }
            else if (k == e.HOME) {
                grid.getSelectionModel().selectFirstRow();
            }
            else if (k == e.RIGHT) {
                grid.getBottomToolbar().moveNext();
                e.stopEvent()
            }
            else if (k == e.LEFT) {
                grid.getBottomToolbar().movePrevious();
                e.stopEvent()
            }
            else if (k == e.ENTER) {
                grid.getBookInfo(record);
                e.stopEvent()
            }
        }
    }

});
