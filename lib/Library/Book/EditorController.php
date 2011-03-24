<?php

class Library_Book_EditorController extends Library_Controller {



    /**
     * --------------------------------------------------------------
     *              Méthodes pour les éditeurs
     * --------------------------------------------------------------
     */

    public function getEditorList() {
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
        Library_Config::getInstance()->testIssetAuser(2);
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
        Library_Config::getInstance()->testIssetAuser(2);
        $table = new Library_Book_Editor();
        $row = $table->fetchRow($table->select()->where('id = ?', $this->getParam('id')));

        $row->editor = $this->getParam('text');
        $row->save();

        return array(
            'success' => true
        );
    }

    protected function removeEditor() {
        Library_Config::getInstance()->testIssetAuser(2);
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


}