<?php

class Library_Book_Editor extends Zend_Db_Table_Abstract {

    protected $_primary = 'id';

    protected $_name = 'library_book_editor';

    protected $_dependentTables = array('Library_Book');

    public static function getList() {
        $table = new self();
        return $table->fetchAll($table
            ->select()
            ->order('editor ASC')
        );
    }

    public static function getListToArray() {
        $rowset = self::getList();
        $data = array();
        foreach ($rowset as $row) {
            $data[$row->id] = $row->editor;
        }
        return $data;
    }

}
