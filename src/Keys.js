Ext.ns('Library');

Library.Keys = Ext.extend(Ext.util.Observable, {

    get: function(grid) {
        return [
            {
                key: [Ext.EventObject.E, 203],
                shift: true,
                alt: true,
                fn: function(key, e) {
                    grid.doContextMenuFilter('editor_id', 'editorid');
                    e.stopEvent();
                }
            },
            {
                key: [Ext.EventObject.M, 730],
                shift: true,
                alt: true,
                fn: function(key, e) {
                    grid.doContextMenuFilter('type_id', 'typeid');
                    e.stopEvent();
                }
            },
            {
                key: [Ext.EventObject.N, 729],
                shift: true,
                alt: true,
                fn: function(key, e) {
                    grid.doContextMenuFilter('niveau_id', 'niveauid');
                    e.stopEvent();
                }
            },
            {
                key: [Ext.EventObject.U, 217],
                shift: true,
                alt: true,
                fn: function(key, e) {
                    grid.fireEvent('filterundo', grid);
                    e.stopEvent();
                }
            },
            {
                key: [Ext.EventObject.S],
                shift: true,
                fn: function(key, e) {
                    grid.launchDownload(grid.getRecordFromContextMenu());
                    e.stopEvent();
                }
            },
            {
                key: [Ext.EventObject.ENTER],
                shift: true,
                fn: function(key, e) {
                    var contextmenu = grid.initBookContextMenu(grid.getRecordFromContextMenu());
                    contextmenu.showAt(e.getXY());
                    e.stopEvent();
                }
            },
            {
                key: [Ext.EventObject.F],
                shift: true,
                fn: function(key, e) {
                    grid.fireEvent('focusfullsearch', grid, e);
                    e.stopEvent();
                }
            },
            {
                key: [Ext.EventObject.PAGE_DOWN, Ext.EventObject.RIGHT, Ext.EventObject.END],
                shift: true,
                fn: function(key, e) {
                    grid.getBottomToolbar().moveLast();
                    e.stopEvent();
                }
            },
            {
                key: [Ext.EventObject.PAGE_UP, Ext.EventObject.LEFT, Ext.EventObject.HOME],
                shift: true,
                fn: function(key, e) {
                    grid.getBottomToolbar().moveFirst();
                    e.stopEvent();
                }
            },
            {
                key: [Ext.EventObject.END],
                shift: false,
                fn: function(key, e) {
                    grid.getSelectionModel().selectLastRow();
                    e.stopEvent();
                }
            },
            {
                key: [Ext.EventObject.HOME],
                shift: false,
                fn: function(key, e) {
                    grid.getSelectionModel().selectFirstRow();
                    e.stopEvent();
                }
            },
            {
                key: [Ext.EventObject.RIGHT],
                shift: false,
                fn: function(key, e) {
                    grid.getBottomToolbar().moveNext();
                    e.stopEvent();
                }
            },
            {
                key: [Ext.EventObject.LEFT],
                shift: false,
                fn: function(key, e) {
                    grid.getBottomToolbar().movePrevious();
                    e.stopEvent();
                }
            },
            {
                key: [Ext.EventObject.ENTER],
                shift: false,
                fn: function(key, e) {
                    grid.getBookInfo(grid.getRecordFromContextMenu());
                    e.stopEvent();
                }
            }
        ];
    }

});
