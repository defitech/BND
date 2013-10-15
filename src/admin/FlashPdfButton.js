
Ext.ns('Library.admin');

/**
 * Bouton qui met en place le bouton Flash pour la selection de fichier PDF
 * et qui gere une barre de progression afin de pouvoir suivre l'evolution
 * de l'upload
 * 
 * @version 0.0.0.0.0.1, 2 mai 2012
 */
Library.admin.FlashPdfButton = Ext.extend(Ext.form.CompositeField, {
    
    /**
     * @var determine si l'upload du PDF a commence ou non
     */
    uploadStarted: false,
    
    /**
     * @var Template pour la gestion du bouton d'upload
     */
    tplUpload: new Ext.XTemplate(
        '<div id="{container}">',
            '<div id="{button}" class="uploadButton">{text}</div>',
        '</div>'
    ),
    
    /**
     * @var Template de gestion de la barre de progression
     */
    tplProgress: new Ext.XTemplate(
        '<div id="{id}" style="height: {height}px;" class="progressbar">{text}</div>'
    ),
    
    /**
     * Methode appelee lorsqu'une erreur survient au moment de l'upload
     * 
     * @param {Uploader} uploader l'objet PlUpload
     * @param {Object} file les infos du fichier en question
     * @return {void}
     */
    eventUploadError: function(uploader, file) {
        console.log('upload error', file);
    },
    
    /**
     * Methode appelee lorsqu'on appuie sur le bouton d'annulation d'upload
     * 
     * @return {void}
     */
    eventCancelUpload: function() {
        this.swfu.stop();
        Ext.get(this.idProgressZone).update(Library.wording.swfupload_cancel);
    },
    
    /**
     * Methode appelee lorsqu'on ferme la fenetre de selection de fichiers
     * 
     * @param {Uploader} uploader l'objet PlUpload
     * @return {void}
     */
    eventFileDialogComplete: function(uploader) {
        //console.log('file dialog complete', uploader);
        this.swfu.start();
    },
    
    /**
     * Methode appelee lorsqu'un upload commence. Elle est appelee a chaque
     * debut d'upload de la file d'attente. Le truc, c'est que dans notre cas
     * il n'y a pas de file d'attente, puisqu'on ne peut envoyer qu'un seul
     * PDF pour un bouquin.
     * 
     * @param {Uploader} uploader l'objet PlUpload
     * @param {Object} file les infos du fichier en question
     * @return {void}
     */
    eventUploadStart: function(uploader, file) {
        //console.log('upload start', file);
        Ext.get(this.idProgressZone).update(Library.wording.swfupload_progress);
        this.uploadStarted = true;
        Ext.getCmp(this.idCancelButton).enable();
        this.fireEvent('uploadstart', this, file);
    },
    
    /**
     * Methode appelee a chaques petites progressions de l'upload du PDF
     * 
     * @param {Uploader} uploader l'objet PlUpload
     * @param {Object} file les infos du fichier en question
     * @param {Object} response la reponse du serveur
     * @return {void}
     */
    eventUploadProgress: function(uploader, file, response){
        // on set a la barre de progression sa largeur en fonction du
        // pourcentage d'upload du fichier PDF courant
        var el = Ext.get(this.idProgressZone);
        el.setWidth(file.percent + '%');
    },
    
    /**
     * Methode appelee lorsqu'un upload de la file d'attente est termine. Il
     * pourrait en avoir d'autres, mais comme on limite a un seul ici, voila...
     * 
     * @param {Uploader} uploader l'objet PlUpload
     * @param {Object} file les infos du fichier en question
     * @param {Object} response la reponse serveur brute
     * @return {void}
     */
    eventUploadSuccess: function(uploader, file, response) {
        //console.log('upload success', file, response);
        this.uploadStarted = false;
        
        var json = {};
        try {
            json = Ext.decode(response.response);
        } catch (e) {
            json = {success: false, message: e.message + ' : ' + response};
        }
        
        if (typeof json.success == 'undefined' || json.success) {
            Ext.get(this.idProgressZone).update(Library.wording.swfupload_after);
            Ext.getCmp(this.idCancelButton).disable();
            this.fireEvent('uploadsuccess', this, json, file, response);
        } else {
            Ext.get(this.idProgressZone).update(Library.wording.swfupload_error);
            alert(json.message || json.msg || (Ext.isObject(json.error) ? json.error.message : json.error));
        }
    },
    
    
    /**
     * Initialise l'objet SwfUpload avec les parametres qu'on veut, soit que
     * des PDF, qu'un seul a la fois, et maximum 500MB
     * 
     * @return {void}
     */
    initFlashObject: function() {
        var swfu = new plupload.Uploader({
            runtimes : 'gears,html5,flash,silverlight',
            browse_button : this.idFlashButton,
            container: this.idProgressContainer,
            max_file_size : this.data.maxpostsize,
            chunk_size: '1mb',
            // adresse du fichier PHP qui gere l'upload
            url : Library.Main.config().upload,
            // adresse du fichier flash qui gere la selection de fichiers
            flash_swf_url : Library.Main.config().libspath + 'extjsux/plupload/js/Moxie.swf',
            silverlight_xap_url : Library.Main.config().libspath + 'extjsux/plupload/js/Moxie.xap',
            filters : [
                {title : 'PDF', extensions : 'pdf'}
            ]
        });
        
        swfu.bind('Error', this.eventUploadError.createDelegate(this));
        swfu.bind('UploadFile', this.eventUploadStart.createDelegate(this));
        swfu.bind('ChunkUploaded', this.eventUploadProgress.createDelegate(this));
        swfu.bind('FileUploaded', this.eventUploadSuccess.createDelegate(this));
        swfu.bind('QueueChanged', this.eventFileDialogComplete.createDelegate(this));
        
        swfu.init();
        
        return swfu;
    },
    
    /**
     * Initialisation du composant. Il s'agit d'un CompositeField, ainsi on peut
     * sur une meme ligne mettre le bouton Flash et la barre de progression
     * 
     * @return {void}
     */
    initComponent: function() {
        this.idFlashButton = Ext.id();
        this.idProgressZone = Ext.id();
        this.idProgressContainer = Ext.id();
        this.idCancelButton = Ext.id();
        
        this.addEvents(
            'uploadsuccess',
            'uploadstart'
        );
        
        Ext.apply(this, {
            border: false,
            items: [{xtype: 'hidden'}, {
                // definition de la barre de progression
                xtype: 'panel',
                // on la flex a 1 pour qu'elle prenne le maximum de place en
                // largeur
                flex: 1,
                // le fond par defaut
                bodyStyle: 'background-color: #99D;',
                // l'element qui a une largeur de zero et qui va grandir petit
                // a petit au fur et a mesure que l'upload avance
                html: this.tplProgress.apply({
                    id: this.idProgressZone,
                    height: 20,
                    text: Library.wording.swfupload_before
                })
            },{
                // definition du bouton Flash
                xtype: 'panel',
                border: false,
                html: this.tplUpload.apply({
                    container: this.idProgressContainer,
                    button: this.idFlashButton,
                    text: Library.wording.swfupload_button_text
                }),
                listeners: {
                    afterrender: {scope: this, fn: function(cmp){
                        // on attend que le panel soit rendu avant d'initialiser
                        // le bouton Flash, car on doit etre sur que l'element
                        // HTML existe bien dans le DOM
                        this.swfu = this.initFlashObject();
                    }}
                }
            },{
                xtype: 'button',
                iconCls: 'book-relation-remove',
                id: this.idCancelButton,
                disabled: true,
                scope: this,
                handler: this.eventCancelUpload
            }]
            
        });
        
        Library.admin.FlashPdfButton.superclass.initComponent.call(this);
        
        this.on({
            destroy: {scope: this, fn: function(cmp){
                // au moment de la destruction de l'objet, on detruit egalement
                // la propriete qui contient le bouton Flash
                this.swfu.destroy();
                delete this.swfu;
            }}
        });
    }
    
});

Ext.reg('flashpdfbutton', Library.admin.FlashPdfButton);
