<?php

/**
 * Classe de définition d'un livre numérisé PDF
 *
 * @package Library
 * @copyright Library
 */
class Library_Book extends Zend_Db_Table_Abstract {

    /**
     * La colonne primaire/unique/auto-incrémentale
     * @var string
     */
    protected $_primary = 'id';

    protected $_name = 'library_book';

    protected $_dependentTables = array('Library_Book_Niveau', 'Library_User_Download');

    /**
     * Lien avec les autres tables
     * @var array
     */
    protected $_referenceMap = array(
        'Type' => array(
            'columns' => 'type_id',
            'refTableClass' => 'Library_Book_Type',
            'refColumns' => 'id'
        ),
        'Editor' => array(
            'columns' => 'editor_id',
            'refTableClass' => 'Library_Book_Editor',
            'refColumns' => 'id'
        )
    );

    public static function getListForImportDistinct() {
        $table = new self();
        $rowset = $table->fetchAll($table->select());
        $data = array('slug' => array(), 'filename' => array());
        foreach ($rowset as $row) {
            $data['slug'][] = Library_Util::getSlug($row->title);
            $data['filename'][] = $row->filename;
        }
        return $data;
    }

    /**
     * Retourne le chemin vers l'image
     *
     * @param boolean $full true pour retourner le chemin complet racine
     * @return string
     */
    public static function getThumbPath($full = false) {
        return ($full ? Library_Config::getInstance()->getRoot() : '') . self::getThumbFolder();
    }

    public static function getThumbFolder() {
        return 'resources/books/';
    }

    /**
     * Retourne le chemin vers le dossier d'upload pdf
     *
     * @param boolean $full true pour retourner le chemin complet racine
     * @return string
     */
    public static function getUploadPdfPath($full = false) {
        return ($full ? Library_Config::getInstance()->getData()->path->pdf : '') . self::getUploadPdfFolder();
    }

    public static function getUploadPdfFolder() {
        return 'upload/';
    }

    /**
     * Retourne le chemin vers le dossier temporaire des pdf
     *
     * @param boolean $full true pour retourner le chemin complet racine
     * @return string
     */
    public static function getTmpPdfPath($full = false) {
        return ($full ? Library_Config::getInstance()->getData()->path->pdf : '') . self::getTmpPdfFolder();
    }

    public static function getTmpPdfFolder() {
        return 'tmp/';
    }

}
