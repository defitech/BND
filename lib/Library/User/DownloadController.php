<?php

class Library_User_DownloadController extends Library_Controller {


    /**
     * --------------------------------------------------------------
     *        Méthodes pour les téléchargements des utilisateurs
     * --------------------------------------------------------------
     */

    protected function getUserDownloadList() {
        Library_Config::getInstance()->testIssetAuser(1);
        $items = array();
        $table = Zend_Registry::get('db');
        $rowset = $table->fetchAll($table
            ->select()
            ->from(array('b' => 'library_book'), array('btitle' => 'b.title', 'bid' => 'b.id'))
            ->join(array('d' => 'library_user_download'), 'd.book_id = b.id', array('nb' => 'COUNT(*)'))
            ->where('d.user_id = ?', $this->getParam('user_id'))
            ->group('d.book_id')
        );

        foreach ($rowset as $row) {
            $items[] = array(
                'id' => $row['bid'],
                'title' => $row['btitle'],
                'nb' => $row['nb']
            );
        }
        return array(
            'success' => true,
            'items' => $items
        );
    }

    protected function exportUserDownloadCsv() {
        header('Content-Type: text/csv');
        Library_Config::getInstance()->testIssetAuser(1);

        $table = Zend_Registry::get('db');
        $rowset = $table->fetchAll($table
            ->select()
            ->from(array('b' => 'library_book'), array('btitle' => 'b.title', 'bid' => 'b.id'))
            ->join(array('d' => 'library_user_download'), 'd.book_id = b.id', array('nb' => 'COUNT(*)'))
            ->where('d.user_id = ?', $this->getParam('user_id'))
            ->group('d.book_id')
        );

        $t = new Library_User();
        $user = $t->fetchRow($t->select()->where('id = ?', $this->getParam('user_id')));

        $items = array(
            array('user id', 'user login', 'livre id', 'livre titre', 'nb downloads')
        );
        foreach ($rowset as $row) {
            $items[] = array(
                $user->id, '"' . $user->login .'"', $row['bid'],
                '"'. str_replace('"', "'", $row['btitle']) .'"', $row['nb']
            );
        }

        header('Expires: 0');
        header('Cache-control: private');
        header('Cache-Control: must-revalidate, post-check=0, pre-check=0');
        header('Content-Description: File Transfer');
        header('Content-Type: application/csv');
        header('Content-disposition: attachment; filename=' . $user->login . '.csv');

        $str = '';
        foreach ($items as $item) {
            $str .= implode(',', $item) . PHP_EOL;
        }

        echo $str;
        exit;
    }
    
    protected function exportBooksDownloadCsv() {
        header('Content-Type: text/csv');
        Library_Config::getInstance()->testIssetAuser(1);

        $table = Zend_Registry::get('db');
        $rowset = $table->fetchAll($table
            ->select()
            ->from(array('b' => 'library_book'), array('btitle' => 'b.title', 'bid' => 'b.id', 'eid' => 'b.editor_id'))
            ->join(array('d' => 'library_user_download'), 'd.book_id = b.id', array('nb' => 'COUNT(*)'))
            ->group('d.book_id')
        );
        
        $editors = Library_Book_Editor::getListToArray();
        
        $items = array(
            array('livre id', 'livre titre', 'editeur', 'nb downloads')
        );
        foreach ($rowset as $row) {
            $editor = str_replace("\n", "", isset($editors[$row['eid']]) ? $editors[$row['eid']] : $row['eid']);
            
            $items[] = array(
                $row['bid'],
                '"'. str_replace('"', "'", $row['btitle']) .'"',
                $editor,
                $row['nb']
            );
        }

        $name = 'bnd-books-stat-' . date('Ymd-His');
        header('Expires: 0');
        header('Cache-control: private');
        header('Cache-Control: must-revalidate, post-check=0, pre-check=0');
        header('Content-Description: File Transfer');
        header('Content-Type: application/csv');
        header('Content-disposition: attachment; filename=' . $name . '.csv');

        $str = '';
        foreach ($items as $item) {
            $str .= implode(',', $item) . PHP_EOL;
        }

        echo $str;
        exit;
    }


}