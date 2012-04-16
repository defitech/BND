<?php

class Library_User_Controller extends Library_Controller {


    /**
     * --------------------------------------------------------------
     *              Méthodes de gestion de connexion
     * --------------------------------------------------------------
     */


    protected function login() {
        $pass = md5($this->getParam('pass'));
        $user = new Library_User();
        $result = $user->fetchRow($user->select()
            ->where('login = ?', $this->getParam('login'))
            ->where('pass = ?', $pass)
        );

        if ($result) {
            $session = new Zend_Session_Namespace('Library');
            $session->login = $this->getParam('login');
            $session->pass = $pass;

            // on enregistre la date de dernière connection
            $result->last_connected = date('Y-m-d H:i:s');
            $result->save();

            Library_Config::log('connexion');

            return array(
                'success' => true
            );
        }

        return array(
            'success' => false,
            'error' => Library_Wording::get('bad_login')
        );
    }

    protected function logout() {
        $session = new Zend_Session_Namespace('Library');
        if (isset($session->login)) {
            unset($session->login);
        }
        if (isset($session->pass)) {
            unset($session->pass);
        }
        return array(
            'success' => true
        );
    }


    /**
     * --------------------------------------------------------------
     *              Méthodes pour les utilisateurs
     * --------------------------------------------------------------
     */

    protected function getUserList() {
        Library_Config::getInstance()->testIssetAuser(1);
        $table = new Library_User();
        $rowset = $table->fetchAll($table->select()
            ->order('type_id', 'ASC')
            ->order($this->getParam('sort', 'login') . ' ' . $this->getParam('dir', 'ASC'))
        );
        $data = array();
        $types = Library_User_Type::getListToArray();
        foreach ($rowset as $row) {
            $data[] = array_merge($row->toArray(), array(
                'pass' => '',
                'type_text' => isset($types[$row->type_id]) ? $types[$row->type_id] : 'Aucun'
            ));
        }
        return array(
            'success' => true,
            'total' => count($data),
            'users' => $data
        );
    }

    protected function saveUser() {
        Library_Config::getInstance()->testIssetAuser(1);

        $id = $this->getParam('id');
        $table = new Library_User();

        if ($id) {
            $row = $table->fetchRow($table->select()->where('id = ?', $this->getParam('id')));
        } else {
            $row = $table->createRow();
        }

        $field = $this->getParam('field');
        $value = $this->getParam('value');

        $row->$field = $field == 'pass' ? md5($value) : $value;
        $row->save();

        return array(
            'success' => true,
            'id' => $row->id
        );
    }

    protected function removeUser() {
        Library_Config::getInstance()->testIssetAuser(1);
        $id = $this->getParam('id');

        $user = Library_Config::getInstance()->getUser();
        if ($user->id == $id) {
            return array(
                'success' => false,
                'error' => Library_Wording::get('own_deletion_not_allowed')
            );
        }

        if (!$this->getParam('forceConfirm', false)) {
            // check si plusieurs livres on l'élément
            $table = new Library_User_Download();
            $rowset = $table->fetchAll($table
                ->select()
                ->where('user_id = ?', $id)
            );
            if ($rowset->count() > 0) {
                // il y a d'autres livres concernés par cette suppression. On
                // renvoie au navigateur la demande de confirmation
                return array(
                    'success' => true,
                    'confirm' => true,
                    'msg' => sprintf(Library_Wording::get('user_delete_confirm'), $rowset->count()),
                    'nb' => $rowset->count()
                );
            }
        }

        $table = new Library_User();
        $table->delete($table->getAdapter()->quoteInto('id = ?', $this->getParam('id')));

        Library_Config::log(sprintf(Library_Wording::get('user_delete'), $id));
        return array(
            'success' => true
        );
    }
    


    /**
     * --------------------------------------------------------------
     *              Méthodes de gestion des types d'utilisateur
     * --------------------------------------------------------------
     */
    
    protected function getUserTypes() {
        return Library_User_Type::getComboList();
    }
    
    protected function addUserType() {
        Library_Config::getInstance()->testIssetAuser(1);

        $table = new Library_User_Type();

        $row = $table->createRow();

        $row->user_type = $this->getParam('value');
        $row->save();

        return array(
            'success' => true,
            'id' => $row->id,
            'value' => $row->user_type
        );
    }
    
    protected function editUserType() {
        Library_Config::getInstance()->testIssetAuser(1);

        $table = new Library_User_Type();

        $row = $table->fetchRow($table->select()->where('id = ?', $this->getParam('id')));

        $row->user_type = $this->getParam('new_value');
        $row->save();

        return array(
            'success' => true,
            'value' => $row->user_type,
            'id' => $row->id
        );
    }
    
    protected function removeUserType() {
        Library_Config::getInstance()->testIssetAuser(1);
        
        $table = new Library_User_Type();
        $table->delete($table->getAdapter()->quoteInto('id = ?', $this->getParam('id')));

        Library_Config::log(sprintf(Library_Wording::get('user_type_delete'), $this->getParam('id')));
        return array(
            'success' => true,
            'id' => $this->getParam('id')
        );
    }



}