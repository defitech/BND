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

    protected function exportUserDownloadCav() {
        header('Content-Type: text/plain');
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


}