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
        $data = Library_Config::getInstance()->getData();
        return ($full ? $data->path->pdf : $data->web->pdf) . self::getThumbFolder();
    }

    public static function getThumbFolder() {
        return 'images/';
    }

    /**
     * Retourne le chemin vers l'image mini de la grid
     *
     * @param boolean $full true pour retourner le chemin complet racine
     * @return string
     */
    public static function getMiniPath($full = false) {
        $data = Library_Config::getInstance()->getData();
        return ($full ? $data->path->pdf : $data->web->pdf) . self::getMiniFolder();
    }

    public static function getMiniFolder() {
        return 'images/mini/';
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
     * Récupère le chemin serveur vers le PDF en fonction de la matière
     * 
     * @param Library_Book $book
     * @return array 
     */
    public static function getPdfPath($book) {     
        // on détermine le bon dossier en fonction de la matière, en checkant
        // sur le label de la matière du livre sélectionné
        $ttype = new Library_Book_Type();
        $type = $ttype->fetchRow($ttype->select()->where('id = ?', $book->type_id));
        
        // il peut ne pas y avoir de type. Du coup, on lance un message d'erreur
        if (!$type) {
            return false;
        }
        
        $label = $type->label;
        $co = $mats = Library_Config::getInstance()->getData()->matieres->toArray();

        $label = (isset($co[$label]) ? $co[$label] : $label) . '/';
        $new_path = Library_Config::getInstance()->getData()->path->pdf . $label;
        
        if (!is_dir($new_path)) {
            mkdir($new_path, 0777);
        }
        
        return array(
            'fullpath' => $new_path,
            'label' => $label,
            'path' => Library_Config::getInstance()->getData()->path->pdf
        );
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
