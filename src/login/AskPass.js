Ext.ns('Library.login');

/**
 * Classe de gestion de la récupération de mot de passe avant de se logger
 *
 * @package Bnd
 */
Library.login.AskPass = Ext.extend(Ext.Window, {
    
    changePassAndLogin: function() {
        if (!this.getForm().getForm().isValid())
            return;
        
        this.getForm().getForm().submit({
            waitMsg: Library.wording.loading,
            scope: this,
            success: function(form, action) {
                Ext.Msg.wait();
                // en cas de succes, la personne aura ete automatiquement
                // logguee. Il suffit donc de recharger la page et la bnd
                // est a nouveau dispo
                window.location.href = window.location.href.split('?')[0];
            },
            failure: function(form, action) {
                Library.Main.failureForm(action.result);
            }
        });
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
            this.changePassAndLogin();
        }
    },

    /**
     * Recupere le formulaire de la window
     *
     * @return Ext.form.FormPanel
     */
    getForm: function() {
        return this.getComponent(0);
    },
            
    initForm: function() {
        var pid = Ext.id();
        return {
            xtype: 'form',
            waitMsgTarget: true,
            url: Library.Main.config().controller,
            baseParams: {
                cmd: 'changePassword',
                hash: this.data.hash,
                user: this.data.id
            },
            bodyStyle: 'padding: 10px;',
            defaults: {
                anchor: '100%'
            },
            items: [{
                xtype: 'panel',
                border: false,
                hideLabel: true,
                unstyled: true,
                html: String.format(Library.wording.user_password_renew, this.data.login)
            }, {
                xtype: 'textfield',
                inputType: 'password',
                id: pid,
                fieldLabel: Library.wording.connect_password,
                name: 'pass',
                ref: '../pass',
                enableKeyEvents: true,
                listeners: {
                    keyup: {scope: this, fn: this.onKeyUp}
                }
            }, {
                xtype: 'textfield',
                inputType: 'password',
                initialPassField: pid,
                fieldLabel: Library.wording.connect_password_confirm,
                name: 'pass_confirm',
                vtype: 'password',
                enableKeyEvents: true,
                listeners: {
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
            width: 300,
            height: 230,
            title: Library.wording.user_password_lost,
            border: false,
            layout: 'fit',
            items: this.initForm(),
            buttons: [{
                text: Library.wording.button_validate,
                scale: 'medium',
                iconCls: 'user-password-lost',
                scope: this,
                handler: this.changePassAndLogin
            }]
        });
        Ext.apply(this, {
            closable: false,
            resizable: false,
            onEsc: Ext.emptyFn
        });
        Library.login.AskPass.superclass.initComponent.call(this);

        this.on({
            afterrender: {scope: this, fn: function(){
                var p = this.pass;
                (function(){p.focus();}).defer(500);
            }}
        });
    }

});