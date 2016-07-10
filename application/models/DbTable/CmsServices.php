<?php

class Application_Model_DbTable_CmsServices extends Zend_Db_Table_Abstract
{
    const STATUS_ENABLED = 1;
    const STATUS_DISABLED = 0;

        protected $_name = 'cms_services';
        
        /**
         * 
         * @param int $id
         * @return null|array Associative array with keys as cms_services table columns or NULL if not found
         */
        public function getServiceById($id){
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
         * @param array $service Associative array with keys as colom names and values as colom new values
         * @return int The ID of the new created service (autoincrement)
         */
        public function insertService($service){
            
            //fetch order number for new member
            
            $id = $this->insert($service);
            
            return $id;
                     
        }
        
         /**
         * 
         * @param int $id
         * @param array $service Associative array with keys as colom names and values as colom new values
         */
        public function updateServiceById($id, $service){
            
            if(isset($service['id'])){
                // forbid changing of user id
                unset($service['id']);
            }
            
            $this->update($service, 'id = ' . $id);
            
        }
        
        
}

