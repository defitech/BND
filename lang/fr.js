Ext.ns('Library.wording');

Library.wording = {

    library_title: 'BND : Biblioth\u00e8que Num\u00e9rique Download',

    library_conditions: 'Conditions d\'utilisation',
    library_conditions_refuse: 'Je ne suis pas d\'accord',
    library_conditions_accept: 'J\'ai signé et j\'accepte les conditions ci-dessus',

    thumb: 'Aperçu',
    title: 'Titre',
    tags: 'Mots-cl\u00e9',
    type: 'Mati\u00e8re',
    editor: 'Editeur',
    niveau: 'Niveaux',
    isbn: 'N° ISBN',
    help: 'Aide',

    filter_undo: 'Annuler les filtres',
    filter_title: 'Filtrer',
    filter_editor: 'par cet \u00e9diteur: {0}',
    filter_type: 'par cette mati\u00e8re: {0}',
    filter_niveau: 'par ce niveau: {0}',
    filter_niveaux: 'par ces niveaux',

    download_book_button: 'T\u00e9l\u00e9charger',

    swfupload_upload_progress: 'Fichier {0} à {1}',
    swfupload_error_title: 'Erreur d\'upload',
    swfupload_button_text: 'Parcourir...',
    swfupload_title: 'Upload de fichiers',
    
    currentThumb: 'Aperçu actuel',
    currentPdf: 'PDF actuel',
    
    editor_add_title: 'Ajouter un \u00e9diteur',
    editor_add: 'Entrez le nom du nouvel \u00e9diteur',
    type_add_title: 'Ajouter une mati\u00e8re',
    type_add: 'Entrez le nom de la nouvelle mati\u00e8re',
    niveau_add_title: 'Ajouter un niveau (classe)',
    niveau_add: 'Entrez le nom du nouveau niveau',

    editor_edit_title: 'Editer l\'\u00e9diteur',
    editor_edit: 'Changez le nom de l\'\u00e9diteur',
    type_edit_title: 'Editer la mati\u00e8re',
    type_edit: 'Changez le nom de la mati\u00e8re',
    niveau_edit_title: 'Editer le niveau (classe)',
    niveau_edit: 'Changez le nom du niveau',

    editor_remove_title: 'Supprimer un \u00e9diteur',
    editor_remove: 'Voulez-vous supprimer l\'\u00e9diteur {0}?',
    type_remove_title: 'Supprimer une mati\u00e8re',
    type_remove: 'Voulez-vous supprimer la mati\u00e8re {0}?',
    niveau_remove_title: 'Supprimer un niveau (classe)',
    niveau_remove: 'Voulez-vous supprimer le(s) niveau(x) {0}',
    niveau_remove_confirm: 'D\'autres \u00e9l\u00e9ments sont li\u00e9s \u00e0 ce(s) niveau(x): {0}. Supprimer quand m\u00eame?',
    remove_confirm_title: 'Confirmation de suppression',
    remove_confirm: '{0} livres sont li\u00e9s \u00e0 cet \u00e9l\u00e9ment. Supprimer quand m\u00eame?',

    file_fieldset: 'Aperçu (miniature)',
    filepdf_fieldset: 'Livre (fichier PDF)',

    connect_title: 'Connexion',
    connect_logout: 'Deconnexion',
    connect_login: 'Login',
    connect_password: 'Mot de passe',

    user_right: 'Droit (1 = admin)',
    user_title: 'Gestion des utilisateurs',
    user_button: 'Utilisateurs',
    user_delete_title: 'Suppression d\'un utilisateur',
    user_delete: 'Voulez-vous supprimer l\'utilisateur {0}?',

    info_book_button: 'Informations',
    info_book_close: 'Fermer',
    info_book_cancel: 'Annuler',
    info_book_save: 'Enregistrer',

    import_book_button: 'Importer CSV',

    add_book_button: 'Ajouter',
    add_book: "Ajout d'un nouveau livre",

    delete_book_button: 'Supprimer',
    delete_book_title: 'Suppression',
    delete_book: 'Supprimer les livres s\u00e9lectionn\u00e9s?',

    error_title: 'Erreur serveur',
    loading: 'Chargement...',
    bad_json: 'Json mal form\u00e9',
    failure: 'Pas de r\u00e9ponse du serveur. Probl\u00e8me r\u00e9seau.'

};

if (typeof Ext.ux != 'undefined' && typeof Ext.ux.grid != 'undefined') {
    Ext.override(Ext.ux.grid.GridFilters, {
        menuFilterText: 'Filtres'
    });

    Ext.override(Ext.ux.menu.ListMenu, {
        loadingText: Library.wording.loading
    });
}
