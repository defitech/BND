<?php

class Library_Book_Controller extends Library_Controller {



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
        $c = new Library_Book_NiveauController($this->getParams());
        $e = new Library_Book_EditorController($this->getParams());
        $t = new Library_Book_TypeController($this->getParams());
        return array(
            'success' => true,
            'data' => array_merge($book->toArray(), array(
                'editors' => $e->getEditorList(),
                'types' => $t->getTypeList(),
                'niveaux' => $c->getNiveauListForCheckboxGroup($book),
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
        $skip_thumb = $this->getParam('skipThumb');
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
            $thumb = !$skip_thumb;
            if ($success) {
                // on essaie de générer le thumb
                if (!$skip_thumb) {
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


}