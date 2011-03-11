<?php

class Defitech_Util {

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
       $Stop = array_merge($Stop, array('_', '#', '~', '|', '^', '@', '¨', '$', '�ç�', '£'));

       $Str = str_replace($Stop, ' ', $Str);

       # Remplacement espace multiple
       return ereg_replace(' +', $separator, trim($Str));
    }

}