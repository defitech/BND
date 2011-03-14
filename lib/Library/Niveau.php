<?php

class Library_Niveau extends Zend_Db_Table_Abstract {

    protected $_primary = 'id';

    protected $_name = 'library_niveau';

    protected $_dependentTables = array('Library_Book_Niveau');

    public static function getList() {
        $table = new self();
        return $table->fetchAll($table
            ->select()
            ->order('label ASC')
        );
    }

    public static function getListToArray() {
        $rowset = self::getList();
        $data = array();
        foreach ($rowset as $row) {
            $data[$row->id] = $row->label;
        }
        return $data;
    }

}
