Ext.ns('Library.login');

Library.login.Form = Ext.extend(Ext.Window, {

    getForm: function() {
        return this.getComponent(0);
    },

    showSplashScreen: function() {
        var win = new Ext.Window({
            modal: true,
            width: 600,
            height: 400,
            layout: 'fit',
            items: [{
                xtype: 'panel',
                cls: 'book-conditions',
                autoLoad: 'conditions.html',
                border: false
            }],
            buttons: [{
                text: Library.wording.library_conditions_accept,
                iconCls: '',
                scale: 'medium',
                scope: this,
                handler: this.connect
            }, '->', {
                text: Library.wording.library_conditions_refuse,
                iconCls: '',
                scale: 'medium',
                handler: function() {
                    win.close();
                }
            }]
        });
        win.show();
    },

    connect: function() {
        if (this.getForm().getForm().isValid()) {
            this._mask.show();
            this.getForm().getForm().submit({
                url: Library.Main.config().controller,
                params: {
                    cmd: 'login'
                },
                scope: this,
                success: function(form, action) {
                    if (action.result.success) {
                        window.location.reload();
                    }
                },
                failure: function(form, action) {
                    this._mask.hide();
                    Library.Main.failureForm(action.result);
                }
            });
        }
    },

    onKeyUp: function(field, ev) {
        if (ev.getKey() === ev.ENTER) {
            this.connect();
        }
    },

    initForm: function() {
        return {
            xtype: 'form',
            bodyStyle: 'padding: 20px 30px;',
            border: false,
            defaults: {
                anchor: '95%'
            },
            items: [{
                xtype: 'textfield',
                fieldLabel: Library.wording.connect_login,
                name: 'login',
                ref: '../login',
                allowBlank: false,
                enableKeyEvents: true,
                listeners: {
                    focus: function(field){
                        field.el.highlight();
                    },
                    keyup: {scope: this, fn: this.onKeyUp}
                }
            }, {
                xtype: 'textfield',
                fieldLabel: Library.wording.connect_password,
                inputType: 'password',
                name: 'pass',
                ref: '../pass',
                allowBlank: false,
                enableKeyEvents: true,
                listeners: {
                    focus: function(field){
                        field.el.highlight();
                    },
                    keyup: {scope: this, fn: this.onKeyUp}
                }
            }]
        };
    },

    initComponent: function() {
        Ext.applyIf(this, {
            modal: false,
            title: Library.wording.connect_title,
            width: 400,
            height: 170,
            layout: 'fit',
            items: this.initForm(),
            buttons: [{
                text: Library.wording.connect_title,
                scale: 'medium',
                iconCls: 'book-connect',
                scope: this,
                handler: this.showSplashScreen
            }]
        });
        Ext.apply(this, {
            closable: false,
            resizable: false,
            onEsc: Ext.emptyFn
        })
        Library.login.Form.superclass.initComponent.call(this);

        this.on({
            afterrender: {scope: this, fn: function(){
                this._mask = new Ext.LoadMask(this.getEl());
                var login = this.login;
                (function(){login.focus();}).defer(300);
            }}
        })
    }

});

Ext.onReady(function(){
    var win = new Library.login.Form();
    win.show();
});