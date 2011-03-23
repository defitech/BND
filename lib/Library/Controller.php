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
            return $data ? recurse($data) : array('success' => false, 'error' => $data);
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
        throw new Exception(sprintf(Library_Wording::get('param_doesnt_exists', $params)));
    }

    public function action() {
        $cmd = $this->getParam('cmd');
        if (!method_exists($this, $cmd)) {
            throw new Exception(sprintf(Library_Wording::get('param_cmd_unknow'), $cmd));
        }
        return $this->$cmd();
    }

    public function getGroupParam($paramPrefix, $separator = '-') {
        $params = array();
        foreach ($this->params as $key => $val) {
            if (strpos($key, $paramPrefix) !== false) {
                $k = array_pop(explode($separator, $key));
                $params[$k] = $val;
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
        Library_Config::getInstance()->testIssetAuser();
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
            ->select(true)
            ->distinct()
            ->joinLeft(array('nb' => 'library_book_niveau'), 'library_book.id = nb.book_id', array())
            ->joinLeft(array('n' => 'library_niveau'), 'n.id = nb.niveau_id', array())
            ->joinLeft(array('e' => 'library_book_editor'), 'library_book.editor_id = e.id', array())
            ->joinLeft(array('t' => 'library_book_type'), 'library_book.type_id = t.id', array())
            ->limit($limit, $start);

        switch($sort) {
            case 'editor_id': $select->order('e.editor ' . $dir); break;
            case 'type_id': $select->order('t.label ' . $dir); break;
            case 'niveau_id': $select->order('n.label ' . $dir); break;
            default: $select->order('library_book.' . $sort . ' ' . $dir);
        }
        $select->order('library_book.title ASC');

        // ajout des filtres non-grid s'il y en a
        if (isset($filters['fullsearch'])) {
            // définition des champs touchés par le fullsearch
            $fullsearch_fields = array(
                'library_book.title', 'library_book.tags', 'library_book.isbn',
                'e.editor',
                't.label',
                'n.label'
            );
            // création du bout de requête sql
            $tmp = array();
            foreach ($fullsearch_fields as $field) {
                $tmp[] = $field . ' LIKE "%' . $filters['fullsearch'] . '%"';
            }
            $select->where('(' . implode(' OR ', $tmp) . ')');
        }

        // ajout des filtres grid s'il y en a
        foreach ($gridFilters as $filter) {
            switch ($filter['data']['type']) {
                case 'string':
                    $select->where('library_book.' . $filter['field'] . ' LIKE "%' . $filter['data']['value'] . '%"');
                    break;
                case 'list':
                    switch  ($filter['field']) {
                        case 'niveau_id': $tb = 'nb'; break;
                        default: $tb = 'library_book';
                    }
                    $select->where($tb . '.' . $filter['field'] . ' IN (?)', explode(',', $filter['data']['value']));
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
            $ns = array('label' => array(), 'id' => array());
            foreach ($niveaux as $niveau) {
                $ns['label'][] = $niveau->label;
                $ns['id'][] = $niveau->id;
            }
            $books[] = array_merge($row->toArray(), array(
                'editorid' => $row->editor_id,
                'editor_id' => isset($editors[$row->editor_id]) ? $editors[$row->editor_id] : $row->editor_id,
                'typeid' => $row->type_id,
                'type_id' => isset($types[$row->type_id]) ? $types[$row->type_id] : $row->type_id,
                'thumb' => $row->thumb ? $row->thumb : 'resources/images/empty.jpg',
                'niveauid' => implode(',', $ns['id']),
                'niveau_id' => implode(', ', $ns['label'])
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
        Library_Config::getInstance()->testIssetAuser();
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
        $niveaux = $this->getNiveauListForCheckboxGroup($book);

        return array(
            'success' => true,
            'data' => array_merge($book->toArray(), array(
                'editors' => $this->getEditorList(),
                'types' => $this->getTypeList(),
                'niveaux' => $niveaux,
                'maxpostsize' => ini_get('post_max_size')
            ))
        );
    }

    protected function saveBook() {
        Library_Config::getInstance()->testIssetAuser();
        // set des paramètres PHP pour favoriser l'upload au mieux
        ini_set('max_execution_time', 120);
        ini_set('memory_limit', '128M');

        $table = new Library_Book();
        $id = $this->getParam('id');
        $success = true;
        $msg = '';

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
                $msg = Library_Wording::get('bad_thumb_type')
                    . '. ' . Library_Wording::get('book_still_save');
            }
        }

        // si un pdf est envoyé en fichier
        $pdf = $_FILES['pdffile'];
        if ($pdf['error'] == UPLOAD_ERR_OK) {
            if ($pdf['type'] == 'application/pdf' || $pdf['type'] == 'application/download') {
                $path = Library_Book::getUploadPdfPath(true);
                if (!is_dir($path)) {
                    mkdir($path, 0766);
                }
                $p = Library_Util::getSlug($row->title) . '.pdf';
                $i = Library_Util::getSlug($row->title) . '.jpg';
                move_uploaded_file($pdf['tmp_name'], $path . $p);
                $output = $this->generatePdfFirstPageThumb($path . $p, Library_Book::getThumbPath(true). $i);
                if (count($output) == 1) {
                    // set du nouveau pdf et de son thumb seulement s'il n'y a
                    // pas eu d'erreur pendant la génération du thumb
                    $row->thumb = Library_Book::getThumbFolder() . $i;
                } else {
                    // la miniature n'a pas pu être générée
                    $success = false;
                    $msg = Library_Wording::get('thumb_doesnt_generate', $i, $p)
                        . '. ' . Library_Wording::get('book_still_save');
                }
                $row->filename = Library_Book::getUploadPdfFolder() . $p;
            } else {
                $success = false;
                $msg = Library_Wording::get('bad_pdf_type');
            }
        }

        $row->save();

        // set des niveaux
        $niveaux = $this->getGroupParam('niveau');
        $table_link = new Library_Book_Niveau();
        $table_link->delete($table_link->getAdapter()->quoteInto('book_id IN(?)', $row->id));
        foreach ($niveaux as $key => $niveau) {
            $table_link->insert(array(
                'book_id' => $row->id,
                'niveau_id' => $key
            ));
        }
        
        return array(
            'success' => $success,
            'msg' => $msg,
            'infos' => $row->toArray()
        );
    }

    protected function removeBook() {
        Library_Config::getInstance()->testIssetAuser();
        $ids = $this->getParam('ids');
        if (!$this->getParam('forceConfirm', false)) {
            // check si plusieurs livres on l'élément
            $table = new Library_User_Download();
            $rowset = $table->fetchAll($table
                ->select()
                ->where('book_id IN(?)', $ids)
            );
            if ($rowset->count() > 0) {
                // il y a d'autres livres concernés par cette suppression. On
                // renvoie au navigateur la demande de confirmation
                return array(
                    'success' => true,
                    'confirm' => true,
                    'nb' => $rowset->count()
                );
            }
        }
        $table = new Library_Book();
        $table->delete($table->getAdapter()->quoteInto('id IN(?)', $ids));

        Library_Config::log(sprintf(Library_Wording::get('book_delete'), implode(', ', $ids)));
        return array(
            'success' => true,
            'nb' => count($ids)
        );
    }

    protected function checkNewBooks() {
        Library_Config::getInstance()->testIssetAuser();
        if ($this->getParam('start', 0) == 0) {
            Library_Util::backupDb();
        }
        $generate_thumb = $this->getParam('withThumb', true);
        $stop = false;

        $files = glob(Library_Book::getTmpPdfPath(true) . '*.pdf');
        $msg = array();
        $table = new Library_Book();
        foreach ($files as $file) {
            // on chope le slug du fichier
            $tmp = str_replace(Library_Book::getTmpPdfPath(true), '', $file);
            $title = Library_Util::getSlug($tmp);
            // on déplace ce fichier dans le dossier d'upload
            $filename = Library_Book::getUploadPdfPath(true) . $tmp;
            $success = @rename($file, $filename);
            $thumb = $generate_thumb;
            if ($success) {
                // on essaie de générer le thumb
                if ($generate_thumb) {
                    $output = $this->generatePdfFirstPageThumb($filename, Library_Book::getThumbPath(true). $tmp . '.jpg');
                    if (count($output) != 1) {
                        $success = false;
                        $thumb = false;
                    }
                }
                // on crée l'entrée dans la base
                $table->insert(array(
                    'title' => $title,
                    'thumb' => $thumb ? Library_Book::getThumbFolder() . $tmp . '.jpg' : '',
                    'filename' => Library_Book::getUploadPdfFolder() . $tmp,
                    'tags' => 'new'
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

    private function generatePdfFirstPageThumb($pdf, $img) {
        $path_convert = Library_Config::getInstance()->getData()->path->convert;

        $argpdf = $pdf . '[0]';
        $cmd = $path_convert . "convert " . escapeshellarg($argpdf) . " " . escapeshellarg($img);
        $output = array($cmd);
        exec($cmd, $output);

        // log de l'output
        ob_start();
        echo "<pre>";
        print_r($output);
        echo "</pre>";
        $content = ob_get_contents();
        ob_end_clean();
        Library_Config::log()->debug(Library_Wording::get('thumb_generation') . $content);

        return $output;
    }

    protected function download() {
        // le download n'est pas appelé en ajax. Si on ne change pas le header
        // et qu'il y a une exception, ça va proposer en téléchargement le
        // fichier controller.php avec l'erreur dedans. On set donc text/plain
        // pour ne pas télécharger le fichier si quelque chose de louche se
        // passe
        header('Content-Type: text/plain');
        Library_Config::getInstance()->testIssetAuser();

        $config = Library_Config::getInstance();
        $user = $config->getUser();
        $table = new Library_Book();
        $book = $table->fetchRow($table->select()->where('id = ?', $this->getParam('id')));

        $filelocation = $config->getData()->path->pdf . $book->filename;
        $filename = array_pop(explode('/', $book->filename));

        if (! file_exists($filelocation) || is_dir($filelocation)) {
            die("Unkown file:".$book->filename);
        }

        // log du download pour cet utilisateur, avant de l'envoyer
        $table = new Library_User_Download();
        $table->insert(array(
            'book_id' => $book->id,
            'user_id' => $user->id,
            'downloaded_at' => date('Y-m-d H:i:s')
        ));

        Library_Config::log(sprintf(Library_Wording::get('book_download'), $book->title, $book->id));

        header('HTTP/1.1 200 OK');
        header('Date: ' . date("D M j G:i:s T Y"));
        header('Last-Modified: ' . date("D M j G:i:s T Y"));
        header("Content-Type: application/force-download"); // changed to force download
        header("Content-Lenght: " . (string)(filesize($filelocation)));
        header("Content-Transfer-Encoding: Binary"); // added
        header('Content-Disposition: attachment; filename="'.$filename.'"');

        fpassthru(fopen($filelocation,'rb'));
        exit;
    }

    protected function generatePdfThumb() {
        Library_Config::getInstance()->testIssetAuser();
        // set des paramètres PHP pour favoriser l'upload au mieux
        ini_set('max_execution_time', 240);
        ini_set('memory_limit', '256M');
        // on laisse la possibilité de générer un thumb même si aucun id de
        // livre n'est fourni
        $pdfname = $this->getParam('pdf', '');
        $imagename = $pdfname;
        $book = null;
        // si un livre est fourni, on en récupère les informations afin de
        // déterminer le nom du thumb
        if ($this->getParam('book_id', null)) {
            $table = new Library_Book();
            $book = $table->fetchRow($table->select()->where('id = ?', $this->getParam('book_id')));
            $imagename = $book->title;
            if ($book->filename) {
                $pdfname = $book->filename;
            }
        }

        $i = Library_Util::getSlug($imagename) . '.jpg';
        $pdf = Library_Config::getInstance()->getData()->path->pdf . $pdfname;
        if (file_exists($pdf) && is_file($pdf)) {
            $output = $this->generatePdfFirstPageThumb($pdf, Library_Book::getThumbPath(true). $i);
            $thumb = Library_Book::getThumbFolder() . $i;
            // On check s'il n'y a pas eu d'erreur pendant la génération du thumb
            if (count($output) == 1) {
                // // s'il y a un livre défini, on lui set son thumb
                if ($book) {
                    $book->thumb = $thumb;
                    $book->save();
                }
                return array(
                    'success' => true,
                    'thumb' => $thumb
                );
            } else {
                // s'il y a un livre mais que la génération a planté, on renvoie
                // le thumb actuel du bouquin
                if ($book) {
                    $thumb = $book->thumb;
                }
                return array(
                    'success' => false,
                    'error' => sprintf(Library_Wording::get('thumb_doesnt_generate'), $i, $pdfname)
                );
            }
        } else {
            return array(
                'success' => false,
                'error' => sprintf(Library_Wording::get('pdf_doesnt_exists'), $pdfname)
            );
        }
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
            $result->last_connected = date('Y-m-d H:i:s');
            $result->save();

            Library_Config::log('connexion');
            
            return array(
                'success' => true
            );
        }

        return array(
            'success' => false,
            'error' => Library_Wording::get('bad_login')
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
        Library_Config::getInstance()->testIssetAuser();
        $table = new Library_Book_Type();
        $table->insert(array(
            'label' => $this->getParam('text')
        ));
        return array(
            'success' => true,
            'id' => $table->getAdapter()->lastInsertId()
        );
    }

    protected function editType() {
        Library_Config::getInstance()->testIssetAuser();
        $table = new Library_Book_Type();
        $row = $table->fetchRow($table->select()->where('id = ?', $this->getParam('id')));

        $row->label = $this->getParam('text');
        $row->save();

        return array(
            'success' => true
        );
    }

    protected function removeType() {
        Library_Config::getInstance()->testIssetAuser();
        $id = $this->getParam('id');
        if (!$this->getParam('forceConfirm', false)) {
            // check si plusieurs livres on l'élément
            $table = new Library_Book();
            $rowset = $table->fetchAll($table
                ->select()
                ->where('type_id = ?', $id)
            );
            if ($rowset->count() > 0) {
                // il y a d'autres livres concernés par cette suppression. On
                // renvoie au navigateur la demande de confirmation
                return array(
                    'success' => true,
                    'confirm' => true,
                    'nb' => $rowset->count()
                );
            }
        }

        // suppression de l'élément
        $table = new Library_Book_Type();
        $table->delete($table->getAdapter()->quoteInto('id = ?', $id));

        Library_Config::log(sprintf(Library_Wording::get('type_delete'), $id));
        return array(
            'success' => true
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
        Library_Config::getInstance()->testIssetAuser();
        $table = new Library_Book_Editor();
        $table->insert(array(
            'editor' => $this->getParam('text')
        ));
        return array(
            'success' => true,
            'id' => $table->getAdapter()->lastInsertId()
        );
    }

    protected function editEditor() {
        Library_Config::getInstance()->testIssetAuser();
        $table = new Library_Book_Editor();
        $row = $table->fetchRow($table->select()->where('id = ?', $this->getParam('id')));

        $row->editor = $this->getParam('text');
        $row->save();

        return array(
            'success' => true
        );
    }

    protected function removeEditor() {
        Library_Config::getInstance()->testIssetAuser();
        $id = $this->getParam('id');
        if (!$this->getParam('forceConfirm', false)) {
            // check si plusieurs livres on l'élément
            $table = new Library_Book();
            $rowset = $table->fetchAll($table
                ->select()
                ->where('editor_id = ?', $id)
            );
            if ($rowset->count() > 0) {
                // il y a d'autres livres concernés par cette suppression. On
                // renvoie au navigateur la demande de confirmation
                return array(
                    'success' => true,
                    'confirm' => true,
                    'nb' => $rowset->count()
                );
            }
        }

        // suppression de l'élément
        $table = new Library_Book_Editor();
        $table->delete($table->getAdapter()->quoteInto('id = ?', $id));

        Library_Config::log(sprintf(Library_Wording::get('editor_delete'), $id));
        return array(
            'success' => true
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

    protected function getNiveauListForCheckboxGroup($book) {
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
        return $niveaux;
    }

    protected function addNiveau() {
        Library_Config::getInstance()->testIssetAuser();
        $table = new Library_Niveau();
        $table->insert(array(
            'label' => $this->getParam('text')
        ));
        return array(
            'success' => true,
            'id' => $table->getAdapter()->lastInsertId()
        );
    }

    protected function editNiveau() {
        Library_Config::getInstance()->testIssetAuser();
        $niveaux = $this->getGroupParam('niveau');
        $table = new Library_Niveau();

        foreach ($niveaux as $id => $val) {
            $row = $table->fetchRow($table->select()->where('id = ?', $id));
            $row->label = $val;
            $row->save();
        }

        $t = new Library_Book();
        $book = $t->fetchRow($t->select()->where('id = ?', $this->getParam('book_id', 0)));
        if (!$book) {
            $book = $t->createRow();
        }
        return array(
            'success' => true,
            'niveaux' => $this->getNiveauListForCheckboxGroup($book)
        );
    }

    protected function removeNiveau() {
        Library_Config::getInstance()->testIssetAuser();
        $ns = $this->getGroupParam('niveau');
        $niveaux = array_keys($ns);
        if (!$this->getParam('forceConfirm', false)) {
            // check si plusieurs livres on l'élément
            $table = Zend_Registry::get('db');
            $rowset = $table->fetchAll($table
                ->select()
                ->from(array('n' => 'library_niveau'), array('txt' => 'n.label'))
                ->join(array('nb' => 'library_book_niveau'), 'nb.niveau_id = n.id', array('nbd' => 'COUNT(*)'))
                ->where('n.id IN(?)', $niveaux)
                ->group('nb.niveau_id')
            );

            if (count($rowset) > 0) {
                $more = false;
                foreach ($rowset as $row) {
                    if ($row['nbd'] > 0) {
                        $more = true;
                        break;
                    }
                }
                // il y a d'autres livres concernés par cette suppression. On
                // renvoie au navigateur la demande de confirmation
                if ($more) {
                    return array(
                        'success' => true,
                        'confirm' => true,
                        'nb' => $rowset
                    );
                }
            }
        }

        // suppression de l'élément
        $table = new Library_Niveau();
        $table->delete($table->getAdapter()->quoteInto('id IN(?)', $niveaux));

        Library_Config::log(sprintf(Library_Wording::get('niveau_delete'), implode(', ', $niveaux)));

        $t = new Library_Book();
        $book = $t->fetchRow($t->select()->where('id = ?', $this->getParam('book_id', 0)));
        if (!$book) {
            $book = $t->createRow();
        }
        return array(
            'success' => true,
            'niveaux' => $this->getNiveauListForCheckboxGroup($book)
        );
    }





    /**
     * --------------------------------------------------------------
     *              Méthodes pour les utilisateurs
     * --------------------------------------------------------------
     */

    protected function getUserList() {
        Library_Config::getInstance()->testIssetAuser();
        $table = new Library_User();
        $rowset = $table->fetchAll($table->select()
            ->order($this->getParam('sort', 'login') . ' ' . $this->getParam('dir', 'ASC'))
        );
        $data = array();
        foreach ($rowset as $row) {
            $data[] = array_merge($row->toArray(), array(
                'pass' => ''
            ));
        }
        return array(
            'success' => true,
            'total' => count($data),
            'users' => $data
        );
    }

    protected function saveUser() {
        Library_Config::getInstance()->testIssetAuser();

        $id = $this->getParam('id');
        $table = new Library_User();

        if ($id) {
            $row = $table->fetchRow($table->select()->where('id = ?', $this->getParam('id')));
        } else {
            $row = $table->createRow();
        }

        $field = $this->getParam('field');
        $value = $this->getParam('value');
        
        $row->$field = $field == 'pass' ? md5($value) : $value;
        $row->save();

        return array(
            'success' => true,
            'id' => $row->id
        );
    }
    
    protected function removeUser() {
        Library_Config::getInstance()->testIssetAuser();
        $id = $this->getParam('id');

        $user = Library_Config::getInstance()->getUser();
        if ($user->id == $id) {
            return array(
                'success' => false,
                'error' => Library_Wording::get('own_deletion_not_allowed')
            );
        }

        if (!$this->getParam('forceConfirm', false)) {
            // check si plusieurs livres on l'élément
            $table = new Library_User_Download();
            $rowset = $table->fetchAll($table
                ->select()
                ->where('user_id = ?', $id)
            );
            if ($rowset->count() > 0) {
                // il y a d'autres livres concernés par cette suppression. On
                // renvoie au navigateur la demande de confirmation
                return array(
                    'success' => true,
                    'confirm' => true,
                    'msg' => sprintf(Library_Wording::get('user_delete_confirm'), $rowset->count()),
                    'nb' => $rowset->count()
                );
            }
        }

        $table = new Library_User();
        $table->delete($table->getAdapter()->quoteInto('id = ?', $this->getParam('id')));

        Library_Config::log(sprintf(Library_Wording::get('user_delete'), $id));
        return array(
            'success' => true
        );
    }





    /**
     * --------------------------------------------------------------
     *        Méthodes pour les téléchargements des utilisateurs
     * --------------------------------------------------------------
     */

    protected function getUserDownloadList() {
        Library_Config::getInstance()->testIssetAuser();
        $items = array();
        $table = Zend_Registry::get('db');
        $rowset = $table->fetchAll($table
            ->select()
            ->from(array('b' => 'library_book'), array('btitle' => 'b.title', 'bid' => 'b.id'))
            ->join(array('d' => 'library_user_download'), 'd.book_id = b.id', array('nb' => 'COUNT(*)'))
            ->where('d.user_id = ?', $this->getParam('user_id'))
            ->group('d.book_id')
        );

        foreach ($rowset as $row) {
            $items[] = array(
                'id' => $row['bid'],
                'title' => $row['btitle'],
                'nb' => $row['nb']
            );
        }
        return array(
            'success' => true,
            'items' => $items
        );
    }

    protected function exportUserDownloadCav() {
        header('Content-Type: text/plain');
        Library_Config::getInstance()->testIssetAuser();

        $table = Zend_Registry::get('db');
        $rowset = $table->fetchAll($table
            ->select()
            ->from(array('b' => 'library_book'), array('btitle' => 'b.title', 'bid' => 'b.id'))
            ->join(array('d' => 'library_user_download'), 'd.book_id = b.id', array('nb' => 'COUNT(*)'))
            ->where('d.user_id = ?', $this->getParam('user_id'))
            ->group('d.book_id')
        );

        $t = new Library_User();
        $user = $t->fetchRow($t->select()->where('id = ?', $this->getParam('user_id')));

        $items = array(
            array('user id', 'user login', 'livre id', 'livre titre', 'nb downloads')
        );
        foreach ($rowset as $row) {
            $items[] = array(
                $user->id, '"' . $user->login .'"', $row['bid'],
                '"'. str_replace('"', "'", $row['btitle']) .'"', $row['nb']
            );
        }

        header('Expires: 0');
        header('Cache-control: private');
        header('Cache-Control: must-revalidate, post-check=0, pre-check=0');
        header('Content-Description: File Transfer');
        header('Content-Type: application/csv');
        header('Content-disposition: attachment; filename=' . $user->login . '.csv');

        $str = '';
        foreach ($items as $item) {
            $str .= implode(',', $item) . PHP_EOL;
        }

        echo $str;
        exit;
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
        $log = array();
        // on check si on peut ouvrir ce fichier uploadé
        if ($file['error'] == UPLOAD_ERR_OK && $file['type'] == 'text/csv') {
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
                    return false;
                }

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