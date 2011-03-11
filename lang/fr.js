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

    currentThumb: 'Aperçu actuel',
    currentPdf: 'PDF actuel',
    editor_add_title: 'Ajouter un éditeur',
    editor_add: 'Entrez le nom du nouvel éditeur',
    type_add_title: 'Ajouter une matière',
    type_add: 'Entrez le nom de la nouvelle matière',
    niveau_add_title: 'Ajouter un niveau (classe)',
    niveau_add: 'Entrez le nom du nouveau niveau',
    file_fieldset: 'Aperçu (miniature) et fichier PDF',

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

