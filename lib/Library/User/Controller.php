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
        $gridFilters = $this->getParam('filter', array());
        $table = new Library_User();
        
        $select = $table->select()
            ->order('type_id', 'ASC')
            ->order($this->getParam('sort', 'login') . ' ' . $this->getParam('dir', 'ASC'));
        
        // ajout des filtres grid s'il y en a
        foreach ($gridFilters as $filter) {
            switch ($filter['data']['type']) {
                case 'string':
                    $select->where('' . $filter['field'] . ' LIKE "%' . $filter['data']['value'] . '%"');
                    break;
            }
        }
        
        $rowset = $table->fetchAll($select);
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

        $row->$field = $field == 'pass' ? $this->makeMdp($value) : ($field == 'email' && !trim($value) ? null : $value);
        $row->save();

        return array(
            'success' => true,
            'id' => $row->id
        );
    }
    
    private function makeMdp($pass) {
        return md5($pass);
    }
    
    protected function getUser() {
        Library_Config::getInstance()->testIssetAuser();
        
        $config = Library_Config::getInstance();
        $user = $config->getUser();
        
        $data = $user->toArray();
        unset($data['pass']);
        unset($data['right']);
        
        return array(
            'success' => true,
            'record' => $data
        );
    }
    
    protected function saveCurrentUser() {
        Library_Config::getInstance()->testIssetAuser();
        
        $config = Library_Config::getInstance();
        $user = $config->getUser();
        
        $pass = trim($this->getParam('pass'));
        if ($pass)
            $user->pass = $this->makeMdp($pass);
        
        $user->email = trim($this->getParam('email'));
        $user->save();
        
        return array(
            'success' => true
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
    
    protected function remindPassword() {
        // récupération du mail à qui on veut envoyer le mdp. On peut soit
        // recevoir un mail, soit un login. On vérifie respectivement que
        // le mail existe dans la base ou que le login a un mail associé.
        $str = $this->getParam('askforpass');
        $table = new Library_User();
        $field = 'email';
        if (strpos($str, '@') === false) {
            // ce n'est pas un mail, on va chercher le login
            $field = 'login';
        }
        $row = $table->fetchRow($table->select()->where($field . ' = ?', $str));
        if (!$row || !$row->email) {
            return array(
                'success' => false,
                'error' => "Personne n'a de login ou de mail ainsi que vous l'avez écrit"
            );
        }
        
        // création de la connexion au serveur de mail
        $config = Library_Config::getInstance();
        $cmail = $config->getData()->mail;
        $tr = new Zend_Mail_Transport_Smtp($cmail->name, $cmail->toArray());
        
        // set des valeurs par défaut et envoi du mail
        Zend_Mail::setDefaultTransport($tr);
        Zend_Mail::setDefaultFrom($cmail->from);
        Zend_Mail::setDefaultReplyTo($cmail->replyTo);
    
        $mail = new Zend_Mail('UTF-8');
        $mail->addTo($row->email);
        $mail->setSubject(
            'BND > Defitech > Récupération de mot de passe'
        );
        $mail->setBodyText("Bonjour, voici votre nouveau mot de passe temporaire: " . $this->generateRandomString());
        $mail->send();
                
        Zend_Mail::clearDefaultTransport();
        Zend_Mail::clearDefaultFrom();
        Zend_Mail::clearDefaultReplyTo();
        
        return array(
            'success' => true
        );
    }
    
    private function generateRandomString($length = 10) {
        $characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
        $randomString = '';
        for ($i = 0; $i < $length; $i++) {
            $randomString .= $characters[rand(0, strlen($characters) - 1)];
        }
        return $randomString;
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

        $row->user_type = $this->getParam('value');
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