<?php

include_once 'config.php';

$config = Library_Config::getInstance();
$user = $config->getUser();

if ($user->right <= 2) {
    if ($user->right == 1) {
?>
<h1>Niveaux de droits</h1>
<ol>
    <li>Le droit de niveau 1 est le droit <strong>super admin</strong>. Il peut tout faire, c'est formidable.</li>
    <li>Le droit de niveau 2 peut apporter des modifications/suppression/ajout à la bibliothèque entière.
        Sa seule limite est la gestion utilisateur, qui est inaccessible.</li>
    <li>Les autres droits ne permettent que d'interagir en lecture avec la bibliothèque.</li>
</ol>
<br/>

<?php } ?>
<h1>Ajout d'un PDF plus lourd que <?php echo ini_get('post_max_size'); ?></h1>
<ul>
    <li>Se connecter au FTP de <em><?php echo $_SERVER['HTTP_HOST']; ?></em></li>
    <li>
        Transférer le PDF dans le dossier contenant tous les livres, soit <strong><?php echo $config->getData()->path->pdf; ?>tmp/</strong>
        <p><em>Note: l'extension <strong>.pdf</strong> doit être en <strong>minuscule</strong> pour que le fichier soit reconnu.</em></p>
    </li>
    <li>Se connecter à l'interface BND</li>
    <li>Cliquer sur la <em>flèche droite</em> du bouton <strong>Ajouter</strong></li>
    <li>Sélectionner <strong>Vérifier s'il y a de nouveaux livres PDF</strong></li>
</ul>
<p>Les fichiers seront alors intégrés au système. Ensuite, pour les retrouver, il suffira de chercher
le mot-clé <strong>new</strong> dans la barre de filtre.</p>
<p><em>Ne pas oublier d'enlever le mot-clé <strong>new</strong> une fois le livre édité.</em></p>

<br/>
<h1>Importation via CSV</h1>
<ul>
    <li>Se connecter au FTP de <em><?php echo $_SERVER['HTTP_HOST']; ?></em></li>
    <li>Transférer les PDF dans leur dossier correspondant, soit quelque part dans l'arborescence
    <strong><?php echo $config->getData()->path->pdf; ?></strong></li>
</ul>
<ul>
    <li>
        Créer un fichier CSV de la forme suivante:
        <ul>
            <li><strong>Séparateur:</strong> virgule, et guillemets pour entourer les champs</li>
            <li><strong>ligne 1:</strong> doit être la ligne des entêtes</li>
            <li><strong>colonne A:</strong> matière (string) ex: Français | Anglais | etc.</li>
            <li><strong>colonne B:</strong> éditeur (string) ex: Hachette | Payot | etc.</li>
            <li><strong>colonne C:</strong> titre (string) ex: le titre du livre</li>
            <li><strong>colonne D:</strong> niveau (separated-string) ex: 1ère-2e | 7e-8e-9e | 5e | etc.</li>
            <li><strong>colonne E:</strong> isbn (string) ex: le numéro ISBN du livre</li>
            <li><strong>colonne F:</strong> chemin vers le fichier PDF, depuis le dossier racine des livres (path) ex: Anglais/5e | Allemand | etc.</li>
            <li><strong>colonne G:</strong> nom du fichier PDF avec l'extension (string) ex: fichier_livre.pdf | etc.</li>
        </ul>
    </li>
    <li>Bien s'assurer pour chaque ligne que:
        <ol>
            <li>Le nom du fichier correspond bien à l'un de ceux transféré précédemment</li>
            <li>Le fichier est bien transféré dans le bon sous-dossier (Anglais, Maths, ou autre)</li>
        </ol>
    </li>
    <li>Se connecter à l'interface BND</li>
    <li>Cliquer sur le bouton <strong>Importation CSV</strong></li>
    <li>Sélectionner le fichier CSV fraichement créé</li>
    <li>Attendre... et voilà</li>
</ul>
<p>A noter que par moment, la connexion peut planter (HTTP error Aborted). Le système tient
compte de ça et renvoie une nouvelle requête droit derrière. Cette erreur est causée par un PDF
dont la génération de la miniature pose problème. Il sera alors facile de retrouver le(s)
élément(s) qui a(ont) posé problème en cherchant les miniatures cassées ou inexistantes.</p>
<p><em>Note: le système essaiera 3x de refaire l'importation qui a échouée, à la suite de quoi
    un message d'erreur sera affiché.</em></p>
<br/>
<?php
}
?>
<h1>Raccourcis clavier</h1>
<ul>
    <li><strong>ENTER</strong> : Afficher les informations</li>
    <li><strong>Droite</strong> : Aller à la page suivante</li>
    <li><strong>Gauche</strong> : Aller à la page précédente</li>
    <li><strong>HOME</strong> : Aller à la première ligne</li>
    <li><strong>END</strong> : Aller à la dernière ligne</li>
</ul>
<ul>
    <?php if ($user->right == 1) { ?>
    <li><strong>Shift + D</strong> : Supprimer</li>
    <li><strong>Shift + N</strong> : Nouveau</li>
    <?php } ?>
    <li><strong>Shift + F</strong> : Mettre le curseur dans le filtre de recherche</li>
    <li><strong>Shift + S</strong> : Télécharger</li>
    <li><strong>Shift + ENTER</strong> : Afficher le menu contextuel</li>
    <li><strong>Shift + Droite</strong> : Aller à la dernière page</li>
    <li><strong>Shift + Gauche</strong> : Aller à la première page</li>
</ul>
<ul>
    <li><strong>Shift + Alt + E</strong> : Filtre par "même éditeur"</li>
    <li><strong>Shift + Alt + M</strong> : Filtre par "même matière"</li>
    <li><strong>Shift + Alt + N</strong> : Filtre par "même niveau"</li>
    <li><strong>Shift + Alt + U</strong> : Annuler tous les filtres</li>
</ul>
