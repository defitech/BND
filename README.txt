------------------------------------------------------
Bugs connus
------------------------------------------------------
- Lorsqu'on crée une nouvelle fiche, on sélectionne une matière puis on upload
  un PDF. Or, le PDF s'upload systématiquement dans le dossier /upload/ et pas
  dans le dossier de la matière choisie. On peut toujours remettre de l'ordre
  en lançant le script qu'on trouve dans l'interface, via le bouton:
  "Ajouter > Déplacer les PDFs dans les bons sous-dossiers"

- NOM DE LA MATIÈRE: un dossier sur le serveur se crée pour chaque nouvelle
  matière insérée. Or, si la matière contient des catactères spéciaux du genre
  espaces ou virgules, le download et l'upload ne fonctionneront pas! Pour
  pallier ce problème, il y a le fichier config/config.ini qui permet de faire
  une correspondance entre la matière telle qu'elle existe dans la BD et le
  nom du dossier.

- on ne peut pas enregistrer du 1er coup une fiche sans avoir un PDF


------------------------------------------------------
Configuration après checkout du dépôt git:
------------------------------------------------------

1. vérifier l'existence du dossier config/
2. vérifier l'existence du dossier resources/background/
3. copier config/config_default.ini et nommer le fichier config/config.ini,
   et y inscrire les bonnes informations 
4. créer un fichier config/background.txt avec le nom d'un JPG existant dans
   resources/background/
5. vérifier que l'écriture est possible pour config/background.txt
6. vérifier que l'écriture est possible dans resources/background/
7. vérifier que l'écriture est possible dans les dossiers et sous-dossiers des
   livres



------------------------------------------------------
Configuration avancées sur un poste local:
------------------------------------------------------

Installer ImageMagick, pour avoir la fonction convert (pour extraire la 1ère
image d'un PDF). Pour faire simple sous Mac OSX, installer Xcode puis MacPorts
et faire:
$ sudo port install ImageMagick

Dans la configuration APACHE (en local pour le dev), ajouter les lignes
suivantes dans les alias, si on veut taper dans les livres sur le Synology:

Alias /livres/  "/chemin/complet/vers/les/livres/" 
<Directory "/chemin/complet/vers/les/livres/"> 
    Options Indexes MultiViews 
    AllowOverride None 
    Order allow,deny 
    Allow from all 
</Directory>
 
# /livres/ = l'alias qu'on retrouve dans web.pdf du config.ini
# /chemin/complet/vers/les/livres/ = le path.pdf du config.ini



------------------------------------------------------
A envisager de faire un jour:
------------------------------------------------------

- boutons "précédent" et "suivant" dans la popup livre, pour naviguer d'un
  livre à l'autre sans avoir à passer par la grid
- ramener le focus sur la grid après chaque action (avec raccourcis clavier)
- la largeur du champ titre (grid des livres) se stoppe à ~1200px

Raccourcis clavier sur fenêtre d'information à faire:
- Shift+Q: ferme la fenêtre
- Shift+S: télécharger

Reflexions:
- filtres pour la grid http://triin.net/temp/filter-row/ ?
- téléchargement sécurisé? par php? ou par http? htaccess?
