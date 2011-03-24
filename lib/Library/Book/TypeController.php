<?php

class Library_Book_TypeController extends Library_Controller {




    /**
     * --------------------------------------------------------------
     *              Méthodes pour les matières
     * --------------------------------------------------------------
     */

    public function getTypeList() {
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



}