<?php

class Library_Controller {
    
    /**
     * --------------------------------------------------------------
     *              Binz de gestion de contrôleur
     * --------------------------------------------------------------
     */

    /**
     * Les paramètres reçus par le contrôleur
     * @var array
     */
    private $params;

    public static function output($params) {
        $controller = new self($params);
        try {
            $data = $controller->action();
            // on encode/stripslashes ou autre toutes les string
            function recurse(&$tab) {
                foreach ($tab as $key => $val) {
                    if (is_array($val)) $tab[$key] = recurse($val);
                    if (is_string($val)) {
                        $tab[$key] = stripslashes($val);
                    }
                }
                return $tab;
            }
            return recurse($data);
        } catch (Exception $e) {
            return array(
                'success' => false,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            );
        }
    }

    private function  __construct($params) {
        $this->params = $params;
    }

    public function hasParam($param) {
        return isset($this->params[$param]);
    }

    public function getParam($param, $default = 'FjhOh83hoo3') {
        if ($this->hasParam($param)) {
            return $this->params[$param];
        } elseif ($default !== 'FjhOh83hoo3') {
            return $default;
        }
        throw new Exception('Le paramètre ' . $param . " n'existe pas.");
    }

    public function action() {
        $cmd = $this->getParam('cmd');
        if (!method_exists($this, $cmd)) {
            throw new Exception('Paramètre ' . $cmd . ' inconnu');
        }
        return $this->$cmd();
    }

    public function getGroupParam($paramPrefix, $separator = '-') {
        $params = array();
        foreach ($this->params as $key => $val) {
            if (strpos($key, $paramPrefix) !== false) {
                $params[] = array_pop(explode($separator, $key));
            }
        }
        return $params;
    }







    /**
     * --------------------------------------------------------------
     *              Méthodes métier pour les livres
     * --------------------------------------------------------------
     */

    protected function getBookList() {
        // récupération des paramètres
        $sort = $this->getParam('sort', 'title');
        $dir = $this->getParam('dir', 'ASC');
        $start = $this->getParam('start', 0);
        $limit = $this->getParam('limit');
        $filters = $this->getParam('filters', array());
        $gridFilters = $this->getParam('filter', array());

        // création de la requête
        $table = new Library_Book();
        $select = $table
            ->select()
            ->order(array($sort . ' ' . $dir, 'title ASC'))
            ->limit($limit, $start);

        // ajout des filtres non-grid s'il y en a
        if (isset($filters['fullsearch'])) {
            // définition des champs touchés par le fullsearch
            $fullsearch_fields = array('title', 'tags');
            // création du bout de requête sql
            $tmp = array();
            foreach ($fullsearch_fields as $field) {
                $tmp[] = $field . ' LIKE "%' . $filters['fullsearch'] . '%"';
            }
            $select->where('(' . implode(' OR ', $tmp) . ')');
        }

        //ajout des filtres grid s'il y en a
        foreach ($gridFilters as $filter) {
            switch ($filter['data']['type']) {
                case 'string':
                    $select->where($filter['field'] . ' LIKE "%' . $filter['data']['value'] . '%"');
                    break;
                case 'list':
                    if  ($filter['field'] == 'niveau_id') {

                    } else {
                        $select->where($filter['field'] . ' IN (?)', explode(',', $filter['data']['value']));
                    }
                    break;
            }
        }


        // récupération des tuples
        $rows = $table->fetchAll($select);

        // création du tableau d'items à balancer à la grid extjs
        $books = array();
        $types = Library_Book_Type::getListToArray();
        $editors = Library_Book_Editor::getListToArray();
        foreach ($rows as $row) {
            $niveaux = $row->findManyToManyRowset('Library_Niveau', 'Library_Book_Niveau');
            $ns = array();
            foreach ($niveaux as $niveau) {
                $ns[] = $niveau->label;
            }
            $books[] = array_merge($row->toArray(), array(
                'editor_id' => isset($editors[$row->editor_id]) ? $editors[$row->editor_id] : $row->editor_id,
                'type_id' => isset($types[$row->type_id]) ? $types[$row->type_id] : $row->type_id,
                'thumb' => $row->thumb ? $row->thumb : 'resources/images/empty.jpg',
                'niveau_id' => implode(', ', $ns)
            ));
        }

        // récupération de la totalité des livres
        $select
            ->reset(Zend_Db_Select::LIMIT_COUNT)
            ->reset(Zend_Db_Select::LIMIT_OFFSET);

        $rows = $table->fetchAll($select);

        return array(
            'success' => true,
            'total' => $rows->count(),
            'books' => $books
        );
    }

