<?php

class Application_Model_DbTable_CmsUsers extends Zend_Db_Table_Abstract
{
    const STATUS_ENABLED = 1;
    const STATUS_DISABLED = 0;

        protected $_name = 'cms_users';
        
        /**
         * 
         * @param int $id
         * @return null|array Associative array with keys as cms_users table columns or NULL if not found
         */
        public function getUserById($id){
            $select = $this->select();
            $select->where('id = ?', $id);

           $row = $this->fetchRow($select);
           
           if($row instanceof Zend_Db_Table_Row){
               return $row->toArray();
           } else {
               // row is not found
               return null;
           }
        }
        
        /**
         * 
         * @param int $id
         * @param array $user Associative array with keys as colom names and values as colom new values
         */
        public function updateUserById($id, $user){
            
            if(isset($user['id'])){
                // forbid changing of user id
                unset($user['id']);
            }
            
            $this->update($user, 'id = ' . $id);
            
        }
        
        /**
         * 
         * @param int $id
         * @param string $newPassword Plain password, not hashed
         */
        public function changeUserPassword($id, $newPassword){
             
            $this->update(array('password' => md5($newPassword)),'id = ' . $id);
            
        }
}

