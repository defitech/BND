Ext.onReady(function() {
    if (Library.Main.right(2)) {
        new Library.admin.App({
            renderTo: Ext.getBody()
        });
    } else {
        new Library.App({
            renderTo: Ext.getBody()
        });
    }
});
