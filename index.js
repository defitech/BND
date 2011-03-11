Ext.onReady(function() {
    if (stacknmblue) {
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
