Ext.onReady(function() {
    if (Library.Main.config().cid == 1) {
        new Library.admin.App({
            renderTo: Ext.getBody()
        });
    } else {
        new Library.App({
            renderTo: Ext.getBody()
        });
    }
    document.title = Library.wording.library_title;
});
