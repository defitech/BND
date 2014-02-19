Ext.ns('Library.login');

/**
 * Classe de gestion du login principal du site. Gere la connexion ainsi que
 * la lecture des conditions
 *
 * @package Bnd
 */
Library.login.Form = Ext.extend(Ext.Window, {

    /**
     * Recupere le formulaire de la window
     *
     * @return Ext.form.FormPanel
     */
    getForm: function() {
        return this.getComponent(0);
    },

    /**
     * Avant de verifier le couple login/password, on s'assure que la perssone
     * a bien lu et accepte les conditions d'utilisation du site
     *
     * @return void
     */
    showSplashScreen: function(access_level) {
        if (this.getForm().getForm().isValid()) {
            if(access_level == 'pristine') {
                // N'a jamais accédé et n'a pas encore signé
                var win = new Ext.Window({
                modal: true,
                width: 680,
                height: 680,
                title: Library.wording.library_conditions,
                iconCls: 'book-main',
                layout: 'fit',
                items: [{
                    xtype: 'panel',
                    cls: 'book-conditions',
                    autoLoad: 'conditions.html',
                    border: false,
                    autoScroll: true
                }],
                buttonAlign: 'left',
                buttons: [{
                    text: Library.wording.library_conditions_refuse,
                    iconCls: 'book-conditions-no',
                    scale: 'large',
                    tabIndex: 1,
                    handler: function() {
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
                }, '->', {
                    text: Library.wording.library_conditions_accept,
                    iconCls: 'book-conditions-ok',
                    scale: 'large',
                    scope: this,
                    tabIndex: 2,
                    handler: function() {
                        window.location.reload();
                        win.close();
                    }
                }],
                listeners: {
                    afterrender: function(cmp) {
                        (function(){
                            cmp.buttons[2].focus();
                            cmp.buttons[2].toggle();
                        }).defer(200);
                    }
                }
            });
            } else {
                // A déjà accédé et n'a pas encore signé => STOP
                var win = new Ext.Window({
                modal: true,
                width: 680,
                height: 680,
                title: Library.wording.library_conditions,
                iconCls: 'book-main',
                layout: 'fit',
                items: [{
                    xtype: 'panel',
                    cls: 'book-conditions',
                    autoLoad: 'conditions.html',
                    border: false,
                    autoScroll: true
                }],
                buttonAlign: 'center',
                buttons: [{
                    text: Library.wording.library_conditions_sign_and_return,
                    iconCls: 'book-conditions-form',
                    scale: 'large',
                    tabIndex: 1,
                    handler: function() {
                        window.location.reload();
                    },
                }],
            });
            }
            win.show();
        }
    },
    
    /**
     * Affiche la popup qui permet de demander le mot de passe si on l'a
     * oublie
     * 
     * @return void
     */
    showForgetPass: function() {
        var me = this;
        var win = new Ext.Window({
            title: Library.wording.user_password_lost,
            modal: true,
            width: 220,
            height: 250,
            border: false,
            layout: 'fit',
            items: {
                xtype: 'form',
                bodyStyle: 'padding: 10px;',
                url: Library.Main.config().controller,
                hideLabels: true,
                waitMsgTarget: true,
                baseParams: {
                    cmd: 'remindPassword'
                },
                items: [{
                    xtype: 'panel',
                    border: false,
                    cls: 'user-password-panel',
                    bodyStyle: '10px 0;',
                    html: Library.wording.user_password_lost_label
                }, {
                    xtype: 'textfield',
                    name: 'askforpass',
                    anchor: '100%',
                    ref: '../passfield',
                    emptyText: Library.wording.user_password_label,
                    allowBlank: false,
                    enableKeyEvents: true,
                    listeners: {
                        keyup: function(field, ev){
                            if (ev.getKey() === ev.ENTER) {
                                me.requestPassword(win);
                            }
                        }
                    }
                }]
            },
            buttons: [
            {
                text: "Valider",
                iconCls: 'user-password-lost',
                scale: 'medium',
                handler: function() {
                    me.requestPassword(win);
                }
            },{
                text: Library.wording.info_book_close,
                iconCls: 'book-window-close',
                scale: 'medium',
                handler: function() {
                    win.close();
                }
            }],
            listeners: {
                afterrender: function(){
                    (function(){win.passfield.focus();}).defer(300);
                }
            }
        });
        win.show();
    },
    
    /**
     * Check si le formulaire de mot de passe oublie est valide et envoie une
     * requete au serveur pour creer la demande de nouveau mot de passe. Si
     * tout se passe bien, un mail est automatiquement envoye via le PHP
     * 
     * @param {Ext.Window} win
     * @return void
     */
    requestPassword: function(win) {
        if (!win.getComponent(0).getForm().isValid())
            return;

        win.getComponent(0).getForm().submit({
            waitMsg: Library.wording.loading,
            success: function(form, action) {
                Ext.Msg.alert(Library.wording.user_password_lost, action.result.msg, function(){
                    win.close();
                });
            },
            failure: function(action, action) {
                Ext.Msg.show({
                    title: Library.wording.user_password_lost,
                    msg: action.result.error || action.result.msg || action.result.message,
                    buttons: Ext.Msg.OK,
                    icon: Ext.Msg.WARNING
                });
            }
        });
    },

    /**
     * Envoie la requete de verification de login/password. Si tout est ok
     * on redirige sur la page principale
     *
     * @return void
     */
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
                        // Si on est pas en mode light, c'est qu'on se log pour
                        // la 1ere fois, on recharge donc la page pour loader
                        // tous les scripts de la BND
                        if (!this.light) {
                            if(action.result.access_level != 'trusted') {
                                this.showSplashScreen(action.result.access_level);
                            } else {
                                window.location.reload();
                            }
                        }
                        else {
                            // si on est en mode light, on a deja charge tous
                            // les scripts, on a rien donc besoin de faire
                            this.close();
                        }
                    }
                },
                failure: function(form, action) {
                    this._mask.hide();
                    Library.Main.failureForm(action.result);
                }
            });
        }
    },

    /**
     * Fonction definie pour les champs login/password, gerant la touche ENTER
     * pour envoyer le formulaire
     *
     * @param {Ext.form.Field} field
     * @param {Ext.Event} ev
     * @return void
     */
    onKeyUp: function(field, ev) {
        if (ev.getKey() === ev.ENTER) {
            this.connect();
        }
    },

    /**
     * Initialisation du formulaire
     *
     * @return Ext.form.FormPanel
     */
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

    /**
     * Initialisation du composant
     *
     * @return void
     */
    initComponent: function() {
        Ext.applyIf(this, {
            modal: false,
            title: Library.wording.connect_title,
            width: 400,
            height: 170,
            iconCls: 'book-main',
            layout: 'fit',
            items: this.initForm(),
            buttons: [{
                text: Library.wording.user_password_lost,
                scale: 'medium',
                iconCls: 'user-password-lost',
                scope: this,
                handler: this.showForgetPass
            },{
                text: Library.wording.connect_title,
                scale: 'medium',
                iconCls: 'book-connect',
                scope: this,
                handler: this.connect
            }]
        });
        Ext.apply(this, {
            closable: false,
            resizable: false,
            onEsc: Ext.emptyFn
        });
        Library.login.Form.superclass.initComponent.call(this);

        this.on({
            afterrender: {scope: this, fn: function(){
                this._mask = new Ext.LoadMask(this.getEl());
                var login = this.login;
                (function(){login.focus();}).defer(300);
            }}
        });
    }

});
