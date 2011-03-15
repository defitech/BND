Ext.ns('Library');

Library.Main = {

    nb: 25,

    config: function() {
        return {
            controller: 'lib/controller.php'
        };
    },

    getJson: function(response) {
        try {
            var json = eval('(' + response.responseText + ')');
            if (!json.success) {
                Ext.Msg.show({
                    title: Library.wording.error_title,
                    msg: json.error
                });
            }
            return json;
        } catch (e) {
            Ext.Msg.show({
                title: Library.wording.error_title,
                msg: Library.wording.bad_json
            });
            return {
                success: false
            }
        }
    },

    failure: function(response) {
        Ext.Msg.show({
            title: Library.wording.error_title,
            msg: Library.wording.failure
        });
    },

    failureForm: function(json) {
        Ext.Msg.show({
            title: Library.wording.error_title,
            msg: json.error || json.msg || Library.wording.error_title
        });
    }
};


Ext.override(Ext.ux.menu.ListMenu, {
    setSelected : function (value) {
        value = this.selected = [].concat(value);

        if (this.loaded) {
            this.items.each(function(item){
                item.setChecked(false, true);
                for (var i = 0, len = value.length; i < len; i++) {
                    if (item.itemId == value[i]) {
                        item.setChecked(true, true);
                    }
                }
            }, this);
        } else {
            this.store.load({
                scope: this,
                callback: function() {
                    this.setSelected(value);
                }
            });
        }
    }
});
