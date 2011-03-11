<?php

/**
 * Classe de définition d'un livre numérisé PDF
 *
 * @package Defitech
 * @copyright Defitech
 */
class Defitech_Book extends Zend_Db_Table_Abstract {

    /**
     * La colonne primaire/unique/auto-incrémentale
     * @var string
     */
    protected $_primary = 'id';

    protected $_dependentTables = array('Defitech_Book_Niveau');

    /**
     * Lien avec les autres tables
     * @var array
     */
    protected $_referenceMap = array(
        'Type' => array(
            'columns' => 'type_id',
            'refTableClass' => 'Defitech_Book_Type',
            'refColumns' => 'id'
        ),
        'Editor' => array(
            'columns' => 'editor_id',
            'refTableClass' => 'Defitech_Book_Editor',
            'refColumns' => 'id'
        )
    );

    /**
     * Retourne le chemin vers l'image
     *
     * @param boolean $full true pour retourner le chemin complet racine
     * @return string
     */
    public static function getThumbPath($full = false) {
        return ($full ? Defitech_Config::getInstance()->getRoot() : '') . self::getThumbFolder();
    }

    public static function getThumbFolder() {
        return 'resources/books/';
    }

}
