Ext.ns('Library');

Library.Main = {

    cfg: {
        nb: 25,
        controller: 'lib/controller.php',
        upload: 'lib/upload.php'
    },

    config: function() {
        return Library.Main.cfg;
    },

    addConfig: function(obj) {
        Ext.apply(Library.Main.cfg, obj);
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
            // override du ListMenu pour pouvoir setter une valeur de recherche
            // alors que le combo n'a pas encore ete charge
            this.store.load({
                scope: this,
                callback: function() {
                    this.setSelected(value);
                }
            });
        }
    }
});

Ext.override(Ext.form.Field, {
    getName : function(){
        // ajout du check si "dom" existe, pour eviter des erreurs
        return this.rendered && this.el.dom && this.el.dom.name ? this.el.dom.name : this.name || this.id || '';
    }
});