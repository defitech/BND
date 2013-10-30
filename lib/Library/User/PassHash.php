<?php

class Library_User_PassHash extends Zend_Db_Table_Abstract {

    protected $_primary = 'id';

    protected $_name = 'library_user_passhash';

    protected $_dependentTables = array('Library_User');
    
    /**
     * Ajoute un hash pour ce user. Si le hash existe déjà, on le remplace
     * 
     * @param int $userId
     * @param string $hash
     * @return void
     */
    public function addHashForUser($userId, $hash) {
        $hashunit = $this->fetchRow($this->select()->where('user_id = ?', $userId));
        if (!$hashunit) {
            $hashunit = $this->createRow();
            $hashunit->user_id = $userId;
        }
        $hashunit->hashcode = $hash;
        $hashunit->save();
    }
    
    /**
     * Vérifie si le couple user_id + hash existe bien dans la bd
     * 
     * @paran int $userId
     * @param string $hash
     * @return bool|Library_User_PassHash
     */
    public function exists($userId, $hash) {
        $hashunit = $this->fetchRow($this
            ->select()
            ->where('user_id = ?', $userId)
            ->where('hashcode = ?', $hash)
        );
        
        return $hashunit;
    }

}
