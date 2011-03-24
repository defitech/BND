<?php

class Library_Book_ImportController extends Library_Controller {




    /**
     * --------------------------------------------------------------
     *              Méthodes pour l'importation CSV
     * --------------------------------------------------------------
     */

    /**
     * Tableau des matières existantes. Utilisé lors de l'import
     * @var array
     */
    private $importTypes = array();

    /**
     * Tableau des éditeurs existants. Utilisé lors de l'import
     * @var array
     */
    private $importEditors = array();

    /**
     * Tableau des niveaux (classes, degrés) existants
     * @var array
     */
    private $importNiveaux = array();

    /**
     * Liste des slugs de tous les livres présents, pour ne pas insérer de doublons
     * @var array
     */
    private $importBooks = array();

    /**
     * Le séparateur des niveaux dans le fichier Excel
     * @var string
     */
    private $niveauxSeparator = '-';

    /**
     * Fonction d'importation d'un fichier CSV. Le fichier CSV doit avoir cette tête:
     *
     * - ligne   1: doit être la ligne des entêtes
     *
     * - colonne A: matière (string) ex: Français | Anglais | etc.
     * - colonne B: éditeur (string) ex: Hachette | Payot | etc.
     * - colonne C: titre (string) ex: le titre du livre
     * - colonne D: niveau (separated-string) ex: 1ère-2e | 7e-8e-9e | 5e | etc.
     * - colonne E: isbn (string) ex: le numéro ISBN du livre
     * - colonne F: chemin vers le fichier PDF, depuis le dossier racine des livres (path) ex: Anglais/5e/ | Allemand/ | etc.
     * - colonne G: nom du fichier PDF avec l'extension (string) ex: fichier_livre.pdf | etc.
     *
     * @return array
     */
    protected function import() {
        Library_Config::getInstance()->testIssetAuser();

        $file = $_FILES['csv'];
        // on check si on peut ouvrir ce fichier uploadé
        $types = array('text/csv', 'application/csv');
        if ($file['error'] == UPLOAD_ERR_OK && in_array($file['type'], $types)) {
            // backup de la bd
            Library_Util::backupDb();

            // sauvegarde du csv quelque part pour réutilisation ultérieure
            $path = Library_Config::getInstance()->getData()->path->log;
            move_uploaded_file($file['tmp_name'], $path . 'import.csv');

            return array(
                'success' => true
            );
        }
        return array(
            'success' => false,
            'error' => Library_Wording::get('bad_csv_type')
        );
    }

    protected function importSegment() {
        Library_Config::getInstance()->testIssetAuser();
        // set des paramètres PHP pour favoriser l'upload au mieux
        ini_set('max_execution_time', 120);
        ini_set('memory_limit', '128M');

        $skip_thumb = $this->getParam('skipThumb', false);

        $file = Library_Config::getInstance()->getData()->path->log . 'import.csv';
        $log = array();
        // on check si on peut ouvrir ce fichier uploadé
        if (($handle = fopen($file, 'r')) !== false) {
            $table = new Library_Book();
            $lines = 0;
            $start = $this->getParam('start') + 1;
            $continue = true;
            $this->importTypes = Library_Book_Type::getListToArray();
            $this->importEditors = Library_Book_Editor::getListToArray();
            $this->importNiveaux = Library_Niveau::getListToArray();
            $this->importBooks = Library_Book::getListForImportDistinct();
            while (($data = fgetcsv($handle)) !== false) {
                // on skip la 1ère ligne (ligne de titre)
                if ($continue) {
                    $continue = false;
                    continue;
                }

                // on skip les lignes éventuellement vides
                $info = $this->makeDataFromImportLine($data);
                if (!$info) {
                    continue;
                }

                // on ne s'occupe que de la ligne voulue
                $lines++;
                if ($start != $lines) continue;

                // si le livre existe déjà dans la base, on shop
                if (in_array(Library_Util::getSlug($info['titre']), $this->importBooks['slug'])) {
                    continue;
                }

                // check si le pdf de ce livre existe
                if (file_exists($info['pathpdf'])) {
                    if (!file_exists($info['pathimg']) && !$skip_thumb) {
                        $output = $this->generatePdfFirstPageThumb($info['pathpdf'], $info['pathimg']);
                        $log[] = $output;
                    }
                } else {
                    $info['thumb'] = null;
                }
                // on insère le nouveau livre
                $table->insert(array(
                    'title' => $info['titre'],
                    'isbn' => $info['isbn'],
                    'thumb' => $info['thumb'],
                    'filename' => $info['filename'],
                    'tags' => $info['tags'],
                    'editor_id' => $info['editor_id'],
                    'type_id' => $info['type_id']
                ));
                $id = $table->getAdapter()->lastInsertId();
                $table_link = new Library_Book_Niveau();
                foreach ($info['niveaux'] as $niveau) {
                    $table_link->insert(array(
                        'book_id' => $id,
                        'niveau_id' => $niveau
                    ));
                }
                $log[] = $info;
            }
            fclose($handle);
            return array(
                'success' => true,
                'total' => $lines,
                'next' => $start < $lines,
                'log' => $log
            );
        }
        return array(
            'success' => false,
            'error' => Library_Wording::get('bad_csv_type')
        );
    }

