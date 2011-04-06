<?php

class Library_Book_ImportController extends Library_Controller {


    /**
     * --------------------------------------------------------------
     *              Méthodes pour l'importation depuis tmp/
     * --------------------------------------------------------------
     */

    protected function checkNewBooks() {
        Library_Config::getInstance()->testIssetAuser(2);
        if ($this->getParam('start', 0) == 0) {
            Library_Util::backupDb();
        }
        $skip_thumb = $this->getParam('skipThumb');
        $stop = false;

        // on chope tous les PDF dont l'extension est en minuscule
        $files = glob(Library_Book::getTmpPdfPath(true) . '*.pdf');
        $msg = array();
        $table = new Library_Book();
        $book_controller = new Library_Book_Controller($this->getParams());
        foreach ($files as $file) {
            // on chope le slug du fichier
            $tmp = str_replace(Library_Book::getTmpPdfPath(true), '', $file);
            $title = Library_Util::getSlug($tmp);
            // on déplace ce fichier dans le dossier d'upload
            $filename = Library_Book::getUploadPdfPath(true) . $tmp;
            $success = @rename($file, $filename);
            $thumb = !$skip_thumb;
            if ($success) {
                // on essaie de générer le thumb
                if (!$skip_thumb) {
                    $output = $this->generatePdfFirstPageThumb($filename, Library_Book::getThumbPath(true). $tmp . '.jpg');
                    if (count($output) != 1) {
                        $success = false;
                        $thumb = false;
                    } else {
                        $book_controller->resizeThumbAndCreateMini($tmp . '.jpg');
                    }
                }
                // on crée l'entrée dans la base
                $table->insert(array(
                    'title' => $title,
                    'thumb' => $thumb ? Library_Book::getThumbFolder() . $tmp . '.jpg' : '',
                    'filename' => Library_Book::getUploadPdfFolder() . $tmp,
                    'tags' => Library_Util::getSlug($title, ',') . ',new'
                ));
            }
            // sécurité. Si la copie ne s'est pas bien passée, on stoppe le
            // processus coté javascript
            $stop = !$success;

            $msg[] = array(
                'title' => $title,
                'file' => $tmp,
                'success' => $success,
                'thumb' => $thumb
            );
            // on en traite qu'un seul à la fois. Le flux est géré dans le js
            break;
        }

        return array(
            'success' => true,
            'total' => count($files),
            'next' => count($files) > 1,
            'data' => $msg,
            'stop' => $stop
        );
    }


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
     * Fonction d'importation d'un fichier CSV
     *
     * @return array
     */
    protected function import() {
        Library_Config::getInstance()->testIssetAuser(2);

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
        Library_Config::getInstance()->testIssetAuser(2);
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
            $book_controller = new Library_Book_Controller($this->getParams());
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

                // il la méthode makeDataFromImportLine signale qu'il faut
                // appeler continue, on le fait. C'est pour pouvoir augmenter
                // $lines tout en ne faisant pas la suite (cas des doublons)
                if (isset($info['countButContinue']) && $info['countButContinue']) {
                    continue;
                }

                // check si le pdf de ce livre existe
                $thumb_exists = file_exists($info['pathimg']);
                if (file_exists($info['pathpdf'])) {
                    if (!$thumb_exists && !$skip_thumb) {
                        $output = $this->generatePdfFirstPageThumb($info['pathpdf'], $info['pathimg']);
                        if (count($output) == 1) {
                            $book_controller->resizeThumbAndCreateMini($info['fileimage']);
                        }
                        $log[] = $output;
                    }
                } else {
                    $info['thumb'] = $thumb_exists ? $info['thumb'] : null;
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
                $this->importBooks['slug'][] = Library_Util::getSlug($info['titre']);
                $this->importBooks['filename'][] = Library_Util::getSlug($info['filename']);
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
        if (!$line[0]) return false;

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

        if (!$csv['titre']) return false;

        // si le livre existe déjà dans la base, on skip
        if (in_array(Library_Util::getSlug($csv['titre']), $this->importBooks['slug'])) {
            return array('countButContinue' => true);
        }

        // données rajoutées
        $file = substr($csv['file'], 0, strrpos($csv['file'], '.'));
        $fileimage = $file . '.jpg';
        $filepdf = $file ? $csv['folder'] . '/' . $csv['file'] : '';
        return array_merge($csv, array(
            'filename' => $filepdf,
            'fileimage' => $fileimage,
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