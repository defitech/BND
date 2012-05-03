<?php

include_once 'config.php';

$config = Library_Config::getInstance();

if (Library_User::right(2)) {
    if (Library_User::right(1)) {
?>
<h1>Niveaux de droits</h1>
<ol>
    <li>Le droit de niveau 1 est le droit <strong>super admin</strong>. Il peut tout faire, c'est formidable.</li>
    <li>Le droit de niveau 2 peut apporter des modifications/suppression/ajout à la bibliothèque entière.
        Sa seule limite est la gestion utilisateur, qui est inaccessible.</li>
    <li>Les autres droits ne permettent que d'interagir en lecture avec la bibliothèque.</li>
</ol>
<br/>
<h1>Visualisation des logs</h1>
<p>Un fichier de logs mensuel est généré dans <strong><?php echo $config->getData()->path->log; ?></strong>.
Ils sont nommés <em>logs_YYYY-MM.txt</em> et contiennent des informations de debug, notamment sur la génération des thumbs.</p>
<br/>
<?php
    } // Fin If user->right == 1
?>
<h1>Ajout d'un PDF plus lourd que <?php echo $config->getMaxPostSize(); ?></h1>
<ul>
    <li>Se connecter au FTP de <em><?php echo $_SERVER['HTTP_HOST']; ?></em></li>
    <li>
        Transférer le(s) PDF dans le dossier temporaire du dossier contenant tous les livres, soit <strong><?php echo Library_Book::getTmpPdfPath(true); ?></strong>
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
            <li><strong>séparateur:</strong> virgule, et guillemets pour entourer les champs</li>
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
            <li>Le nom du fichier ne contient pas de caractères spéciaux (une apostrophe, un espace, un accent, etc.).
                Les tirets et les soulignés peuvent être utilisés.</li>
        </ol>
    </li>
    <li>Se connecter à l'interface BND</li>
    <li>Cliquer sur la <em>flèche droite</em> du bouton <strong>Ajouter</strong></li>
    <li>Sélectionner <strong>Importer CSV</strong></li>
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
<h1>Emplacement des images</h1>
<p>Pour chaque PDF se crée, normalement, une image et une mini image. Celles-ci sont
    sauvegardées dans le dossier <strong><?php echo Library_Book::getThumbPath(true); ?></strong>.</p>
<br/>
<h1>Backup de la base de données</h1>
<p>Afin de générer un backup (il n'y a pas de cron job pour ça), il y a deux méthodes:</p>
<ul>
    <li>(recommandé) Cliquer sur la <em>flèche droite</em> du bouton <em>Ajouter</em> et sélectionner
        <strong>Vérifier s'il y a de nouveaux livres PDF</strong>, même s'il n'y en a pas.</li>
    <li>Importer un fichier CSV vide (s'il n'est pas vide, le backup se fera quand même)</li>
</ul>
<br/>
<h1>FAQ</h1>
<ul>
    <li><em>Quelqu'un se plaint de ne pas trouver un livre, alors que l'admin constate qu'il
    est bien là</em>:<ul><li> C'est très probablement qu'il y a des droits qui ont été mis sur le
    livre, accidentellement ou non. Pour résoudre ce cas, il faudra soit décocher tous les
    droits particuliers (du coup tout le monde pourra voir le livre), soit ajouter le plaignant
    dans les droits particuliers.</li></ul></li>
</ul>
<?php
} // Fin if user->right <= 2
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
    <?php if (Library_User::right(2)) { ?>
    <li><strong>Shift + D</strong> : Supprimer</li>
    <li><strong>Shift + N</strong> : Nouveau</li>
    <?php } ?>
    <li><strong>Shift + F</strong> : Mettre le curseur dans le filtre de recherche</li>
    <li><strong>Shift + S</strong> : Télécharger</li>
    <li><strong>Shift + ENTER</strong> : Afficher le menu contextuel</li>
    <li><strong>Shift + Droite</strong> : Aller à la dernière page</li>
    <li><strong>Shift + Gauche</strong> : Aller à la première page</li>
</ul>
<ul
    <li><strong>Shift + Alt + M</strong> : Filtre par "même matière"</li>
    <li><strong>Shift + Alt + E</strong> : Filtre par "même éditeur"</li>
    <li><strong>Shift + Alt + N</strong> : Filtre par "même niveau"</li>
    <li><strong>Shift + Alt + U</strong> : Annuler tous les filtres</li>
</ul>
