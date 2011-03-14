Ext.ns('Library.wording');

Library.wording = {

    library_title: 'BND : Bibliothèque Numérique Download',

    thumb: 'Aperçu',
    title: 'Titre',
    tags: 'Mots-clé',
    type: 'Matière',
    editor: 'Editeur',
    niveau: 'Niveaux',
    isbn: 'N° ISBN',
    help: 'Aide',

    filter_title: 'Filtrer',
    filter_editor: 'par cet éditeur: {0}',
    filter_type: 'par cette matière: {0}',
    filter_niveau: 'par ce(s) niveau(x): {0}',

    download_book_button: 'Télécharger',
    
    currentThumb: 'Aperçu actuel',
    currentPdf: 'PDF actuel',
    
    editor_add_title: 'Ajouter un éditeur',
    editor_add: 'Entrez le nom du nouvel éditeur',
    type_add_title: 'Ajouter une matière',
    type_add: 'Entrez le nom de la nouvelle matière',
    niveau_add_title: 'Ajouter un niveau (classe)',
    niveau_add: 'Entrez le nom du nouveau niveau',

    editor_edit_title: 'Editer l\'éditeur',
    editor_edit: 'Changez le nom de l\'éditeur',
    type_edit_title: 'Editer la matière',
    type_edit: 'Changez le nom de la matière',
    niveau_edit_title: 'Editer le niveau (classe)',
    niveau_edit: 'Changez le nom du niveau',

    editor_remove_title: 'Supprimer un éditeur',
    editor_remove: 'Voulez-vous supprimer l\'éditeur {0}?',
    type_remove_title: 'Supprimer une matière',
    type_remove: 'Voulez-vous supprimer la matière {0}?',
    niveau_remove_title: 'Supprimer un niveau (classe)',
    niveau_remove: 'Voulez-vous supprimer le niveau {0}',
    remove_confirm_title: 'Confirmation de suppression',
    remove_confirm: '{0} livres sont liés à cet élément. Supprimer quand même?',

    file_fieldset: 'Aperçu (miniature)',
    filepdf_fieldset: 'Livre (fichier PDF)',

    connect_title: 'Connexion',
    connect_logout: 'Deconnexion',
    connect_login: 'Login',
    connect_password: 'Mot de passe',

    info_book_button: 'Informations',
    info_book_close: 'Fermer',
    info_book_save: 'Enregistrer',

    import_book_button: 'Importer CSV',

    add_book_button: 'Ajouter',
    add_book: "Ajout d'un nouveau livre",

    delete_book_button: 'Supprimer',
    delete_book_title: 'Suppression',
    delete_book: 'Supprimer les livres sélectionnés?',

    error_title: 'Erreur serveur',
    bad_json: 'Json mal formé',
    failure: 'Pas de réponse du serveur. Problème réseau.'

};

Ext.override(Ext.ux.grid.GridFilters, {
    menuFilterText: 'Filtres'
});

Ext.override(Ext.ux.menu.ListMenu, {
    loadingText: 'Chargement...'
});

