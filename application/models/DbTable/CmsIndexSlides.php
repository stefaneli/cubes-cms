<?php

class Application_Model_DbTable_CmsIndexSlides extends Zend_Db_Table_Abstract
{
    const STATUS_ENABLED = 1;
    const STATUS_DISABLED = 0;

        protected $_name = 'cms_index_slides';
        
        /**
         * 
         * @param int $id
         * @return null|array Associative array with keys as cms_indexSlides table columns or NULL if not found
         */
        public function getIndexSlideById($id){
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
         * @param array $indexSlide Associative array with keys as colom names and values as colom new values
         */
        public function updateIndexSlideById($id, $indexSlide){
            
            if(isset($indexSlide['id'])){
                // forbid changing of user id
                unset($indexSlide['id']);
            }
            
            $this->update($indexSlide, 'id = ' . $id);
            
        }
        
        /**
         * @param array $indexSlide Associative array with keys as colom names and values as colom new values
         * @return int The ID of the new created indexSlide (autoincrement)
         */
        public function insertIndexSlide($indexSlide){
            
            $select = $this->select();
            
            // Sort rows by order_number DESCENDING and fetch one row from the top with biggest order number
            $select->order('order_number DESC');
            
            $indexSlideWithBiggestOrderNumber = $this->fetchRow($select);
            
            if($indexSlideWithBiggestOrderNumber instanceof Zend_Db_Table_Row){
                $indexSlide['order_number'] = $indexSlideWithBiggestOrderNumber['order_number'] + 1;
            } else {
                // Table was empty, we are inserting first indexSlide
                $indexSlide['order_number'] = 1;
            }
            
//            Ovo je bio moj kod
//            
//            $select->from($this, array(new Zend_Db_Expr('MAX(order_number) AS maxorder')));
//            
//            $lastM = $this->fetchRow($select);
//            
//            $indexSlide['order_number'] = $lastM['maxorder'] + 1;
            
            //fetch order number for new indexSlide
            
            $id = $this->insert($indexSlide);
            
            return $id;
                    
                    
        }
        
 
        /**
         * 
         * @param array $indexSlide IndexSlide to delete
         */
         public function deleteIndexSlide($indexSlide){
            
            $this->update(array('order_number' => new Zend_Db_Expr('order_number -  1')), 'order_number > ' . $indexSlide['order_number']); 
             
//            Ovo je bio moj kod
//            
//            $select = $this->select();
//            
//            $on = $indexSlide['order_number'];
//            
//            $select->where('order_number > ?', $on);
//            
//            $indexSlides = $this->fetchAll($select)->toArray();
//            
//             foreach($indexSlides as $m) {
//                 $m['order_number'] = $m['order_number'] - 1;
//                 
//                 $this->update($m, 'id = ' . $m['id']);
//             }
//             
            $this->delete('id=' . $indexSlide['id']);
        }
        
         /**
         * 
         * @param int $id ID of indexSlide to enable
         */
        public function enableIndexSlide($id){
            
             $this->update(array(
                'status' => self::STATUS_ENABLED,
                ), 'id=' . $id);
        }
        
        /**
         * 
         * @param int $id ID of indexSlide to disable
         */
        public function disableIndexSlide($id){
            
            $this->update(array(
                'status' => self::STATUS_DISABLED,
                ), 'id=' . $id);
        }
        
        public function updateIndexSlideOrder($sortedIds) {
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
                        case "title":
                        case "link_type":
                        case "status":
                        case "order_number":  
                            
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
                        case "title":
                        case "link_type":
                        case "status":
                            
                            if(is_array($value)){
                                $select->where($field . ' IN (?)', $value);
                            } else {
                                $select->where($field . ' = ?', $value);
                            }
                            break;
                            
                        case "title_search":   
                            
                            $select->where('title LIKE ?', '%' . $value . '%');
                            break;
                        
                        case "description_search":   
                            
                            $select->where('description LIKE ?', '%' . $value . '%');
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

