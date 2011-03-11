<?php

class Library_Book_Type extends Zend_Db_Table_Abstract {

    protected $_primary = 'id';

    protected $_dependentTables = array('Library_Book');

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