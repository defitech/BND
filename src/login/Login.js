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
    showSplashScreen: function() {
        if (this.getForm().getForm().isValid()) {
            // le mode "light" est utilise lorsqu'on fait une action dans la
            // BND alors qu'on a perdu la connexion. Si on est pas en mode
            // light, c'est qu'on se connecte la 1ere fois. On montre donc la
            // fenetre des conditions legales.
            if (!this.light) {
                var win = new Ext.Window({
                    modal: true,
                    width: 680,
                    height: 610,
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
                            win.close();
                        }
                    }, '->', {
                        text: Library.wording.library_conditions_accept,
                        iconCls: 'book-conditions-ok',
                        scale: 'large',
                        scope: this,
                        tabIndex: 2,
                        handler: function() {
                            this.connect();
                            win.close();
                        }
                    }]
                });
                win.show();
            } else {
                // en mode light, on se connecte directement car on est deja
                // dans l'application et on a deja accepte les conditions
                this.connect();
            }
        }
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
                        if (!this.light)
                            window.location.reload();
                        else
                            // si on est en mode light, on a deja charge tous
                            // les scripts, on a rien donc besoin de faire
                            this.close();
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
     * @return void
     */
    onKeyUp: function(field, ev) {
        if (ev.getKey() === ev.ENTER) {
            this.showSplashScreen();
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