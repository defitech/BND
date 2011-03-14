<?php

class Library_User_Download extends Zend_Db_Table_Abstract {

    protected $_primary = 'id';

    protected $_referenceMap = array(
        'User' => array(
            'columns' => 'user_id',
            'refTableClass' => 'Library_User',
            'refColumns' => 'id'
        ),
        'Book' => array(
            'columns' => 'book_id',
            'refTableClass' => 'Library_Book',
            'refColumns' => 'id'
        )
    );

}
