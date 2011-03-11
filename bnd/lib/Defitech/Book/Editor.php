<?php

class Defitech_Book_Editor extends Zend_Db_Table_Abstract {

    protected $_primary = 'id';

    protected $_dependentTables = array('Defitech_Book');

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
