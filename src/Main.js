Ext.ns('Library');

Ext.onReady(function() {
    document.title = Library.wording.library_title;
    Ext.Msg.minWidth = 450;
});

// si une exception de type "unauthorized" apparait, on montre la box de
// login.
Ext.Ajax.on('requestexception', function(conn, response) {
    if (response.status == 401) {
        var win = new Library.login.Form({
            modal: true,
            light: true
        });
        win.show();
    }
});

Ext.apply(Ext.form.VTypes, {
    
    passwordText: "Error",
    
    password: function(val, field) {
        if (field.initialPassField) {
            var pwd = Ext.getCmp(field.initialPassField);
            var r = val === pwd.getValue();
            return r;
        }
        return true;
    }
});

Library.Main = {

    cfg: {
        nb: 25,
        controller: 'lib/controller.php',
        upload: 'lib/upload.php',
        bg: 'resources/background/',
        image: 'lib/image.php?i={0}&t={1}'
    },

    config: function() {
        return Library.Main.cfg;
    },

    addConfig: function(obj) {
        Ext.apply(Library.Main.cfg, obj);
        // si on a un background sette, on le place dans le body
        if (obj.background) {
            Library.Main.setBackground(obj.background);
        }
    },

    right: function(right) {
        // on check si le droit vaut quelque chose (!=null ou 0) et qu'il est
        // plus petit ou egal au droit de l'utilisateur connecte (1=admin)
        return Library.Main.config().rid * 1 && Library.Main.config().rid <= right;
    },
            
    setBackground: function(file) {
        Ext.getBody().setStyle('backgroundImage', 'url(' + Library.Main.config().bg + file + ')');
    },

    getJson: function(response) {
        try {
            var json = Ext.decode(response.responseText);
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
                msg: Library.wording.bad_json + ': ' + response.responseText
            });
            return {
                success: false
            };
        }
    },

    failure: function(response) {
        // on montre l'erreur s'il s'agit d'autre chose qu'une Unauthorized
        if (response.status != 401)
            Ext.Msg.show({
                title: Library.wording.error_title,
                msg: Library.wording.failure
            });
    },

    failureForm: function(json) {
        Ext.Msg.show({
            title: Library.wording.error_title,
            msg: json.error || json.msg || Library.wording.error_title,
            width: 400
        });
    }
};

Ext.ns('Ext.ux.menu');
if (typeof Ext.ux.menu.ListMenu != 'undefined') {
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
}

Ext.ns('Et.form.Action');
if (typeof Ext.form.Action.Submit != 'undefined') {
    Ext.override(Ext.form.Action.Submit, {
        handleResponse : function(response){
            if(this.form.errorReader){
                var rs = this.form.errorReader.read(response);
                var errors = [];
                if(rs.records){
                    for(var i = 0, len = rs.records.length; i < len; i++) {
                        var r = rs.records[i];
                        errors[i] = r.data;
                    }
                }
                if(errors.length < 1){
                    errors = null;
                }
                return {
                    success : rs.success,
                    errors : errors
                };
            }
            // ajout d'un try catch pour eviter des erreurs de js si la reponse est mal formee
            try {
                return Ext.decode(response.responseText);
            } catch (e) {
                var msg = response.responseText + '<br/>Javascript: ' + e.toString();
                return {success: false, error: msg};
            }
        }
    });
}

if (typeof Ext.form.Field != 'undefined')
    Ext.override(Ext.form.Field, {
        getName : function(){
            // ajout du check si "dom" existe, pour eviter des erreurs
            return this.rendered && this.el.dom && this.el.dom.name ? this.el.dom.name : this.name || this.id || '';
        }
    });
    
Ext.ns('Ext.grid');
if (typeof Ext.grid.ColumnModel !== 'undefined')
    Ext.override(Ext.grid.ColumnModel, {
        getTotalWidth: function(includeHidden) {
            var off = 0;
            // redefinition de l'offset pour Chrome, qui a perdu des pixels entre
            // ses versions...
            if (Ext.isChrome){
                off = 2;
            }
            if (!this.totalWidth) {
                this.totalWidth = 0;
                for (var i = 0, len = this.config.length; i < len; i++) {
                    if (includeHidden || !this.isHidden(i)) {
                        this.totalWidth += this.getColumnWidth(i)+off;
                    }
                }
            }
            return this.totalWidth;
        }
    });