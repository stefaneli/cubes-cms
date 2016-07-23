<?php

class Application_Model_DbTable_CmsClients extends Zend_Db_Table_Abstract
{
    const STATUS_ENABLED = 1;
    const STATUS_DISABLED = 0;

        protected $_name = 'cms_clients';
        
        /**
         * 
         * @param int $id
         * @return null|array Associative array with keys as cms_users table columns or NULL if not found
         */
        public function getClientById($id){
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
         * @param array $client Associative array with keys as colom names and values as colom new values
         */
        public function updateClientById($id, $client){
            
            if(isset($client['id'])){
                // forbid changing of user id
                unset($client['id']);
            }
            
            $this->update($client, 'id = ' . $id);
            
        }
        
        /**
         * @param array $client Associative array with keys as colom names and values as colom new values
         * @return int The ID of the new created client (autoincrement)
         */
        public function insertClient($client){
            
            $select = $this->select();
            
            // Sort rows by order_number DESCENDING and fetch one row from the top with biggest order number
            $select->order('order_number DESC');
            
            $clientWithBiggestOrderNumber = $this->fetchRow($select);
            
            if($clientWithBiggestOrderNumber instanceof Zend_Db_Table_Row){
                $client['order_number'] = $clientWithBiggestOrderNumber['order_number'] + 1;
            } else {
                // Table was empty, we are inserting first member
                $client['order_number'] = 1;
            }
            
            $id = $this->insert($client);
            
            return $id;
                    
                    
        }
        
 
        /**
         * 
         * @param array $client Client to delete
         */
         public function deleteClient($client){
            
            $this->update(array('order_number' => new Zend_Db_Expr('order_number -  1')), 'order_number > ' . $client['order_number']); 
            
            $this->delete('id=' . $client['id']);
        }
        
         /**
         * 
         * @param int $id ID of client to enable
         */
        public function enableClient($id){
            
             $this->update(array(
                'status' => self::STATUS_ENABLED,
                ), 'id=' . $id);
        }
        
        /**
         * 
         * @param int $id ID of client to disable
         */
        public function disableClient($id){
            
            $this->update(array(
                'status' => self::STATUS_DISABLED,
                ), 'id=' . $id);
        }
        
        public function updateClintOrder($sortedIds) {
            foreach ($sortedIds as $orderNumber => $id) {
                 $this->update(array(
                'order_number' => $orderNumber + 1, // +1 because order number starts from 1 not from 0
                ), 'id=' . $id);
            }
        }
}