    protected function getBook() {
        $table = new Library_Book();
        $id = $this->getParam('id');

        if ($id) {
            $book = $table->fetchRow($table
                ->select()
                ->where('id = ?', $id)
            );
        } else {
            $book = $table->createRow();
        }

        // récupération de tous les niveaux
        $niveaux_all = Library_Niveau::getListToArray();
        // récupération des niveaux liés au livre
        $table_link = new Library_Book_Niveau();
        $niveaux_tmp = $book->findManyToManyRowset('Library_Niveau', 'Library_Book_Niveau');
        $niveaux_select = array();
        foreach ($niveaux_tmp as $n) {
            $niveaux_select[$n->id] = $n->id;
        }
        $niveaux = array();
        foreach ($niveaux_all as $i => $n) {
            // création du tableau pour le set de checkbox
            $niveaux[] = array(
                'boxLabel' => $n,
                'checked' => isset($niveaux_select[$i]) ? true : false,
                'name' => 'niveau-' . $i
            );
        }

        return array(
            'success' => true,
            'data' => array_merge($book->toArray(), array(
                'editors' => $this->getEditorList(),
                'types' => $this->getTypeList(),
                'niveaux' => $niveaux
            ))
        );
    }

    protected function saveBook() {
        // set des paramètres PHP pour favoriser l'upload au mieux
        ini_set('max_execution_time', 120);
        ini_set('memory_limit', '128M');
        // fin

        $table = new Library_Book();
        $id = $this->getParam('id');
        $success = true;
        $msg = '';
        $log = array();

        if ($id) {
            $row = $table->fetchRow($table
                ->select()
                ->where('id = ?', $id)
            );
        } else {
            $row = $table->createRow();
        }

        $row->title = stripslashes($this->getParam('title'));
        $row->isbn = $this->getParam('isbn');
        $row->thumb = $this->getParam('thumb');
        $row->filename = $this->getParam('pdf');
        $row->editor_id = $this->getParam('editor_id');
        $row->type_id = $this->getParam('type_id');

        $config = Library_Config::getInstance();

        // si un thumb est envoyé en fichier
        $valid_extensions = array('image/jpeg', 'image/jpg', 'image/png', 'image/gif');
        $thumb = $_FILES['thumbfile'];
        if ($thumb['error'] == UPLOAD_ERR_OK) {
            if (in_array($thumb['type'], $valid_extensions)) {
                $img = Library_Util::getSlug($row->title) . strrchr($thumb['name'], '.');
                move_uploaded_file($thumb['tmp_name'], Library_Book::getThumbPath(true) . $img);
                // set du nouveau thumb
                $row->thumb = Library_Book::getThumbFolder() . $img;
            } else {
                $success = false;
                $msg = "Mauvais type de fichier pour l'aperçu";
            }
        }

        // si un pdf est envoyé en fichier
        $pdf = $_FILES['pdffile'];
        if ($pdf['error'] == UPLOAD_ERR_OK) {
            if ($pdf['type'] == 'application/pdf') {
                $path = $config->getData()->path->pdf . 'upload/';
                if (!is_dir($path)) {
                    mkdir($path, 0766);
                }
                $p = Library_Util::getSlug($row->title) . '.pdf';
                $i = Library_Util::getSlug($row->title) . '.jpg';
                move_uploaded_file($pdf['tmp_name'], $path . $p);
                $output = $this->generatePdfFirstPageThumb($path . $p, Library_Book::getThumbPath(true). $i);
                $log[] = $output;
                // set du nouveau pdf et de son thumb
                $row->thumb = Library_Book::getThumbFolder() . $i;
                $row->filename = 'upload/' . $p;
            } else {
                $success = false;
                $msg = "Mauvais type de fichier pour le PDF";
            }
        }

        $row->save();

        // set des niveaux
        $niveaux = $this->getGroupParam('niveau');
        $table_link = new Library_Book_Niveau();
        $table_link->delete($table_link->getAdapter()->quoteInto('book_id IN(?)', $row->id));
        foreach ($niveaux as $niveau) {
            $table_link->insert(array(
                'book_id' => $row->id,
                'niveau_id' => $niveau
            ));
        }
        
        return array(
            'success' => $success,
            'msg' => $msg,
            'log' => $log
        );
    }

    protected function removeBook() {
        Library_Config::getInstance()->testIssetAuser();
        $ids = $this->getParam('ids');
        $table = new Library_Book();
        $table->delete($table->getAdapter()->quoteInto('id IN(?)', $ids));

        return array(
            'success' => true,
            'nb' => count($ids)
        );
    }

    private function generatePdfFirstPageThumb($pdf, $img) {
        $path_convert = Library_Config::getInstance()->getData()->path->convert;

        $cmd = $path_convert . "convert '" . $pdf . "[0]' '" . $img . "'";
        $output = array($cmd);
        exec($cmd, $output);

        return $output;
    }





