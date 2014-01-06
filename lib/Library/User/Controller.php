<?php

class Library_User_Controller extends Library_Controller {
    
    const MAIL_MSG_CREATE_PASSWORD = 1;
    

    /**
     * --------------------------------------------------------------
     *              Méthodes de gestion de connexion
     * --------------------------------------------------------------
     */


    protected function login() {
        $pass = $this->makeMdp($this->getParam('pass'));
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
        if ($pass && $pass == $this->getParam('pass_confirm')) {
            // check si la personne a bien inséré son bon mot de passe actuel
            $utable = new Library_User();
            $check = $utable->fetchRow($utable->select()->where('id = ?', $user->id)->where('pass = ?', $this->makeMdp($this->getParam('pass_old'))));
            if (!$check) {
                return array(
                    'success' => false,
                    'error' => Library_Wording::get('old_pass_wrong')
                );
            }
            
            $user->pass = $this->makeMdp($pass);
            
            $session = new Zend_Session_Namespace('Library');
            $session->pass = $user->pass;
        }
        
        $user->email = trim($this->getParam('email')) ? $this->getParam('email') : null;
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
            // check si plusieurs livres ont été téléchargé par le user
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
     * Méthode appelée depuis l'administration des users. Permet d'envoyer un
     * mail de création de mot de passe à n'importe quel compte. Le message du
     * mail aura comme contenu qqch qui parle de créer son mot de passe.
     * 
     * @return array
     */
    protected function remindPasswordCreate() {
        Library_Config::getInstance()->testIssetAuser(2);
        if (!$this->getParam('holdmail', false)) {
            $ctrl = $this
                ->setParam('type', self::MAIL_MSG_CREATE_PASSWORD)
                ->remindPassword();

            if ($ctrl['success']) {
                $ctrl['msg'] = Library_Wording::get('mail_sent_create');
            }
        }
        else {
            $data = $this->getUserAndHash();
            if (!$data['success'])
                return $data;

            $ctrl = array(
                'success' => true,
                'msg' => sprintf(Library_Wording::get('mail_notsent_butlink'), $data['user']->login, $this->getHashLink($data['hash']))
            );
        }
        return $ctrl;
    }
    
    /**
     * Méthode appelée au login si on a oublié son mot de passe. Le message
     * du mail envoyé parlera du fait qu'on a oublié son mot de passe et qu'on
     * peut du coup le changer
     * 
     * @return array
     */
    protected function remindPassword() {
        $data = $this->getUserAndHash();
        if (!$data['success'])
            return $data;
        
        $row = $data['user'];
        $hash = $data['hash'];
        
        // création de la connexion au serveur de mail
        $config = Library_Config::getInstance();
        $cmail = $config->getData()->mail;
        if ($cmail->active) {
            $link = $this->getHashLink($hash);
            $tr = new Zend_Mail_Transport_Smtp($cmail->name, $cmail->toArray());
            
            switch ($this->getParam('type', null)) {
                case self::MAIL_MSG_CREATE_PASSWORD:
                    $msg = sprintf(Library_Wording::get('mail_content_create'), $row->login, $link, $row->login, date('d.m.Y, H:i'));
                    $subject = Library_Wording::get('mail_subject_create');
                    break;
                default:
                    $msg = sprintf(Library_Wording::get('mail_content'), $link, $row->login, date('d.m.Y, H:i'));
                    $subject = Library_Wording::get('mail_subject');
            }

            $mail = new Zend_Mail('UTF-8');
            $mail
                ->setFrom($cmail->from)
                ->setReplyTo($cmail->replyTo)
                ->addTo($row->email)
                ->setSubject($subject)
                ->setBodyText($msg);

            $mail->send($tr);
        }
                
        return array(
            'success' => true,
            'msg' => Library_Wording::get('mail_sent')
        );
    }
    
    private function getHashLink($hash) {
        $config = Library_Config::getInstance();
        return $config->getWeb() . '?' . $config->getData()->password->getParamName . '=' . $hash;
    }
    
    private function getUserAndHash() {
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
        if (!$row || !$row->email)
            return array(
                'success' => false,
                'error' => !$row ? Library_Wording::get('mail_error') : Library_Wording::get('mail_nomail')
            );
        
        $config = Library_Config::getInstance();
        
        // génération du hash qui sera le lien de récupération du mdp
        $hash = hash('sha256', sprintf('u:%s;s:%s;d:%s', $row->id, $config->getData()->password->hashSalt, date('Ymd-His')));

        // enregistrement du hash dans la BD
        $htable = new Library_User_PassHash();
        $htable->addHashForUser($row->id, $hash);
        
        return array(
            'success' => true,
            'user' => $row,
            'hash' => $hash
        );
    }
    
    /**
     * Détermine s'il y a une demande pour créer un nouveau mot de passe. Cette
     * méthode de contrôleur est appelée uniquement dans l'index.php. Si elle
     * retourne true, l'index va charger les javascripts pour demander le
     * nouveau mot de passe. Autrement, il ne se passera rien de spécial.
     * 
     * @return array
     */
    protected function checkNewPasswordAsk() {
        $pass_param = Library_Config::getInstance()->getData()->password->getParamName;
        if (!$this->getParam($pass_param, false))
            return array(
                'success' => false
            );
        
        $table = new Library_User_PassHash();
        $row = $table->fetchRow($table->select()->where('hashcode = ?', $this->getParam($pass_param)));
        if (!$row)
            return array(
                'success' => false
            );
        
        $utable = new Library_User();
        $user = $utable->fetchRow($utable->select()->where('id = ?', $row->user_id));
        if (!$user)
            return array(
                'success' => false
            );
        
        return array(
            'success' => true,
            'data' => array(
                'id' => $row->user_id,
                'login' => $user->login,
                'hash' => $row->hashcode
            )
        );
    }
    
    /**¨
     * Change le password après la demande d'un hash et loggue automatiquement
     * la personne au système
     * 
     * @return array
     */
    protected function changePassword() {
        $pass1 = $this->getParam('pass');
        $pass2 = $this->getParam('pass_confirm');
        
        if ($pass1 != $pass2)
            return array(
                'success' => false,
                'error' => "Mot de passe mal réinséré"
            );
        
        $htable = new Library_User_PassHash();
        $hrow = $htable->exists($this->getParam('user'), $this->getParam('hash'));
        if (!$hrow)
            return array(
                'success' => false,
                'error' => "Le changement de mot de passe a échoué. Merci de refaire une demande."
            );
        
        $utable = new Library_User();
        $user = $utable->fetchRow($utable->select()->where('id = ?', $this->getParam('user')));
        if (!$user)
            return array(
                'success' => false,
                'error' => "Le changement de mot de passe a échoué. Merci de refaire une demande."
            );
        
        $user->pass = $this->makeMdp($pass1);
        $user->save();
        
        // suppression du tuple du hash
        $htable->delete($htable->getAdapter()->quoteInto('id = ?', $hrow->id));
        
        // login automatique
        return $this
            ->setParam('login', $user->login)
            ->setParam('pass', $pass1)
            ->login();
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