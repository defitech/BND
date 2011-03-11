Ext.onReady(function() {
    if (stacknmblue) {
        new Defitech.admin.App({
            renderTo: Ext.getBody()
        });
    } else {
        new Defitech.App({
            renderTo: Ext.getBody()
        });
    }
    document.title = Defitech.wording.library_title;
});
