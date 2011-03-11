Ext.ns('Defitech');

Defitech.BookGrid = Ext.extend(Ext.grid.GridPanel, {

    getBookInfo: function(record, config) {
        this.loadMask.show();
        Ext.Ajax.request({
            url: Defitech.Main.config().controller,
            params: {
                cmd: 'getBook',
                id: record ? record.get('id') : 0
            },
            scope: this,
            success: function(response) {
                var json = Defitech.Main.getJson(response);
                if (json.success) {
                    var row = record ? this.getView().getRow(this.getStore().indexOf(record)) : null;
                    this.initBookWindow(record, json, row, config);
                } else {
                    this.loadMask.hide();
                }
            },
            failure: function(response) {
                this.loadMask.hide();
                Defitech.Main.failure(response);
            }
        });
    },

    initBookWindow: function(record, json, row, config) {
        var win = new Defitech.Book(Ext.apply({
            record: record,
            data: json.data,
            animateTarget: row || this.getEl(),
            listeners: {
                show: {scope: this, fn: function(){
                    this.loadMask.hide();
                }}
            }
        }, config || {}));
        win.show();
    },

    renderBookThumb: function(val, data, record){
        var t = record.get('title').replace('"', "'");
        return '<img class="book-thumb" src="' + val + '" alt="' + t + '" title="' + t + '" />';
    },

    initBookStore: function() {
        return new Ext.data.JsonStore({
            url: Defitech.Main.config().controller,
            baseParams: {
                cmd: 'getBookList'
            },
            sortInfo   : {
                field     : 'title',
                direction : 'ASC'
            },
            remoteSort: true,
            root: 'books',
            fields: [
                {name: 'id'},
                {name: 'title'},
                {name: 'thumb'},
                {name: 'type_id'},
                {name: 'editor_id'},
                {name: 'niveau_id'}
            ]
        });
    },

    initPagingToolbar: function(store, filters) {
        return {
            xtype : 'paging',
            pageSize : Defitech.Main.nb,
            store : store,
            displayInfo : true,
            plugins: [filters]
        };
    },


    initBookHeaders: function(idAutoExpand) {
        return new Ext.grid.ColumnModel({
            defaults: {
                sortable: true
            },
            columns: [
                {
                    header : Defitech.wording.thumb,
                    width : 80,
                    sortable : false,
                    menuDisabled: true,
                    scope : this,
                    renderer : this.renderBookThumb,
                    dataIndex : 'thumb'
                },
                {
                    header : Defitech.wording.type,
                    width : 110,
                    dataIndex : 'type_id'
                },
                {
                    header : Defitech.wording.editor,
                    width : 180,
                    dataIndex : 'editor_id'
                },
                {
                    header : Defitech.wording.niveau,
                    width : 100,
                    dataIndex : 'niveau_id'
                },
                {
                    header : Defitech.wording.title,
                    id : idAutoExpand,
                    dataIndex : 'title'
                }
            ]
        });
    },

    initFilters: function() {
        var filters = new Ext.ux.grid.GridFilters({
            filters: [{
                type: 'string',
                dataIndex: 'title'
            }, {
                type: 'list',
                dataIndex: 'editor_id',
                phpMode: true,
                store: new Ext.data.JsonStore({
                    url: Defitech.Main.config().controller,
                    baseParams: {
                        cmd: 'getEditorList'
                    },
                    root: 'items',
                    fields: [
                        {name: 'id'},
                        {name: 'text'}
                    ]
                })
            }, {
                type: 'list',
                dataIndex: 'type_id',
                phpMode: true,
                store: new Ext.data.JsonStore({
                    url: Defitech.Main.config().controller,
                    baseParams: {
                        cmd: 'getTypeList'
                    },
                    root: 'items',
                    fields: [
                        {name: 'id'},
                        {name: 'text'}
                    ]
                })
            }, {
                type: 'list',
                dataIndex: 'niveau_id',
                phpMode: true,
                store: new Ext.data.JsonStore({
                    url: Defitech.Main.config().controller,
                    baseParams: {
                        cmd: 'getNiveauList'
                    },
                    root: 'items',
                    fields: [
                        {name: 'id'},
                        {name: 'text'}
                    ]
                })
            }]
        });
        return filters;
    },

    initComponent: function() {
        this.addEvents('selectionchange');
        
        var idAutoExpand = Ext.id();
        var store = this.initBookStore();
        var filters = this.initFilters();
        Ext.apply(this, {
            store: store,
            plugins: [filters],
            loadMask: true,
            columnLines: false,
            colModel: this.initBookHeaders(idAutoExpand),
            autoExpandColumn: idAutoExpand,
            viewConfig: {
                stripeRows: true
            },
            bbar: this.initPagingToolbar(store, filters),
            listeners: {
                rowdblclick: {scope: this, fn: function(grid, rowIndex){
                    var record = grid.getStore().getAt(rowIndex);
                    this.getBookInfo(record);
                }},
                afterrender: {scope: this, fn: function(grid){
                    grid.getStore().load({params:{start:0, limit: Defitech.Main.nb}});
                }}
            }
        });
        Defitech.BookGrid.superclass.initComponent.apply(this, arguments);

        this.getSelectionModel().on({
            selectionchange: {scope: this, fn: function(model){
                this.fireEvent('selectionchange', this, model);
            }}
        });
    }

});

Ext.reg('bookgrid', Defitech.BookGrid);
