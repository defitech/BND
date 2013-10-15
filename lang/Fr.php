<?php

class Fr {

    public static $wording = array(
        'param_doesnt_exists' => "Le paramètre [%s] n'existe pas.",
        'param_cmd_unknow' => 'Paramètre cmd [%s] inconnu',

        'bad_thumb_type' => "Mauvais type de fichier pour l'aperçu",
        'bad_pdf_type' => "Mauvais type de fichier pour le PDF",
        'bad_login' => 'Mauvais couple login/pass',
        'bad_csv_type' => "Mauvais fichier fourni. Il doit s agir d'un CSV",
        
        'book_delete' => 'suppression du(des) livre(s): %s',
        'book_download' => 'telechargement du livre: %s (%s)',

        'pdf_doesnt_exists' => "Le fichier [%s] n'existe pas",
        'thumb_doesnt_generate' => "La miniature [%s] du pdf [%s] n'a pas pu être générée. Le PDF est peut-être trop lourd. Plus d'informations dans les logs.",
        'thumb_generation' => 'Création de la miniature: ',
        'book_still_save' => 'Les autres informations de ce livre ont quand même été sauvegardées',

        'type_delete' => 'suppression de la matiere: %s',
        'editor_delete' => "suppression de l'editeur: %s",
        'niveau_delete' => 'suppression du(des) niveau(x): %s',

        'own_deletion_not_allowed' => 'Impossible de se supprimer soi-même',
        'user_delete_confirm' => 'Cet utilisateur a %s téléchargements à son actif. Supprimer quand même?',
        'user_delete' => "suppression de l'utilisateur: %s",
        'user_type_delete' => "suppression du type d'utilisateur: %s",
        'no_connected_user' => "Droits d'accès insuffisants",
        
        'move_pdf_to_good_folder_notype' => "Le livre [%s] n'a pas de matière. Il faut lui en donner une!",
        'move_pdf_to_good_folder_error' => "Le déplacement de [%s] vers [%s] a échouée (erreur: %s)",

        'db_backup_done' => 'Base de données backupée (ok? %s)',
        
        'field_id' => 'ID',
        'field_type' => 'Matière',
        'field_editor' => 'Editeur',
        'field_niveau' => 'Niveaux',
        'field_title' => 'Titre',
        'field_isbn' => 'N° ISBN'

    );

}