Ext.ns('Library.admin');

Library.admin.UserPanel = Ext.extend(Ext.Panel, {

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
            items: [
                new Library.admin.UserGrid({
                    region: 'center',
                    margins: '4 2 4 4',
                    listeners: {
                        showdownloads: {scope: this, fn: this.showDownloadsForUser}
                    }
                }),
                new Library.admin.UserDownload({
                    region: 'east',
                    margins: '4 4 4 2',
                    width: 350,
                    collapsible: true,
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
