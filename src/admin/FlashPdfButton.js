
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
     * Methode appelee lorsqu'une erreur survient au moment de l'ajout de
     * fichiers dans la liste d'attente d'upload
     * 
     * @param {Object} file les infos du fichier en question
     * @param {Number} error le numero d'erreur
     * @param {String} message le message d'erreur
     * @return {void}
     */
    eventFileQueueError: function(file, error, message) {
        try {
            // Handle this error separately because we don't want to create a FileProgress element for it.
            switch (error) {
                case SWFUpload.QUEUE_ERROR.QUEUE_LIMIT_EXCEEDED:
                    alert("You have attempted to queue too many files.\n" + (message === 0 ? "You have reached the upload limit." : "You may select " + (message > 1 ? "up to " + message + " files." : "one file.")));
                    return;
                case SWFUpload.QUEUE_ERROR.FILE_EXCEEDS_SIZE_LIMIT:
                    alert("The file you selected is too big: " + message);
                    return;
                case SWFUpload.QUEUE_ERROR.ZERO_BYTE_FILE:
                    alert("The file you selected is empty.  Please select another file: " + message);
                    return;
                case SWFUpload.QUEUE_ERROR.INVALID_FILETYPE:
                    alert("The file you choose is not an allowed file type: " + message);
                    return;
                default:
                    alert("An error occurred in the upload. Try again later: " + message);
                    return;
            }
        } catch (e) {
            alert("An error occurred in the upload. Try again later: " + message + ' / ' + e);
            return;
        }
    },
    
    /**
     * Methode appelee lorsqu'une erreur survient pendant l'upload
     * 
     * @param {Object} file les infos du fichier en question
     * @param {Number} errorCode le numero d'erreur
     * @param {String} message le message d'erreur
     * @return {void}
     */
    eventUploadError: function(file, errorCode, message) {
        try {
            // Don't show cancelled error boxes
            if (errorCode === SWFUpload.UPLOAD_ERROR.FILE_CANCELLED) return;

            // on gere ces erreurs separement car il n'y a pas besoin de faire
            // avec la barre de progression pour ca
            switch (errorCode) {
                case SWFUpload.UPLOAD_ERROR.MISSING_UPLOAD_URL:
                    alert("There was a configuration error. You will not be able to upload a resume at this time: " + message);
                    return;
                case SWFUpload.UPLOAD_ERROR.UPLOAD_LIMIT_EXCEEDED:
                    alert("You may only upload 1 file: " + message);
                    return;
            }

            // theoriquement ici, on fait avec la barre de progression. Pour le
            // moment, ce n'est pas le cas
            switch (errorCode) {
                case SWFUpload.UPLOAD_ERROR.HTTP_ERROR:
                    alert("Upload Error: " + message);
                    break;
                case SWFUpload.UPLOAD_ERROR.UPLOAD_FAILED:
                    alert("Upload Failed: " + message);
                    break;
                case SWFUpload.UPLOAD_ERROR.IO_ERROR:
                    alert("Server (IO) Error: " + message);
                    break;
                case SWFUpload.UPLOAD_ERROR.SECURITY_ERROR:
                    alert("Security Error: " + message);
                    break;
                case SWFUpload.UPLOAD_ERROR.FILE_CANCELLED:
                    alert("Upload Cancelled: " + message);
                    break;
                case SWFUpload.UPLOAD_ERROR.UPLOAD_STOPPED:
                    alert("Upload Stopped: " + message);
                    break;
            }
        } catch (e) {
            alert("An error occurred in the upload. Try again later: " + message + ' / ' + e);
            return;
        }
    },
    
    /**
     * Methode appelee lorsqu'on ferme la fenetre de selection de fichiers
     * 
     * @param {Number} nbFiles le nombre de fichiers selectionnes
     * @param {Number} nbFilesQueued le nombre de fichiers actuellement en file
     * d'attente
     * @param {Number} nbTotalFilesQueued le nombre total de fichiers
     * actuellement en file d'attente
     * @return {void}
     */
    eventFileDialogComplete: function(nbFiles, nbFilesQueued, nbTotalFilesQueued) {
        console.log('file dialog complete', nbFiles, nbFilesQueued, nbTotalFilesQueued);
        // on lance automatiquement l'upload si le nombre de fichiers
        // selectionnes est bien 1. On s'assure par ailleurs qu'il n'y a pas
        // deja un upload en route
        if (nbFiles == 1 && !this.uploadStarted)
            this.swfu.startUpload();
    },
    
    /**
     * Methode appelee lorsqu'un upload commence. Elle est appelee a chaque
     * debut d'upload de la file d'attente. Le truc, c'est que dans notre cas
     * il n'y a pas de file d'attente, puisqu'on ne peut envoyer qu'un seul
     * PDF pour un bouquin.
     * 
     * @param {Object} file les infos du fichier en question
     * @return {void}
     */
    eventUploadStart: function(file) {
        console.log('upload start', file);
        this.uploadStarted = true;
    },
    
    /**
     * Methode appelee a chaques petites progressions de l'upload du PDF
     * 
     * @param {Object} file les infos du fichier en question
     * @param {Decimal} current le nombre de bytes envoyes
     * @param {Decimal} total le nombre de bytes total du fichier
     * @return {void}
     */
    eventUploadProgress: function(file, current, total){
        console.log('upload progress', file, current, total);
        // on recupere la largeur totale de la zone de progression
        var cmp = Ext.get(this.idProgressContainer);
        var twidth = cmp.getWidth();

        // on set a la barre de progression sa largeur en fonction du nombre
        // courant de bytes envoyes et de la largeur totale de la zone
        var el = Ext.get(this.idProgressZone);
        el.setWidth(current * twidth / total);
    },
    
    /**
     * Methode appelee lorsqu'un upload de la file d'attente est termine. Il
     * pourrait en avoir d'autres, mais comme on limite a un seul ici, voila...
     * 
     * @param {Object} file les infos du fichier en question
     * @param {Object} data les donnees revenant du serveur
     * @param {XmlHttpResponse} response la reponse serveur brute
     * @return {void}
     */
    eventUploadSuccess: function(file, data, response) {
        console.log('upload success', file, data,response);
        this.uploadStarted = false;
        
        var json = {};
        try {
            json = Ext.decode(data);
        } catch (e) {
            json = {success: false, message: e.message + ' : ' + data};
        }
        
        if (json.success) {
            this.fireEvent('uploadsuccess', this, json, file, data, response);
        } else {
            alert(json.message || json.msg || json.error);
        }
    },
    
    
    /**
     * Initialise l'objet SwfUpload avec les parametres qu'on veut, soit que
     * des PDF, qu'un seul a la fois, et maximum 500MB
     * 
     * @return {void}
     */
    initSwfObject: function() {
        return new SWFUpload({
            // adresse du fichier PHP qui gere l'upload
            upload_url: Library.Main.config().upload,
            // adresse du fichier flash qui gere la selection de fichiers
            flash_url: Library.Main.config().libspath + 'extjsux/FileUploader/swfupload.swf',
            
            // bug swfupload (connu) des sessions
            post_params: {'PHPSESSID' : Library.Main.config().sid}, 

            // id de l'element HTML qui contiendra le bouton Flash
            button_placeholder_id   : this.idFlashButton,
            // configurations du bouton Flash
            button_text             : '<span class="swfupload_button">' + Library.wording.swfupload_button_text + '</span>',
            button_text_style       : '.swfupload_button{font-family:Arial,sans-serif; font-size:11px;}',
            button_width            : 50,
            button_height           : 25,

            // le nom du fichier dans le $_FILES du fichier PHP
            file_post_name          : 'Filedata',
            // les types de fichiers autorises
            file_types              : '*.pdf',
            file_types_description  : 'PDF',
            // la limite en taille
            file_size_limit         : '500 MB',
            // le nombre d'upload autorises (0 = infini)
            file_upload_limit       : 0,
            // le nombre de fichiers qu'on peut selectionner en une fois
            file_queue_limit        : 1,

            file_queue_error_handler : this.eventFileQueueError.createDelegate(this),
            file_dialog_complete_handler : this.eventFileDialogComplete.createDelegate(this),

            upload_error_handler : this.eventUploadError.createDelegate(this),
            upload_start: this.eventUploadStart.createDelegate(this),
            upload_progress_handler : this.eventUploadProgress.createDelegate(this),
            upload_success_handler: this.eventUploadSuccess.createDelegate(this)
        });
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
        
        this.addEvents(
            'uploadsuccess'
        );
        
        Ext.apply(this, {
            border: false,
            items: [{
                // definition de la barre de progression
                xtype: 'panel',
                // on la flex a 1 pour qu'elle prenne le maximum de place en
                // largeur
                flex: 1,
                id: this.idProgressContainer,
                // le fond par defaut
                bodyStyle: 'background-color: #99DD99;',
                // l'element qui a une largeur de zero et qui va grandir petit
                // a petit au fur et a mesure que l'upload avance
                html: '<div id="' + this.idProgressZone + '" style="height: 20px; width: 0; background-color: #000099;"></div>'
            },{
                // definition du bouton Flash
                xtype: 'panel',
                border: false,
                html: '<div id="' + this.idFlashButton + '"></div>',
                listeners: {
                    afterrender: {scope: this, fn: function(cmp){
                        // on attend que le panel soit rendu avant d'initialiser
                        // le bouton Flash, car on doit etre sur que l'element
                        // HTML existe bien dans le DOM
                        this.swfu = this.initSwfObject();
                    }}
                }
            },{
                xtype: 'button',
                iconCls: 'book-relation-remove',
                scope: this,
                handler: function(){
                    this.swfu.cancelUpload(null, false);
                }
            }]
            
        });
        
        Library.admin.FlashPdfButton.superclass.initComponent.call(this);
        
        this.on({
            destroy: {scope: this, fn: function(cmp){
                // au moment de la destruction de l'objet, on detruit egalement
                // la propriete qui contient le bouton Flash
                delete this.swfu;
            }}
        });
    }
    
});

Ext.reg('flashpdfbutton', Library.admin.FlashPdfButton);
