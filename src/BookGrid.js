Ext.ns('Library');

Library.BookGrid = Ext.extend(Ext.grid.GridPanel, {

    getBookInfo: function(record, config) {
        this.loadMask.show();
        Ext.Ajax.request({
            url: Library.Main.config().controller,
            params: {
                cmd: 'getBook',
                id: record ? record.get('id') : 0
            },
            scope: this,
            success: function(response) {
                var json = Library.Main.getJson(response);
                if (json.success) {
                    var row = null;
                    if (record && record.store && record.store.baseParams.cmd == this.getStore().baseParams.cmd) {
                        row = this.getView().getRow(this.getStore().indexOf(record));
                    }
                    this.initBookWindow(record, json, row, config || {});
                } else {
                    this.loadMask.hide();
                }
            },
            failure: function(response) {
                this.loadMask.hide();
                Library.Main.failure(response);
            }
        });
    },
    
    getCurrentFilters: function() {
        var current = this.filters.buildQuery(this.filters.getFilterData());
        var customs = ['fullsearch'];
        for (var i = 0; i < customs.length; i++) {
            if (this.getStore().baseParams['filters[' + customs[i] + ']']) {
                current['filters[' + customs[i] + ']'] = this.getStore().baseParams['filters[' + customs[i] + ']'];
            }
        }
        return current;
    },

    launchDownload: function(record) {
        window.location.href = Library.Main.config().controller + '?cmd=download&id=' + record.get('id');
    },

    doContextMenuFilter: function(text, id) {
        var record = this.getRecordFromContextMenu();
        var filter = this.filters.getFilter(text);
        this.loadMask.show();
        filter.setActive(true);
        filter.setValue(record.get(id));
    },

    getRecordFromContextMenu: function(wholeSelection) {
        var records = this.getSelectionModel().getSelections();
        var record = records[0];
        return wholeSelection ? records : record;
    },
    
    initBookContextMenuListeners: function() {
        return {
            bookdownload: {scope: this, fn: function(menu, record){
                 this.launchDownload(record);
            }},
            bookinfo: {scope: this, fn: function(menu, record){
                 this.getBookInfo(record);
            }},
            bookfilter: {scope: this, fn: function(menu, record, filter, value){
                 var f = this.filters.getFilter(filter);
                 this.loadMask.show();
                 f.setActive(true);
                 f.setValue(value);
            }},
            bookfilterundo: {scope: this, fn: function(){
                this.fireEvent('filterundo', this);
            }}
        };
    },

    initBookContextMenu: function(record, disableUndoFilters) {
        return new Library.ContextMenu({
            record: record,
            disableUndoFilters: disableUndoFilters,
            listeners: this.initBookContextMenuListeners()
        });
    },

    initBookWindow: function(record, json, row, config) {
        var win = new Library.Book(Ext.apply({
            record: record,
            data: json.data,
            animateTarget: row || this.getEl(),
            listeners: this.initBookWindowListeners()
        }, config || {}));
        win.show();
    },

    initBookWindowListeners: function() {
        return {
            show: {scope: this, fn: function(){
                this.loadMask.hide();
            }},
            bookdownload: {scope: this, fn: function(w, record){
                this.launchDownload(record);
            }}
        };
    },

    renderBookThumb: function(val, data, record){
        var t = record.get('title').replace('"', "'");
        if (record.get('thumbName'))
            return '<img class="book-thumb" src="' + String.format(Library.Main.config().image, record.get('thumbName')) + '" alt="' + t + '" title="' + t + '" />';
        
        else
            return '<img class="book-thumb" src="' + val + '" alt="' + t + '" title="' + t + '" />';
    },

    initBookStore: function() {
        return new Ext.data.JsonStore({
            url: Library.Main.config().controller,
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
                {name: 'thumb'}, {name: 'thumbName'},
                {name: 'isbn'},
                {name: 'filename'},
                // correspond au label (texte)
                {name: 'type_id'},
                // correspond au nom de l'editeur (texte)
                {name: 'editor_id'},
                // correspond aux labels des niveaux (texte)
                {name: 'niveau_id'},
                // id de la matiere (int)
                {name: 'typeid', type: 'int'},
                // id de l'editeur (int)
                {name: 'editorid', type: 'int'},
                // liste des ids des niveaux (texte separe par virgule)
                {name: 'niveauid'}
            ]
        });
    },

    initPagingToolbar: function(store, filters) {
        return {
            xtype : 'paging',
            pageSize : Library.Main.config().nb,
            store : store,
            displayInfo : true,
            plugins: [filters]
        };
    },

    initColumnId: function(cfg) {
        return Ext.apply({
            dataIndex: 'id',
            header: 'ID',
            align: 'right',
            width: 50,
            fixed: true,
            hideable: false,
            hidden: true
        }, cfg || {});
    },

    initBookHeaders: function(idAutoExpand) {
        return new Ext.grid.ColumnModel({
            defaults: {
                sortable: true,
                fixed: true
            },
            columns: [
                {
                    header : Library.wording.thumb,
                    width : 80,
                    sortable : false,
                    menuDisabled: true,
                    scope : this,
                    renderer : this.renderBookThumb,
                    dataIndex : 'thumb'
                },
                this.initColumnId(),
                {
                    header : Library.wording.type,
                    width : 110,
                    dataIndex : 'type_id'
                },
                {
                    header : Library.wording.editor,
                    width : 160,
                    dataIndex : 'editor_id'
                },
                {
                    header : Library.wording.niveau,
                    width : 100,
                    dataIndex : 'niveau_id'
                },
                {
                    header : Library.wording.title,
                    fixed: false,
                    dataIndex : 'title'
                },
                {
                    header : Library.wording.isbn,
                    width: 140,
                    dataIndex : 'isbn'
                }
            ]
        });
    },

    initFilters: function() {
        /*return new Ext.ux.grid.FilterRow({
            autoFilter: false,
            listeners: {
                change: function(data) {
                    store.load({
                        params: data
                    });
                }
            }
        });*/
        var filters = new Ext.ux.grid.GridFilters({
            filters: [{
                type: 'numeric',
                dataIndex: 'id'
            }, {
                type: 'string',
                dataIndex: 'title'
            }, {
                type: 'string',
                dataIndex: 'isbn'
            }, {
                type: 'list',
                dataIndex: 'editor_id',
                phpMode: true,
                store: new Ext.data.JsonStore({
                    url: Library.Main.config().controller,
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
                    url: Library.Main.config().controller,
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
                    url: Library.Main.config().controller,
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

    initKeyMap: function() {
        var keys = new Library.Keys();
        return keys.get(this);
    },

    initComponent: function() {
        this.addEvents('selectionchange', 'filterundo', 'focusfullsearch');
        
        var idAutoExpand = Ext.id();
        var store = this.initBookStore();
        var filters = this.initFilters();
        Ext.apply(this, {
            cls: 'book-grid',
            store: store,
            plugins: [filters],
            loadMask: true,
            keys: this.initKeyMap(),
            columnLines: false,
            colModel: this.initBookHeaders(idAutoExpand),
            autoExpandColumn: idAutoExpand,
            viewConfig: {
                stripeRows: true,
                forceFit: true
            },
            bbar: this.initPagingToolbar(store, filters),
            listeners: {
                rowdblclick: {scope: this, fn: function(grid, rowIndex){
                    var record = grid.getStore().getAt(rowIndex);
                    this.getBookInfo(record);
                }},
                rowcontextmenu: {scope: this, fn: function(grid, rowIndex, e){
                    var record = grid.getStore().getAt(rowIndex);
                    grid.getSelectionModel().selectRow(rowIndex);
                    var disableUndoFilters = grid.filters.getFilterData().length == 0 && !grid.hasFullsearch;
                    var contextmenu = this.initBookContextMenu(record, disableUndoFilters);
                    contextmenu.showAt(e.getXY());
                    e.stopEvent();
                }},
                afterrender: {scope: this, fn: function(grid){
                    grid.getStore().load({params:{start:0, limit: Library.Main.config().nb}});
                }}
            }
        });
        Library.BookGrid.superclass.initComponent.apply(this, arguments);

        this.getSelectionModel().on({
            selectionchange: {scope: this, fn: function(model){
                this.fireEvent('selectionchange', this, model);
            }}
        });

        this.getStore().on({
            load: {scope: this, fn: function(){
                this.getSelectionModel().selectFirstRow();
            }}
        });
    }

});

Ext.reg('bookgrid', Library.BookGrid);
