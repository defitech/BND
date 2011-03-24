<?php

class Library_Book_NiveauController extends Library_Controller {




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

    public function getNiveauListForCheckboxGroup($book) {
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





}