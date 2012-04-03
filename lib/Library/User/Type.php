<?php

class Library_User_Type extends Zend_Db_Table_Abstract {

    protected $_primary = 'id';

    protected $_name = 'library_user_type';

    protected $_dependentTables = array('Library_User');

    public static function getList() {
        $table = new self();
        return $table->fetchAll($table
            ->select()
            ->order('user_type ASC')
        );
    }

    public static function getListToArray() {
        $rowset = self::getList();
        $data = array();
        foreach ($rowset as $row) {
            $data[$row->id] = $row->user_type;
        }
        return $data;
    }
    
    public static function getComboList() {
        $list = self::getList();
        $data = array();
        foreach ($list as $l) {
            $data[] = array(
                0 => $l['id'],
                1 => $l['user_type']
            );
        }
        return $data;
    }

}
