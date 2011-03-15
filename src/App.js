Ext.ns('Library');

Library.App = Ext.extend(Ext.Viewport, {

    doBookSearch: function() {
        var search = this.searchbox.getValue();
        var grid = this.getGrid();
        grid.getStore().setBaseParam('filters[fullsearch]', search);
        grid.filters.reload();
    },

    undoBookSearch: function() {
        var grid = this.getGrid();
        this.searchbox.setValue(null);
        grid.getStore().setBaseParam('filters[fullsearch]', '');
        var current = grid.filters.getFilterData();
        grid.filters.clearFilters();
        if (current.length == 0) {
            grid.getStore().reload();
        }
    },

    downloadBook: function() {
        this.getGrid().launchDownload(this.getGrid().getSelectionModel().getSelected());
    },

    getBookInfo: function() {
        this.getGrid().getBookInfo(this.getGrid().getSelectionModel().getSelected());
    },

    getGrid: function() {
        return this.getComponent(0).getComponent(0);
    },

    initActionButtons: function() {
        return [];
    },

    logout: function() {
        Ext.Ajax.request({
            url: Library.Main.config().controller,
            params: {
                cmd: 'logout'
            },
            scope: this,
            success: function(response) {
                var json = Library.Main.getJson(response);
                if (json.success) {
                    window.location.reload();
                }
            },
            failure: function(response) {
                Library.Main.failure(response);
            }
        });
    },

    initLoginButton: function() {
        return {
            text: Library.wording.connect_logout,
            scale: 'medium',
            iconCls: 'book-deconnect',
            scope: this,
            handler: this.logout
        };
    },

    initComponent: function() {
        Ext.apply(this, {
            layout: {
                type: 'border'
            },
            items: [{
                region: 'center',
                iconCls: 'book-main',
                title: Library.wording.library_title,
                layout: 'fit',
                margins: '50',
                tools: [{
                    id: 'help',
                    qtip: Library.wording.help,
                    scope: this,
                    handler: function() {

                    }
                }],
                items: [{
                    xtype: this.bookgridxtype || 'bookgrid',
                    border: false,
                    listeners: Ext.apply(this.booklisteners || {}, {
                        selectionchange: {scope: this, fn: function(grid, model) {
                            if (model.getCount() == 1) {
                                this.bookinfo.enable();
                                this.bookdownload.enable();
                            } else {
                                this.bookinfo.disable();
                                this.bookdownload.disable();
                            }
                        }},
                        focusfullsearch: {scope: this, fn: function() {
                            this.searchbox.focus();
                        }},
                        filterundo: {scope: this, fn: this.undoBookSearch}
                    })
                }],
                tbar: this.initActionButtons().concat([
                    {
                        text: Library.wording.info_book_button,
                        iconCls: 'book-info',
                        scale: 'medium',
                        ref: '../../bookinfo',
                        disabled: true,
                        scope: this,
                        handler: this.getBookInfo
                    },
                    {
                        text: Library.wording.download_book_button,
                        iconCls: 'book-download',
                        scale: 'medium',
                        ref: '../../bookdownload',
                        disabled: true,
                        scope: this,
                        handler: this.downloadBook
                    },
                    '->',
                    {
                        xtype: 'button',
                        scale: 'medium',
                        iconCls: 'book-search-go',
                        text: Library.wording.filter_title,
                        scope: this,
                        handler: this.doBookSearch
                    },{
                        xtype: 'textfield',
                        name: 'book-search',
                        ref: '../../searchbox',
                        enableKeyEvents: true,
                        listeners: {
                            keypress: {scope: this, fn: function(field, e){
                                if (e.getKey() == e.ENTER) {
                                    this.doBookSearch();
                                }
                            }}
                        }
                    },{
                        xtype: 'button',
                        scale: 'medium',
                        iconCls: 'book-search-undo',
                        scope: this,
                        handler: this.undoBookSearch
                    }
                ])
            },{
                region: 'south',
                border: false,
                buttons: [this.initLoginButton()]
            }]
        });
        Library.App.superclass.initComponent.apply(this, arguments);
    }

});
