Ext.ns('Library.admin');

Library.admin.BookGrid = Ext.extend(Library.BookGrid, {

    initBookWindow: function(record, json, row, config) {
        if (!config.forceReadOnly) {
            var win = new Library.admin.Book(Ext.apply({
                record: record,
                data: json.data,
                modal: true,
                animateTarget: row || this.getEl(),
                listeners: Ext.apply(this.initBookWindowListeners(), {
                    booksave: {scope: this, fn: function(cmp){
                        this.getStore().navigateToRecord = cmp.record;
                        this.getStore().reload();
                        cmp.close();
                    }},
                    bookapply: {scope: this, fn: function(cmp, data) {
                        if (!record) return;
                        for (var i in data) {
                            if (i != 'thumb' && typeof record.data[i] != 'undefined') {
                                record.set(i, data[i]);
                            }
                        }
                        record.commit();
                    }},
                    bookthumbchange: {scope: this, fn: function(cmp, thumb){
                        if (!record) return;
                        record.set('thumbName', thumb);
                        record.set('thumb', thumb);
                        record.commit();
                    }}
                })
            }, config || {}));
            win.show();
        } else {
            Library.admin.BookGrid.superclass.initBookWindow.apply(this, arguments);
        }
    },

    initBookContextMenu: function(record, disableUndoFilters) {
        return new Library.admin.ContextMenu({
            record: record,
            disableUndoFilters: disableUndoFilters,
            listeners: Ext.apply(this.initBookContextMenuListeners(), {
                bookadd: {scope: this, fn: function(menu, record){
                     this.getBookInfo(null);
                }},
                bookdelete: {scope: this, fn: function(menu, record){
                     this.fireEvent('bookdelete', this, record);
                }}
            })
        });
    },
    
    initColumnId: function() {
        return Library.admin.BookGrid.superclass.initColumnId.apply(this, [{
            hideable: true,
            hidden: false
        }]);
    },

    initKeyMap: function() {
        var map = new Library.admin.Keys();
        return map.get(this);
    },

    initComponent: function() {
        this.addEvents('bookdelete');
        Library.admin.BookGrid.superclass.initComponent.apply(this, arguments);
        this.getStore().on({
            load: {scope: this, fn: function(store){
                var grid = this;
                if (store.navigateToRecord) {
                    var row = store.find('id', store.navigateToRecord.get('id'));
                    (function(){
                        grid.getSelectionModel().selectRow(row);
                        grid.getView().focusRow(row);
                    }).defer(200);
                    delete store.navigateToRecord;
                }
            }}
        })
    }

});

Ext.reg('bookgridadmin', Library.admin.BookGrid);
