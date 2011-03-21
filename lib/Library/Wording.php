<?php

class Library_Wording {

    public static function get($word) {

        include_once 'Fr.php';
        $wording = Fr::$wording;

        return isset($wording[$word]) ? $wording[$word] : $word;
    }

}