    /**
     * Méthode qui met en forme une ligne du CSV en tableau plus explicite. Si
     * un jour, un champ du CSV s'ajoute ou change de place, c'est ici qu'il
     * faudra gérer la chose
     *
     * @param array $line tableau correspondant à la ligne courante du CSV
     * @return array tableau explicite mis en forme
     */
    private function makeDataFromImportLine($line) {
        $line = array_map('trim', $line);

        // si la 1ère ligne est vide, on arrête le traitement
        if (!$line[0]) {
            return false;
        }

        // données du fichier CSV
        $csv = array(
            'matiere' => $line[0],
            'editeur' => $line[1],
            'titre' => $line[2],
            'niveau' => $line[3],
            'isbn' => $line[4],
            'folder' => $line[5],
            'file' => $line[6],
        );

        // données rajoutées
        $file = substr($csv['file'], 0, strrpos($csv['file'], '.'));
        $fileimage = $file . '.jpg';
        $filepdf = $file ? $csv['folder'] . '/' . $csv['file'] : '';
        return array_merge($csv, array(
            'filename' => $filepdf,
            'thumb' => $file ? Library_Book::getThumbPath() . $fileimage : null,
            'pathimg' => Library_Book::getThumbPath(true) . $fileimage,
            'pathpdf' => Library_Config::getInstance()->getData()->path->pdf . $filepdf,
            'tags' => Library_Util::getSlug($csv['titre'], ',', 2),
            'editor_id' => $this->importGetEditor($csv),
            'type_id' => $this->importGetType($csv),
            'niveaux' => $this->importGetNiveaux($csv)
        ));
    }

    private function importGetEditor($csv) {
        // est-ce que l'éditeur existe déjà? si oui, on le récupère
        $slug = Library_Util::getSlug($csv['editeur']);
        $id = null;
        foreach ($this->importEditors as $ide => $val) {
            if ($slug == Library_Util::getSlug($val)) {
                $id = $ide;
                break;
            }
        }
        // sinon, on le crée
        if (!$id && $csv['editeur']) {
            $table = new Library_Book_Editor();
            $table->insert(array(
                'editor' => $csv['editeur']
            ));
            $id = $table->getAdapter()->lastInsertId();
            $this->importEditors[$id] = $csv['editeur'];
        }
        return $id;
    }

    private function importGetType($csv) {
        // est-ce que la matière existe déjà? si oui, on la récupère
        $slug = Library_Util::getSlug($csv['matiere']);
        $id = null;
        foreach ($this->importTypes as $ide => $val) {
            if ($slug == Library_Util::getSlug($val)) {
                $id = $ide;
                break;
            }
        }
        // sinon, on le crée
        if (!$id && $csv['matiere']) {
            $table = new Library_Book_Type();
            $table->insert(array(
                'label' => $csv['matiere']
            ));
            $id = $table->getAdapter()->lastInsertId();
            $this->importTypes[$id] = $csv['matiere'];
        }
        return $id;
    }

    private function importGetNiveaux($csv) {
        $niveaux = array_map('trim', explode($this->niveauxSeparator, $csv['niveau']));
        $ns = array();
        foreach ($niveaux as $niveau) {
            // est-ce que le niveau existe déjà? si oui, on le récupère
            $slug = Library_Util::getSlug($niveau);
            $id = null;
            foreach ($this->importNiveaux as $ide => $val) {
                if ($slug == Library_Util::getSlug($val)) {
                    $ns[] = $ide;
                    $id = $ide;
                    break;
                }
            }
            // sinon, on le crée
            if (!$id && $niveau) {
                $table = new Library_Niveau();
                $table->insert(array(
                    'label' => $niveau
                ));
                $id = $table->getAdapter()->lastInsertId();
                $this->importNiveaux[$id] = $niveau;
            }
        }
        return $ns;
    }

}