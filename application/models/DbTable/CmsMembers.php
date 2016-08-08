<?php

class Application_Model_DbTable_CmsMembers extends Zend_Db_Table_Abstract
{
    const STATUS_ENABLED = 1;
    const STATUS_DISABLED = 0;

        protected $_name = 'cms_members';
        
        /**
         * 
         * @param int $id
         * @return null|array Associative array with keys as cms_members table columns or NULL if not found
         */
        public function getMemberById($id){
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
         * @param array $member Associative array with keys as colom names and values as colom new values
         */
        public function updateMemberById($id, $member){
            
            if(isset($member['id'])){
                // forbid changing of user id
                unset($member['id']);
            }
            
            $this->update($member, 'id = ' . $id);
            
        }
        
        /**
         * @param array $member Associative array with keys as colom names and values as colom new values
         * @return int The ID of the new created member (autoincrement)
         */
        public function insertMember($member){
            
            $select = $this->select();
            
            // Sort rows by order_number DESCENDING and fetch one row from the top with biggest order number
            $select->order('order_number DESC');
            
            $memberWithBiggestOrderNumber = $this->fetchRow($select);
            
            if($memberWithBiggestOrderNumber instanceof Zend_Db_Table_Row){
                $member['order_number'] = $memberWithBiggestOrderNumber['order_number'] + 1;
            } else {
                // Table was empty, we are inserting first member
                $member['order_number'] = 1;
            }
            
//            Ovo je bio moj kod
//            
//            $select->from($this, array(new Zend_Db_Expr('MAX(order_number) AS maxorder')));
//            
//            $lastM = $this->fetchRow($select);
//            
//            $member['order_number'] = $lastM['maxorder'] + 1;
            
            //fetch order number for new member
            
            $id = $this->insert($member);
            
            return $id;
                    
                    
        }
        
 
        /**
         * 
         * @param array $member Member to delete
         */
         public function deleteMember($member){
            
            $this->update(array('order_number' => new Zend_Db_Expr('order_number -  1')), 'order_number > ' . $member['order_number']); 
             
//            Ovo je bio moj kod
//            
//            $select = $this->select();
//            
//            $on = $member['order_number'];
//            
//            $select->where('order_number > ?', $on);
//            
//            $members = $this->fetchAll($select)->toArray();
//            
//             foreach($members as $m) {
//                 $m['order_number'] = $m['order_number'] - 1;
//                 
//                 $this->update($m, 'id = ' . $m['id']);
//             }
//             
            $this->delete('id=' . $member['id']);
        }
        
         /**
         * 
         * @param int $id ID of member to enable
         */
        public function enableMember($id){
            
             $this->update(array(
                'status' => self::STATUS_ENABLED,
                ), 'id=' . $id);
        }
        
        /**
         * 
         * @param int $id ID of member to disable
         */
        public function disableMember($id){
            
            $this->update(array(
                'status' => self::STATUS_DISABLED,
                ), 'id=' . $id);
        }
        
        public function updateMemberOrder($sortedIds) {
            foreach ($sortedIds as $orderNumber => $id) {
                 $this->update(array(
                'order_number' => $orderNumber +1, // +1 because order number starts from 1 not from 0
                ), 'id=' . $id);
            }
        }
//        Ovo su metode koje sam ja pisao pre ubacivanja search() i count()
//        
//        public function countAll() {
//            $select = $this->select();
//            
//             $select->reset('columns');
//            // set only column/filed to fetch and it is COUNT(*) function
//            $select->from($this->_name, 'COUNT(*) AS total');
//            
//            $row = $this->fetchRow($select);
//            
//            return $row['total'];
//        }
//        
//         public function countActive() {
//            $select = $this->select();
//            
//             $select->reset('columns');
//            // set only column/filed to fetch and it is COUNT(*) function
//            $select->from($this->_name, 'COUNT(*) AS active')
//                    ->where('status = ?', self::STATUS_ENABLED);
//            
//            $row = $this->fetchRow($select);
//            
//            return $row['active'];
//        }
        
        /**
         * Array $parameters is keeping search parameters.
         * Array $parameters must be in following form:
         *      array(
         *          'filters' => array(
         *              'status' => 1,
         *              'id' => array(3, 8, 11)
         *          ),
         *          'orders' => array(
         *              'username' => 'ASC',  // key is column, if value is ASC then ORDER BY ASC
         *              'first_name' => 'DESC',  // key is column, if value is DESC then ORDER BY DESC
         *          ),
         *          'limit' => 50, // limit result to 50 rows
         *          'page' => 3 // start from page 3. If no limit is set, page is ignored.     
         *      )
         * @param array $parameters Asoc array with keys "filters", "orders", "limit" and "page".
         */
        public function search(array $parameters = array()){
            
            $select = $this->select();
            
            if(isset($parameters['filters'])){
                
                $filters = $parameters['filters'];
                
                $this->processFilters($filters, $select);
            }
            
            if(isset($parameters['orders'])){
                $orders = $parameters['orders'];
                
                foreach ($orders as $field => $orderDirection) {
                    
                    switch ($field) {
                        
                        case "id":
                        case "first_name":
                        case "last_name":
                        case "email":
                        case "status":
                        case "order_number": 
                        case "work_title":    
                            
                            if($orderDirection === 'DESC'){
                                $select->order($field . ' DESC');
                            } else{
                                $select->order($field);
                            }
                            
                            break;
                    }
                }
            }
            
            if(isset($parameters['limit'])){
               
               if(isset($parameters['page'])){
                   // page is set do limit by page
                $select->limitPage($parameters['page'], $parameters['limit']); 
               } else {
                   // page is not set, just do regular limit
                   $select->limit($parameters['limit']);
               }
            }
            
//            die($select->assemble());
            return $this->fetchAll($select)->toArray();
            
        }
        
        /**
         * 
         * @param array $filters See function search $parameters['filters']
         * @return int Count of rows that match $filters
         */
        public function count(array $filters = array()) {
            $select = $this->select();
            
            $this->processFilters($filters, $select);
            
            // reset previously set columns for result - Bilo je SELECT * a mi hocemo COUNT(*)
            $select->reset('columns');
            // set only column/filed to fetch and it is COUNT(*) function
            $select->from($this->_name, 'COUNT(*) AS total');
            
            $row = $this->fetchRow($select);
            
            return $row['total'];
            
        }
        
        /**
         * Fill $select object with WHERE conditions
         * @param array $filters
         * @param Zend_Db_Select $select
         */
        protected function processFilters(array $filters, Zend_Db_Select $select){
            
            // $select object will be modified outside this function
            // object are alowed passed by reference
            
            foreach($filters as $field => $value){
                  
                    switch ($field) {
                        
                        case "id":
                        case "first_name":
                        case "last_name":
                        case "email":
                        case "status":
                        case "work_title":
                            
                            if(is_array($value)){
                                $select->where($field . ' IN (?)', $value);
                            } else {
                                $select->where($field . ' = ?', $value);
                            }
                            break;
                            
                        case "first_name_search":   
                            
                            $select->where('first_name LIKE ?', '%' . $value . '%');
                            break;
                        
                        case "last_name_search":   
                            
                            $select->where('last_name LIKE ?', '%' . $value . '%');
                            break;
                        
                        case "email_search":   
                            
                            $select->where('email LIKE ?', '%' . $value . '%');
                            break;
                        
                        case 'id_exclude':
                            
                            if(is_array($value)){
                                $select->where('id NOT IN (?)', $value);
                            } else{
                                $select->where('id != ?', $value);
                            }
                            
                            break;
                         
                    }
                }
        }

        
}

