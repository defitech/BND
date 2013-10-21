<?php

class Library_Util {

    /**
     * Méthode pour créer un alias sans caractères spéciaux
     *
     * @link http://forum.webrankinfo.com/fonctions-pour-creer-slug-seo-friendly-url-t99376.html#wrap
     * @param string $url
     * @param string $separator
     * @param integer $charMin le nombre de caractère minimum pour faire un mot
     * @return string
     */
    public static function getSlug($Str, $separator = '-', $charMin = null){

       $Str = str_replace("'", " ", $Str);
       if ($charMin) {
           $tmp = explode(' ', $Str);
           $s = array();
           foreach ($tmp as $i => $val) {
               if (strlen($val) >= $charMin) {
                   $s[] = $val;
               }
           }
           $Str = implode(' ', $s);
       }

       $Str = strtolower($Str);
       $s = array('à','á','â','ã','ä','å','ò','ó','ô','õ','ö','ø','è','é','ê','ë','ç','ì','í','î','ï','ù','ú','û','ü','ÿ','ñ');
       $r = array('a','a','a','a','a','a','o','o','o','o','o','o','e','e','e','e','c','i','i','i','i','u','u','u','u','y','n');
       $Str = str_replace($s, $r, $Str);

       # Ponctuation
       $Stop = array('(', ')', '[', ']', '{', '}', '&', '.', ';', ':', ',', '!', '?', '<', '>');

       # Math
       $Stop = array_merge($Stop, array('+', '-', '*', '/', '°', '%', 'µ', '§'));

       # Divers
       $Stop = array_merge($Stop, array('_', '#', '~', '|', '^', '@', '¨', '$', '£'));

       $Str = str_replace($Stop, ' ', $Str);

       # Remplacement espace multiple
       return preg_replace('/ +/', $separator, trim($Str));
    }

    /**
     * Retourne l'extension d'un fichier
     *
     * @param string $file
     * @param boolean $toLower false pour avoir l'extension telle qu'elle est écrite (majuscule ou minuscule)
     * @return string
     */
    public static function getExtension($file, $toLower = true) {
        $ext = substr($file, strrpos($file, '.') + 1);
        return $toLower ? strtolower($ext) : $ext;
    }

    /**
     * Gère le traitement des fatal error
     *
     * @return void
     */
    public static function manageFatalErrors() {
        $e = error_get_last();
        $types = array(E_ERROR, E_RECOVERABLE_ERROR, E_USER_ERROR);
        if (in_array($e['type'], $types)) {
            Library_Config::log()->err(sprintf('%s, %s, %s, %s', $e['type'], $e['message'], $e['file'], $e['line']));
        }
    }

    /**
     * Backup la base de données
     *
     * @deprecated utilisé pour Sqlite. Mais on a passé sur MySQL en 2013.
     * @return boolean true si la copie s'est bien déroulée, false sinon
     */
    public static function backupDb() {
        Library_Config::getInstance()->testIssetAuser();
        $path = Library_Config::getInstance()->getData()->path->backup;
        if (!is_dir($path)) {
            mkdir($path, 0777);
        }
        $name = 'db_backup_' . date('Ymd_hi') . '.txt';
        $cp = copy(Library_Config::getInstance()->getData()->db->name, $path . $name);

        Library_Config::log(sprintf(Library_Wording::get('db_backup_done'), $cp));
        return $cp;
    }
    
    
    
    
    
    
    
    
    /**
     * Renomme les thumbs en enlevant le path qui avait été mis dans la 1ère
     * version du programme.
     * 
     * @return void 
     */
    public static function renameThumbs() {
        // migration des images du svn vers le dossier des livres
        $table = new Library_Book();
        $rowset = $table->fetchAll();
        foreach ($rowset as $row) {
            $row->thumb = str_replace('resources/books/', '', $row->thumb);
            $row->save();
        }
    }
    
    /**
     * Régénère les tags de tous les livres en fonction de leur titre, pour
     * pouvoir matcher des recherches avec ou sans accents.
     * 
     * @return void
     */
    public static function regenerateTags() {
        // mise en place des tags du titre en slug
        $table = new Library_Book();
        $rowset = $table->fetchAll();
        foreach ($rowset as $row) {
            $old_tags = array_map('trim', explode(',', $row->tags));
            $new_tags = explode('-', Library_Util::getSlug($row->title));
            $ok_tags = array_unique(array_merge($new_tags, $old_tags));
            $row->tags = implode(',', $ok_tags);
            $row->save();
        }
    }

}