<?php

class Application_Model_DbTable_CmsSitemapPages extends Zend_Db_Table_Abstract
{
    const STATUS_ENABLED = 1;
    const STATUS_DISABLED = 0;
    
    protected $_name = 'cms_sitemap_pages';
    
    protected static $sitemapPagesMap;
    
    /**
     * 
     * @return array With keys as sitemap page ids and values as assoc. array with keys url and type
     */
    public static function getSitemapPagesMap() {
        
        // lazy loading metod
        
        if(!self::$sitemapPagesMap){
             
            $sitemapPagesMap = array();

            $cmsSitemapPagesDbTable = new self();

            // same as $cmsSitemapPagesDbTable = new Application_Model_DbTable_CmsSitemapPages();

            $sitemapPages = $cmsSitemapPagesDbTable->search(array(
                'orders' => array(
                    'parent_id' => 'ASC',
                    'order_number' => 'ASC'
                )
            ));

            foreach ($sitemapPages as $sitemapPage) {
                $type = $sitemapPage['type'];
                $url = $sitemapPage['url_slug'];

                if(isset($sitemapPagesMap[$sitemapPage['parent_id']])){
                    $url = $sitemapPagesMap[$sitemapPage['parent_id']]['url'] . '/' . $url;
                }

                $sitemapPagesMap[$sitemapPage['id']] = array(
                    'url' => $url,
                    'type' => $type
                );
            }

            self::$sitemapPagesMap = $sitemapPagesMap;  

        }
        
        return self::$sitemapPagesMap;
         
        
    }

    

    /**
         * 
         * @param int $id
         * @return null|array Associative array with keys as cms_sitemap_pages table columns or NULL if not found
         */
        public function getSitemapPageById($id){
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
         * @param array $sitemapPage Associative array with keys as colom names and values as colom new values
         */
        public function updateSitemapPageById($id, $sitemapPage){
            
            if(isset($sitemapPage['id'])){
                // forbid changing of user id
                unset($sitemapPage['id']);
            }
            
            $this->update($sitemapPage, 'id = ' . $id);
            
        }
        
        /**
         * @param array $sitemapPage Associative array with keys as colom names and values as colom new values
         * @return int The ID of the new created sitemapPage (autoincrement)
         */
        public function insertSitemapPage($sitemapPage){
            
            $select = $this->select();
            
            // Sort rows by order_number DESCENDING and fetch one row from the top with biggest order number
            $select->where('parent_id = ?', $sitemapPage['parent_id'])
                    ->order('order_number DESC');
            
            $sitemapPageWithBiggestOrderNumber = $this->fetchRow($select);
            
            if($sitemapPageWithBiggestOrderNumber instanceof Zend_Db_Table_Row){
                $sitemapPage['order_number'] = $sitemapPageWithBiggestOrderNumber['order_number'] + 1;
            } else {
                // Table was empty, we are inserting first sitemapPage
                $sitemapPage['order_number'] = 1;
            }
            
            $id = $this->insert($sitemapPage);
            
            return $id;
                    
                    
        }
        
 
        /**
         * 
         * @param int $id id of SitemapPage to delete
         */
         public function deleteSitemapPage($id){
             
               $sitemapPage = $this->getSitemapPageById($id);
        
        
                $childSitemapPages = $this->search(
                array(
                    'filters' => array('parent_id' => $id)
                    
              )
            );
            
            if (!empty($childSitemapPages)){
                
                foreach ($childSitemapPages as $childSitemapPage){
                   
                    $this->deleteSitemapPage($childSitemapPage['id']);
                }
                
            }
            
        $this->update(array(
            'order_number'=> new Zend_Db_Expr('order_number - 1')
        ),
         'order_number > ' . $sitemapPage['order_number'] . ' AND parent_id = ' . $sitemapPage['parent_id']);
        
        $this->delete('id = ' . $id);     
        }
        
         /**
         * 
         * @param int $id ID of sitemapPage to enable
         */
        public function enableSitemapPage($id){
            
             $this->update(array(
                'status' => self::STATUS_ENABLED,
                ), 'id=' . $id);
        }
        
        /**
         * 
         * @param int $id ID of sitemapPage to disable
         */
        public function disableSitemapPage($id){
            
            $this->update(array(
                'status' => self::STATUS_DISABLED,
                ), 'id=' . $id);
        }
        
        public function updateSitemapPageOrder($sortedIds) {
            
            foreach ($sortedIds as $orderNumber => $id) {
                 $this->update(array(
                'order_number' => $orderNumber +1, // +1 because order number starts from 1 not from 0
                ), 'id=' . $id);
            }
        }
        
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
                        case "short_title":
                        case "url_slug":
                        case "title":
                        case "parent_id":
                        case "type":
                        case "order_number":    
                        case "status":
                            
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
                        case "short_title":
                        case "url_slug":
                        case "title":
                        case "parent_id":
                        case "type":
                        case "order_number":    
                        case "status":
                            
                            if(is_array($value)){
                                $select->where($field . ' IN (?)', $value);
                            } else {
                                $select->where($field . ' = ?', $value);
                            }
                            break;
                        
                        case "short_title_search":   
                            
                            $select->where('short_title LIKE ?', '%' . $value . '%');
                            break;
                        
                        case "title_search":   
                            
                            $select->where('title LIKE ?', '%' . $value . '%');
                            break;
                        
                        case "description_search":   
                            
                            $select->where('description LIKE ?', '%' . $value . '%');
                            break;
                        
                        case "body_search":   
                            
                            $select->where('body LIKE ?', '%' . $value . '%');
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
        
        /**
         * 
         * @param int $id Id of the sitemap page
         * @return array Sitemap page rows in path
         */
        public function getSitemapPageBreadcrumbs($id) {
            
            $sitemapPageBreadcrumbs = array();
           
            while ($id > 0) {
               
                $sitemapPageInPath = $this->getSitemapPageById($id);
                
                if($sitemapPageInPath) {
                    
                    $id = $sitemapPageInPath['parent_id'];
                    
                    array_unshift($sitemapPageBreadcrumbs, $sitemapPageInPath);
                    
                } else {
                    $id = 0;
                }
            }
            
            
            return $sitemapPageBreadcrumbs;
        }
        
        /**
         * Returns count by type, example:
         * array(
         *      'StaticPage' => 3,
         *      'AboutUsPage' => 1,
         *      'ContactPage' => 1,
         *      ...
         *      );
         * @param type $filters
         * @return array Count by type
         */
        public function countByTypes($filters = array()) {
            
            $select = $this->select();
            
            $this->processFilters($filters, $select);
            
            // reset previously set columns for result - Bilo je SELECT * a mi hocemo COUNT(*)
            $select->reset('columns');
            // set only column/filed to fetch and it is COUNT(*) function
            $select->from($this->_name, array(
                'type',
                'COUNT(*) AS total_by_type'
            ));
            
            $select->group('type');
            
            
            $rows = $this->fetchAll($select);
            
            $countByTypes = array();
            
            foreach ($rows as $row) {
                $countByTypes[$row['type']] = $row['total_by_type'];
            }
            
            return $countByTypes;
        }
    
}
