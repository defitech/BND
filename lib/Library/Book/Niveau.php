<?php

class Library_Book_Niveau extends Zend_Db_Table_Abstract {

    protected $_primary = array('book_id', 'niveau_id');

    protected $_referenceMap = array(
        'Book' => array(
            'columns' => array('book_id'),
            'refTableClass' => 'Library_Book',
            'refColumns' => array('id')
        ),
        'Niveau' => array(
            'columns' => array('niveau_id'),
            'refTableClass' => 'Library_Niveau',
            'refColumns' => array('id')
        )
    );

}