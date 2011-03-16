Ext.ns('Library.admin');

Library.admin.UserDownload = Ext.extend(Ext.grid.GridPanel, {

    exportCsv: function() {
        window.location.href = Library.Main.config().controller + '?cmd=exportUserDownloadCav&user_id=' + this.record.get('id')
    },

    setUserRecord: function(record) {
        this.record = record;
        this.getStore().setBaseParam('user_id', record.get('id'));
        this.getStore().load();
        this.exportcsv.enable();
        this.setTitle(String.format(Library.wording.user_dl_title, record.get('login')));
    },

    initDlStore: function() {
        return new Ext.data.JsonStore({
            url: Library.Main.config().controller,
            baseParams: {
                cmd: 'getUserDownloadList'
            },
            sortInfo   : {
                field     : 'title',
                direction : 'ASC'
            },
            remoteSort: false,
            root: 'items',
            fields: [
                {name: 'id'},
                {name: 'title'},
                {name: 'nb'}
            ]
        });
    },

    initDlHeader: function() {
        return new Ext.grid.ColumnModel({
            defaults: {
                sortable: true
            },
            columns: [
                {
                    header : Library.wording.user_dl_book,
                    dataIndex : 'title'
                },
                {
                    header : Library.wording.user_dl_nb,
                    dataIndex : 'nb'
                }
            ]
        });
    },

    initComponent: function() {
        this.addEvents('bookget');
        Ext.apply(this, {
            store: this.initDlStore(),
            loadMask: true,
            columnLines: false,
            colModel: this.initDlHeader(),
            viewConfig: {
                forceFit: true,
                stripeRows: true
            },
            bbar: ['->', {
                text: Library.wording.user_dl_exportcsv,
                iconCls: 'book-export-csv',
                ref: '../exportcsv',
                disabled: true,
                scope: this,
                handler: this.exportCsv
            }],
            listeners: {
                rowdblclick: {scope: this, fn: function(grid, rowIndex){
                    var record = grid.getStore().getAt(rowIndex);
                    this.fireEvent('bookget', this, record);
                }}
            }
        });
        Library.admin.UserDownload.superclass.initComponent.apply(this, arguments);
    }

});
