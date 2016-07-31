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
        
        
        public function countAll() {
            $select = $this->select();
            
             $select->reset('columns');
            // set only column/filed to fetch and it is COUNT(*) function
            $select->from($this->_name, 'COUNT(*) AS total');
            
            $row = $this->fetchRow($select);
            
            return $row['total'];
        }
        
         public function countActive() {
            $select = $this->select();
            
             $select->reset('columns');
            // set only column/filed to fetch and it is COUNT(*) function
            $select->from($this->_name, 'COUNT(*) AS active')
                    ->where('status = ?', self::STATUS_ENABLED);
            
            $row = $this->fetchRow($select);
            
            return $row['active'];
        }

        
}

