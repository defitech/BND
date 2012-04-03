<?php

/**
 * Gestion ultra basique d'utilisateurs et droits
 *
 * @package Library
 * @copyright Library
 */
class Library_User extends Zend_Db_Table_Abstract {

    /**
     * La colonne primaire/unique/auto-incrémentale
     * @var string
     */
    protected $_primary = 'id';

    protected $_dependentTables = array('Library_User_Download');

    /**
     * Lien avec les autres tables
     * @var array
     */
    protected $_referenceMap = array(
        'Type' => array(
            'columns' => 'type_id',
            'refTableClass' => 'Library_User_Type',
            'refColumns' => 'id'
        )
    );

    public static function right($right) {
        return Library_Config::getInstance()->getUser($right) ? true : false;
    }

    public static function getList() {
        $table = new self();
        return $table->fetchAll($table
            ->select()
            ->order('login ASC')
        );
    }

    public static function getListToArray() {
        $rowset = self::getList();
        $data = array();
        foreach ($rowset as $row) {
            $data[$row->id] = $row->login;
        }
        return $data;
    }

}