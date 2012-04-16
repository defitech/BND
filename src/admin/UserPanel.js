Ext.ns('Library.admin');

Library.admin.UserPanel = Ext.extend(Ext.Panel, {
    
    getBooksDownloads: function() {
        window.location.href = Library.Main.config().controller + '?cmd=exportBooksDownloadCsv';
    },

    showDownloadsForUser: function(grid, record) {
        var dl = this.getDownloadGrid();
        dl.setUserRecord(record);
        dl.expand();
    },

    getDownloadGrid: function() {
        return this.getComponent(1);
    },

    initComponent: function() {
        this.addEvents('bookget');
        Ext.apply(this, {
            layout: 'border',
            border: false,
            tbar: ['->', {
                text: Library.wording.user_books_exportcsv,
                iconCls: 'book-export-csv',
                scope: this,
                handler: this.getBooksDownloads
            }],
            items: [
                new Library.admin.UserGrid({
                    region: 'center',
                    margins: '0 2 0 0',
                    border: false,
                    listeners: {
                        showdownloads: {scope: this, fn: this.showDownloadsForUser}
                    }
                }),
                new Library.admin.UserDownload({
                    region: 'east',
                    margins: '0 0 0 2',
                    width: 350,
                    border: false,
                    collapsible: true,
                    split: true,
                    listeners: {
                        bookget: {scope: this, fn: function(grid, record){
                            this.fireEvent('bookget', this, record);
                        }}
                    }
                })
            ]
        });
        Library.admin.UserPanel.superclass.initComponent.apply(this, arguments);
    }

});
