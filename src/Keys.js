Ext.ns('Library');

Library.Keys = Ext.extend(Ext.util.Observable, {

    get: function(grid, e) {
        var k = e.getKey();
        var records = grid.getSelectionModel().getSelections();
        var record = records[0];
        var filter;
        if (e.shiftKey) {
            if (e.altKey) {
                var col;
                if (k == e.E || k == 203) { // editeur
                    filter = grid.filters.getFilter('editor_id');
                    grid.loadMask.show();
                    filter.setActive(true);
                    filter.setValue(record.get('editorid'));
                    e.stopEvent()
                } else if (k == e.M || k == 730) { // matiere
                    filter = grid.filters.getFilter('type_id');
                    grid.loadMask.show();
                    filter.setActive(true);
                    filter.setValue(record.get('typeid'));
                    e.stopEvent()
                } else if (k == e.N || k == 729) { // niveaux
                    filter = grid.filters.getFilter('niveau_id');
                    grid.loadMask.show();
                    filter.setActive(true);
                    filter.setValue(record.get('niveauid'));
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
                    grid.fireEvent('focusfullsearch', grid, e);
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
