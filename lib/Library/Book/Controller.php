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
        $dir = strtolower($dir) == 'asc' ? 'ASC' : 'DESC';
        $start = $this->getParam('start', 0);
        $limit = $this->getParam('limit', null);
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

        // check des droits
        $user = Library_Config::getInstance()->getUser();
        // s'il ne s'agit pas d'un admin, on ajoute la condition de droits
        if (!Library_User::right(2)) {
            $select->where('(library_book.right IS NULL OR library_book.right = "" OR library_book.right LIKE ?)', '%|' . $user->id . '|%');
        }

        // on va gérer l'éventuel multisort de la grid (programmatiquement
        // généré pour les besoins du print)
        $msorts = $this->getParam('sorts', null);
        if (is_array($msorts) && count($msorts)) {
            foreach ($msorts as $msort => $mdir) {
                $mdir = strtolower($mdir) == 'asc' ? 'ASC' : 'DESC';
                switch($msort) {
                    case 'editor_id': $select->order('e.editor ' . $mdir); break;
                    case 'type_id': $select->order('t.label ' . $mdir); break;
                    case 'niveau_id': $select->order('n.label ' . $mdir); break;
                }
            }
        }

        switch($sort) {
            case 'editor_id': $select->order('e.editor ' . $dir); break;
            case 'type_id': $select->order('t.label ' . $dir); break;
            case 'niveau_id': $select->order('n.label ' . $dir); break;
            default: $select->order('library_book.' . $sort . ' ' . $dir);
        }
        $select->order('library_book.title ' . $dir);

        // ajout des filtres non-grid s'il y en a
        if (isset($filters['fullsearch']) && $filters['fullsearch']) {
            $terms = str_replace('"', '', stripslashes($filters['fullsearch']));
            $terms = explode(' ', Library_Util::getSlug(trim($terms), ' '));
            foreach ($terms as $term) {
                // définition des champs touchés par le fullsearch
                $fullsearch_fields = array(
                    'library_book.title', 'library_book.tags', 'library_book.isbn', 'library_book.notes',
                    'e.editor',
                    't.label',
                    'n.label'
                );
                // création du bout de requête sql
                $tmp = array();
                foreach ($fullsearch_fields as $field) {
                    $t = '%' . $term . '%';
                    // on envoie directement la string dans la requete, mais il
                    // ne devrait pas y avoir de soucis d'injection, car la
                    // string est "sluguée" avant d'être processée. Cela dit
                    // l'idéal serait d'arriver à binder les paramètres, mais
                    // Zend ne semble pas vouloir dans ce cas précis...
                    $tmp[] = $field . ' LIKE "' . $t . '"';
                }
                $select->where('(' . implode(' OR ', $tmp) . ')');
            }
        }

        // ajout des filtres grid s'il y en a
        foreach ($gridFilters as $filter) {
            switch ($filter['data']['type']) {
                case 'numeric':
                    $operator = '=';
                    if ($filter['data']['comparison'] == 'gt')
                        $operator = '>';
                    elseif ($filter['data']['comparison'] == 'lt')
                        $operator = '<';
                    
                    $select->where('library_book.' . $filter['field'] . $operator . '?', $filter['data']['value']);
                    break;
                case 'string':
                    $select->where('library_book.' . $filter['field'] . ' LIKE ?', '%' . $filter['data']['value'] . '%');
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
            $thumb = 'resources/images/emptysmall.jpg';
            $thumbName = '';
            if ($row->thumb) {
                // test de l'existence d'une mini
                $tmp = $row->thumb;
                if (file_exists(Library_Book::getMiniPath(true) . $tmp)) {
                    $thumb = Library_Book::getMiniPath() . $tmp;
                    $thumbName = $row->thumb;
                } elseif (file_exists(Library_Book::getThumbPath(true) . $tmp)) {
                    $thumb = Library_Book::getThumbPath() . $tmp;
                    $thumbName = $row->thumb;
                }
            }
            $books[] = array_merge($row->toArray(), array(
                'editorid' => $row->editor_id,
                'editor_id' => isset($editors[$row->editor_id]) ? $editors[$row->editor_id] : $row->editor_id,
                'typeid' => $row->type_id,
                'type_id' => isset($types[$row->type_id]) ? $types[$row->type_id] : $row->type_id,
                'thumb' => $thumb,
                'thumbName' => $thumbName,
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
    
    protected function printBooks() {
        header('Content-type: text/html');
        Library_Config::getInstance()->testIssetAuser(2);
        
        $data = $this->getBookList();
        
        $books = $data['books'];
        ob_start();
        include Library_Config::getInstance()->getRoot() . 'print.php';
        $content = ob_get_contents();
        ob_clean();
        
        echo $content;
        exit;
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

        $c = new Library_Book_NiveauController($this->getParams());
        $e = new Library_Book_EditorController($this->getParams());
        $t = new Library_Book_TypeController($this->getParams());
        return array(
            'success' => true,
            'data' => array_merge($book->toArray(), array(
                'editors' => $e->getEditorList(),
                'types' => $t->getTypeList(),
                'niveaux' => $c->getNiveauListForCheckboxGroup($book),
                'rights' => $this->getRightListForCheckboxGroup($book),
                'thumbBasePath' => Library_Book::getThumbPath(),
                'maxpostsize' => Library_Config::getInstance()->getMaxPostSize()
            ))
        );
    }

    private function getRightListForCheckboxGroup($book) {
        if (!Library_User::right(1)) return array();
        
        $users = Library_User::getListToArray();
        // récupération ultra basique des droits: un champ varchar avec des
        // ids entourés par des | (pipe)
        $users_select = explode('|', trim($book->right, '|'));
        $rights = array();
        foreach ($users as $i => $u) {
            // création du tableau pour le set de checkbox
            $rights[] = array(
                'boxLabel' => $u,
                'checked' => in_array($i, $users_select) ? true : false,
                'name' => 'right-' . $i
            );
        }
        return $rights;
    }

    protected function saveBook() {
        Library_Config::getInstance()->testIssetAuser(2);
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
        
        $old_type = $row->type_id;
        
        // création des tags automatiques par rapport au titre
        $old_tags = explode('-', Library_Util::getSlug($row->title));
        $new_tags = explode('-', Library_Util::getSlug($this->getParam('title')));
        
        $tags = explode(',', $this->getParam('tags'));
        $ok_tags = array();
        foreach ($tags as $tag) {
            // on supprime les tags qui correspondent à l'ancien titre et qui ne
            // sont pas dans le nouveau
            if (in_array($tag, $old_tags) && !in_array($tag, $new_tags)) continue;
            
            $ok_tags[] = $tag;
        }
        // on ajoute les tags du nouveau titre
        $ok_tags = array_unique(array_merge($ok_tags, $new_tags));

        $row->title = stripslashes($this->getParam('title'));
        $row->isbn = $this->getParam('isbn');
        $row->thumb = $this->getParam('thumb');
        $row->tags = implode(',', $ok_tags);
        $row->filename = $this->getParam('pdf');
        $row->editor_id = $this->getParam('editor_id');
        $row->type_id = $this->getParam('type_id');
        $row->notes = $this->getParam('notes', null);

        if (Library_User::right(1)) {
            $rights = $this->getGroupParam('right');
            $row->right = count($rights) ? sprintf('|%s|', implode('|', array_keys($rights))) : '';
        }

        // si un thumb est envoyé en fichier
        $thumb = $_FILES['thumbfile'];
        if ($thumb['error'] == UPLOAD_ERR_OK) {
            $valid_extensions = array('image/jpeg', 'image/jpg', 'image/png', 'image/gif');
            if (in_array($thumb['type'], $valid_extensions)) {
                $img = Library_Util::getSlug($row->title) . strrchr($thumb['name'], '.');
                if (!move_uploaded_file($thumb['tmp_name'], Library_Book::getThumbPath(true) . $img))
                    Library_Config::log()->err(sprintf('Upload image %s echoué', Library_Book::getThumbPath(true) . $img));
                $this->resizeThumbAndCreateMini($img);
                // set du nouveau thumb
                Library_Config::log('Sauve image ' . $thumb['tmp_name'] . ' vers ' . Library_Book::getThumbPath(true) . $img);
                $row->thumb = $img;
            } else {
                $success = false;
                $msg = Library_Wording::get('bad_thumb_type')
                    . '. ' . Library_Wording::get('book_still_save');
            }
        }

        // si un pdf est envoyé en fichier
        $pdf = isset($_FILES['pdffile']) ? $_FILES['pdffile'] : null;
        if ($pdf && $pdf['error'] == UPLOAD_ERR_OK) {
            $valid_extensions = array('application/pdf', 'application/download');
            if (in_array($pdf['type'], $valid_extensions)) {
                $npath = Library_Book::getPdfPath($row);
                $path = $npath ? $npath['fullpath'] : Library_Book::getUploadPdfPath(true);
                if (!is_dir($path)) {
                    mkdir($path, 0766);
                }
                $p = Library_Util::getSlug($row->title) . '.pdf';
                $i = Library_Util::getSlug($row->title) . '.jpg';
                if (!move_uploaded_file($pdf['tmp_name'], $path . $p))
                    Library_Config::log()->err(sprintf('Move PDF %s échoué', $path . $p));
                        
                $output = $this->generatePdfFirstPageThumb($path . $p, Library_Book::getThumbPath(true). $i);
                if (count($output) == 1) {
                    $this->resizeThumbAndCreateMini($i);
                    // set du nouveau pdf et de son thumb seulement s'il n'y a
                    // pas eu d'erreur pendant la génération du thumb
                    Library_Config::log('Sauve image depuis PDF (' . $p . ') ' . $pdf['tmp_name'] . ' vers ' . $i);
                    $row->thumb = $i;
                } else {
                    // la miniature n'a pas pu être générée
                    $success = false;
                    $msg = Library_Wording::get('thumb_doesnt_generate', $i, $p)
                        . '. ' . Library_Wording::get('book_still_save');
                }
                $row->filename = ($npath ? $npath['label'] : Library_Book::getUploadPdfFolder()) . $p;
            } else {
                $success = false;
                $msg = Library_Wording::get('bad_pdf_type')
                    . '. ' . Library_Wording::get('book_still_save');
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
        
        // on bouge le PDF dans le bon répertoire si nécessaire
        if ($old_type != $row->type_id) {
            $old = explode('/', $row->filename);
            array_pop($old);
            $return = $this->doMoveUploadedBooksToGoodFolder($row, implode('/', $old) . '/');
            if (!$return['success']) {
                return $return;
            }
        }
        
        return array(
            'success' => $success,
            'msg' => $msg,
            'infos' => $row->toArray()
        );
    }

    public function resizeThumbAndCreateMini($img) {
        Library_Config::getInstance()->testIssetAuser(2);
        $ext = Library_Util::getExtension($img);

        $i = Library_Book::getThumbPath(true) . $img;
        switch ($ext) {
            case 'jpg':
            case 'jpeg': $im = @imagecreatefromjpeg($i); break;
            case 'png': $im = @imagecreatefrompng($i); break;
            case 'gif': $im = @imagecreatefromgif($i); break;
            default: return false;
        }

        if (!$im) {
            return false;
        }

        list($width, $height) = getimagesize($i);

        // création de la mini (affichée dans la grid)
        $mini_width = 55;
        $mini_height = 80;
        $tn = imagecreatetruecolor($mini_width, $mini_height);
        imagecopyresampled($tn, $im, 0, 0, 0, 0, $mini_width, $mini_height, $width, $height);
        $path = Library_Book::getMiniPath(true);
        if (!is_dir($path)) {
            mkdir($path, 0777);
        }
        imagejpeg($tn, $path . $img, 70);
        imageDestroy($tn);

        // redimensionnement de la thumb
        $mini_width = 258;
        $mini_height = 392;
        $tn = imagecreatetruecolor($mini_width, $mini_height);
        imagecopyresampled($tn, $im, 0, 0, 0, 0, $mini_width, $mini_height, $width, $height);
        imagejpeg($tn, $i, 70);
        imageDestroy($tn);

        return true;
    }

    protected function resizeAllThumbs() {
        Library_Config::getInstance()->testIssetAuser(2);
        // on chope toutes les images du dossier
        $files = glob(Library_Book::getThumbPath(true) . '*.*');

        $msg = array();
        $count = 0;
        $msg = array();
        $start = $this->getParam('start');
        foreach ($files as $file) {
            if ($start == $count) {
                $tmp = str_replace(Library_Book::getThumbPath(true), '', $file);
                $r = $this->resizeThumbAndCreateMini($tmp);
                $msg[] = array(
                    'img' => $tmp,
                    'success' => $r ? 1 : 0
                );
                // on en traite qu'un seul à la fois. Le flux est géré dans le js
                break;
            }
            $count++;
        }
        return array(
            'success' => true,
            'msg' => $msg,
            'total' => count($files),
            'next' => count($files) > $start + 1
        );
    }
    
    /**
     * Déplace les PDF contenus dans le dossier d'upload vers les dossiers
     * de rangement (de upload/ vers Math/, Histoire/, etc.) 
     * 
     * @return array
     */
    protected function moveUploadedBooksToGoodFolder() {
        $total = null;
        $table = new Library_Book();
        
        // si on lance le processus (start = 0) on calcule le nombre total de
        // livres qui ont leur PDF dans le dossier d'upload
        if (!$this->getParam('start', 0)) {
            $count = $table->fetchAll($table->select()->where('filename LIKE ?', Library_Book::getUploadPdfFolder() . '%'));
            $total = count($count);
        }
        
        // on récupère le 1er tuple qui a son PDF dans le dossier d'upload
        $book = $table->fetchRow($table->select()->where('filename LIKE ?', Library_Book::getUploadPdfFolder() . '%'));
        if (!$book) {
            return array(
                'success' => true,
                'next' => false
            );
        }
        
        return array_merge($this->doMoveUploadedBooksToGoodFolder($book), array(
            'total' => $total
        ));
    }
    
    protected function doMoveUploadedBooksToGoodFolder($book, $oldPath = '') {
        $path = Library_Book::getPdfPath($book);
        // il peut ne pas y avoir de type. Du coup, on lance un message d'erreur
        if (!$path) {
            return array(
                'success' => false,
                'error' => sprintf(Library_Wording::get('move_pdf_to_good_folder_notype'), $book->title)
            );
        }
        
        // on déplace le fichier PDF au bon endroit
        $name = str_replace(array($oldPath, Library_Book::getUploadPdfFolder()), '', $book->filename);
        $source = Library_Config::getInstance()->getData()->path->pdf . $book->filename;
        if (!@rename($source, $path['fullpath'] . $name)) {
            $e = error_get_last();
            throw new Exception(sprintf(Library_Wording::get('move_pdf_to_good_folder_error'), $source, $path['fullpath'] . $name, $e['message']));
        }
        
        // on change le filename du livre
        $book->filename = $path['label'] . $name;
        $book->save();
        
        return array(
            'success' => true,
            'next' => true
        );
    }

    protected function removeBook() {
        Library_Config::getInstance()->testIssetAuser(2);
        $ids = $this->getParam('ids');
        if (!$this->getParam('forceConfirm', false)) {
            // check si ce livre a été téléchargé au moins 1x
            $table = new Library_User_Download();
            $rowset = $table->fetchAll($table
                ->select()
                ->where('book_id IN(?)', $ids)
            );
            if ($rowset->count() > 0) {
                // il y a des téléchargements concernant ce livre. On
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
        $f = explode('/', $book->filename);
        $filename = array_pop($f);

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
        header("Content-Length: " . (string)(filesize($filelocation)));
        header("Content-Transfer-Encoding: Binary"); // added
        header('Content-Disposition: attachment; filename="'.$filename.'"');
        @session_write_close();
        @ob_end_clean();
        if($file = fopen($filelocation, 'rb')){
            while( (!feof($file)) && (connection_status()==0) ){
                print(fread($file, 1024*8));
                flush();
            }

            fclose($file);
        }
		
        //fpassthru(fopen($filelocation,'rb'));
        exit;
    }

    protected function generatePdfThumb() {
        Library_Config::getInstance()->testIssetAuser(2);
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
            $thumb = $i;
            // On check s'il n'y a pas eu d'erreur pendant la génération du thumb
            if (count($output) == 1) {
                $this->resizeThumbAndCreateMini($i);
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
