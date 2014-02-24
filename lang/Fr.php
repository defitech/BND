<?php

class Fr {

    public static $wording = array(
        'param_doesnt_exists' => "Le paramètre [%s] n'existe pas.",
        'param_cmd_unknow' => 'Paramètre cmd [%s] inconnu',

        'bad_thumb_type' => "Mauvais type de fichier pour l'aperçu",
        'bad_pdf_type' => "Mauvais type de fichier pour le PDF",
        'bad_login' => 'Mauvais identifiant et ou mauvais mot de passe!',
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
        'mail_content' => "Bonjour,

Vous avez demandé un nouveau mot de passe pour la base de données de
livres. Rendez-vous à l'adresse suivante:

	%s

Votre login est le suivant: %s

Si vous n'avez pas demandé un nouveau mot de passe, ignorez simplement
cet email. Merci de ne pas y répondre.

  La fondation defitech\n%s",

// Mail complet pour mot de passe initial
        'mail_subject_create' => "BND > Defitech > Création d'un compte",
        'mail_content_create' => "Bonjour,

Un compte vient d'être créé pour vous dans la base de données de livres
numérisés de defitech, la BND. Votre identifiant de connexion (login)
est: '%s'

Nous vous proposons de vous rendre à l'adresse ci-dessous pour choisir
votre mot de passe:

    %s

Merci de bien vouloir nous renvoyer la convention signée, elle vous est
présentée à la première connexion.

Voici encore une rapide marche à suivre pour l'utilisation de la BND:

  1. Allez sur le site: http://bnd.defitech.ch/

  2. Entrez le login (%s) et le mot de passe

  3. Cliquez sur: \"J'ai signé et j'accepte les conditions ci-dessus\".
     (N'oubliez de nous renvoyer la convention signée.)

  4. Trouvez le livre que vous cherchez. Plusieurs moyens sont mis à
     votre disposition pour faciliter votre recherche:
    • Tapez un mot clé qui correspond au livre (titre, matière, ISBN)
      dans le champ \"Filtrer\" en haut à droite, puis validez avec la
      touche \"Enter\".
	• Lorsque vous survolez un en-tête de colonne avec la souris, une
	  petite flèche apparait à droite. En cliquant dessus, un menu se
	  déroule avec pour dernière proposition \"Filtres\". Vous pourrez
	  ainsi choisir ce qui convient en fonction de la colonne sélectionnée.

  5. Une fois le livre trouvé, cliquez avec le bouton droite de la souris
     (ou simultanément sur \"ctrl\" + clic avec le touchpad) et faites
     \"Télécharger\". Une fois le téléchargement terminé, le livre s'ouvre
     automatiquement avec votre lecteur PDF.
     Faites menu \"Fichier\" -> \"Enregistrer sous...\" -> \"Fichier PDF...\".
     Sélectionnez un emplacement et cliquez sur \"Enregistrer\".

  6. Pour quitter l'application, cliquez sur \"Déconnexion\" en bas à droite.

N.B.: Les livres sont des fichiers assez volumineux et le temps de chargement
      peut prendre de 2 à 30 min selon votre connexion.

N'hésitez pas à nous contacter en cas de problème.

  La fondation defitech\n%s",
        'mail_sent' => "Un mail vous a été envoyé. Suivez-en les instructions pour changer votre mot de passe",
        'mail_sent_create' => "Le mail a bien été envoyé.",
        'mail_notsent_butlink' => "Voici le lien généré pour %s:<br/>%s",
        'mail_error' => "Personne n'a de login ou de mail ainsi que vous l'avez écrit",
        'mail_nomail' => "Votre compte existe mais n'a pas d'email associé. Vous ne pourrez pas recevoir de notification. Veuillez contacter defitech.",

        'bg_error_upload' => "Un problème a eu lieu pendant l'upload du fond d'écran",
        'bg_error_extension' => "Seul le format JPG est accepté. Le format %s a été donné.",
        'bg_error_move' => "Impossible de déplacer le fond d'écran sur le serveur",
        'bg_error_notexist' => "Le fond d'écran [%s] n'existe pas!"
    );

}