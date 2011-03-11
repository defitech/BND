<?php

class Library_Wording {

    public static function get($word) {

        $wording = Fr::$wording;

        return isset($wording[$word]) ? $wording[$word] : '';
    }

}
