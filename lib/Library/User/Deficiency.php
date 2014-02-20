<?php

class Library_User_Deficiency extends Zend_Db_Table_Abstract {

    protected $_primary = 'id';

    protected $_name = 'library_user_deficiency';

    protected $_dependentTables = array('Library_User');

    public static function getList() {
        $table = new self();
        return $table->fetchAll($table
            ->select()
            ->order('user_deficiency ASC')
        );
    }

    public static function getListToArray() {
        $rowset = self::getList();
        $data = array();
        foreach ($rowset as $row) {
            $data[$row->id] = $row->user_deficiency;
        }
        return $data;
    }
    
    public static function getComboList() {
        $list = self::getList();
        $data = array();
        foreach ($list as $l) {
            $data[] = array(
                0 => $l['id'],
                1 => $l['user_deficiency']
            );
        }
        return $data;
    }

}
