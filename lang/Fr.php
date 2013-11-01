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
        'old_pass_wrong' => "Le changement de mot de passe n'a pas pu avoir lieu! Mot de passe courant faux.",
        
        'move_pdf_to_good_folder_notype' => "Le livre [%s] n'a pas de matière. Il faut lui en donner une!",
        'move_pdf_to_good_folder_error' => "Le déplacement de [%s] vers [%s] a échouée (erreur: %s)",

        'db_backup_done' => 'Base de données backupée (ok? %s)',
        
        'field_id' => 'ID',
        'field_type' => 'Matière',
        'field_editor' => 'Editeur',
        'field_niveau' => 'Niveaux',
        'field_title' => 'Titre',
        'field_isbn' => 'N° ISBN',
        
        'mail_subject' => "BND > Defitech > Récupération de mot de passe",
        'mail_content' => "Bonjour,\n\nVous avez demander un nouveau mot de passe. Rendez-vous à l'adresse suivante: %s \nVotre login est le suivant: %s\n\nSi vous n'avez pas demandé un nouveau mot de passe, ignorez simplement cet email. Merci de ne pas y répondre.\n\n Fondaton defitech\n%s",
        'mail_content_create' => "Bonjour,\n\nUn compte est disponible pour vous dans la BND. Nous vous proposons de vous rendre à l'adresse ci-dessous pour choisir votre mot de passe.\n\nVotre login: %s\nAdresse: %s \n\nMerci de ne pas répondre à cet email.\n\n Fondaton defitech\n%s",
        'mail_sent' => "Un mail vous a été envoyé. Suivez-en les instructions pour changer votre mot de passe",
        'mail_sent_create' => "Le mail a bien été envoyé.",
        'mail_error' => "Personne n'a de login ou de mail ainsi que vous l'avez écrit",
        'mail_nomail' => "Votre compte existe mais n'a pas d'email associé. Vous ne pourrez pas recevoir de notification. Veuillez contacter defitech."

    );

}