    /**
     * --------------------------------------------------------------
     *              Méthodes de gestion de connexion
     * --------------------------------------------------------------
     */


    protected function login() {
        $pass = md5($this->getParam('pass'));
        $user = new Library_User();
        $result = $user->fetchRow($user->select()
            ->where('login = ?', $this->getParam('login'))
            ->where('pass = ?', $pass)
        );
        
        if ($result) {
            $session = new Zend_Session_Namespace('Library');
            $session->login = $this->getParam('login');
            $session->pass = $pass;

            // on enregistre la date de dernière connection
            $result->last_connected = date('u');
            $result->save();
            
            return array(
                'success' => true
            );
        }

        return array(
            'success' => false,
            'error' => 'Mauvais couple login/pass'
        );
    }

    protected function logout() {
        $session = new Zend_Session_Namespace('Library');
        if (isset($session->login)) {
            unset($session->login);
        }
        if (isset($session->pass)) {
            unset($session->pass);
        }
        return array(
            'success' => true
        );
    }





    /**
     * --------------------------------------------------------------
     *              Méthodes pour les matières
     * --------------------------------------------------------------
     */

    protected function getTypeList() {
        $rowset = Library_Book_Type::getList();
        $data = array();
        foreach ($rowset as $row) {
            $data[] = array(
                'id' => $row->id,
                'text' => $row->label
            );
        }

        return array(
            'success' => true,
            'items' => $data
        );
    }

    protected function addType() {
        $table = new Library_Book_Type();
        $table->insert(array(
            'label' => $this->getParam('text')
        ));
        return array(
            'success' => true,
            'id' => $table->getAdapter()->lastInsertId()
        );
    }




    /**
     * --------------------------------------------------------------
     *              Méthodes pour les éditeurs
     * --------------------------------------------------------------
     */

    protected function getEditorList() {
        $rowset = Library_Book_Editor::getList();
        $data = array();
        foreach ($rowset as $row) {
            $data[] = array(
                'id' => $row->id,
                'text' => $row->editor
            );
        }

        return array(
            'success' => true,
            'items' => $data
        );
    }

    protected function addEditor() {
        $table = new Library_Book_Editor();
        $table->insert(array(
            'editor' => $this->getParam('text')
        ));
        return array(
            'success' => true,
            'id' => $table->getAdapter()->lastInsertId()
        );
    }





    /**
     * --------------------------------------------------------------
     *              Méthodes pour les niveaux
     * --------------------------------------------------------------
     */

    protected function getNiveauList() {
        $rowset = Library_Niveau::getList();
        $data = array();
        foreach ($rowset as $row) {
            $data[] = array(
                'id' => $row->id,
                'text' => $row->label
            );
        }

        return array(
            'success' => true,
            'items' => $data
        );
    }

    protected function addNiveau() {
        $table = new Library_Niveau();
        $table->insert(array(
            'label' => $this->getParam('text')
        ));
        return array(
            'success' => true,
            'id' => $table->getAdapter()->lastInsertId()
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
     * Fonction d'importation d'un fichier CSV
     *
     * @return array
     */
    protected function import() {
        // set des paramètres PHP pour favoriser l'upload au mieux
        ini_set('max_execution_time', 120);
        ini_set('memory_limit', '128M');
        // fin

        $file = $_FILES['csv'];
        $log = array();
        // on check si on peut ouvrir ce fichier uploadé
        if ($file['error'] == UPLOAD_ERR_OK && $file['type'] == 'text/csv' && ($handle = fopen($file['tmp_name'], 'r')) !== false) {
            $table = new Library_Book();
            $lines = 0;
            $continue = true;
            $this->importTypes = Library_Book_Type::getListToArray();
            $this->importEditors = Library_Book_Editor::getListToArray();
            $this->importNiveaux = Library_Niveau::getListToArray();
            while (($data = fgetcsv($handle)) !== false) {
                // on skip la 1ère ligne (ligne de titre) ainsi que les lignes
                // éventuellement vides
                if ($continue || !trim($data[0])) {
                    $continue = false;
                    continue;
                }
                $info = $this->makeDataFromImportLine($data);
                // check si le pdf de ce livre existe
                if (file_exists($info['pathpdf'])) {
                    if (!file_exists($info['pathimg'])) {
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
                $lines++;
                $log[] = $info;
            }
            fclose($handle);
            return array(
                'success' => true,
                'lines' => $lines,
                'log' => $log
            );
        }
        return array(
            'success' => false,
            'error' => 'Mouvais fichier fourni. Il doit s agir d un CSV'
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
        $niveaux = array_map('trim', explode('-', $csv['niveau']));
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