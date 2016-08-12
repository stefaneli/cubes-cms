<?php

class AboutusController extends Zend_Controller_Action
{

    public function init()
    {
        /* Initialize action controller here */
    }

    public function indexAction()
    {
        $request = $this->getRequest();
        
        $sitemapPageId = (int) $request->getParam('sitemap_page_id');
        
        if($sitemapPageId <= 0) {
            throw new Zend_Controller_Router_Exception('Invalid sitemap page id: ' . $sitemapPageId, 404);
        }
        
        $cmsSitemapPageDbTable = new Application_Model_DbTable_CmsSitemapPages();
        
        $sitemapPage = $cmsSitemapPageDbTable->getSitemapPageById($sitemapPageId);
        
        if(!$sitemapPage){
            throw new Zend_Controller_Router_Exception('No sitemap page is found for id: ' . $sitemapPageId, 404);
        }
        
        if(
           $sitemapPage['status'] == Application_Model_DbTable_CmsSitemapPages::STATUS_DISABLED
           //check if user is not looged in than preview is not available for displayed page
           && !Zend_Auth::getInstance()->hasIdentity()
        ) {
            throw new Zend_Controller_Router_Exception('Sitemap page is disabled.', 404);
        }
       
        
        $cmsMembersDbTable = new Application_Model_DbTable_CmsMembers();
        
        $members = $cmsMembersDbTable->search(array(
            'filters' => array(
                'status' => Application_Model_DbTable_CmsMembers::STATUS_ENABLED
            ),
            'orders' => array(
                'order_number' => 'ASC'
            ),
//            'limit' => 4,
//            'page' => 2
        ));
        
//        $cmsMembersDbTable = new Application_Model_DbTable_CmsMembers();
//        
//         
//        // Select je objekat klase Zend_Db_Table
//        $select = $cmsMembersDbTable->select();
//        
//        $select->where('status = ?', Application_Model_DbTable_CmsMembers::STATUS_ENABLED)
//                ->order('order_number');
//        
//        // debug za db select - vraca se sql upit
//        // die($select->assemble());
//        
//        
//        $members = $cmsMembersDbTable->fetchAll($select);
        
        $this->view->members = $members;
//        
        $this->view->sitemapPage = $sitemapPage;
    }
    
    public function memberAction(){
        $request = $this->getRequest();
        
        $id = (int) $request->getParam('id');
        
        if(empty($id)){
            throw new Zend_Controller_Router_Exception('No member id', 404);
        }
        
        $cmsMembersDbTable = new Application_Model_DbTable_CmsMembers();
       
        $select = $cmsMembersDbTable->select();
        
        $select->where('id = ?', $id)
                ->where('status = ?', Application_Model_DbTable_CmsMembers::STATUS_ENABLED);
        
        $foundMembers = $cmsMembersDbTable->fetchAll($select);
        
        if(count($foundMembers) <= 0){
            throw new Zend_Controller_Router_Exception('No member is found for id' . $id, 404);
        }
        
       // $memberSlug = $request->getParam('member_slug');
        
        $member = $foundMembers[0];
        
//         if(empty($memberSlug)) {
//            
//            $redirector = $this->getHelper('Redirector');
//            $redirector->setExit(true)
//                    ->gotoRoute(array(
//                        'id' => $member['id'],
//                        'member_slug' => $member['first_name'] . '-' . $member['last_name']
//                            ), 'member-route', true);
//        }
        
        // Fetching all other members
        
        $select = $cmsMembersDbTable->select();
        $select->where('status = ?', Application_Model_DbTable_CmsMembers::STATUS_ENABLED)
                ->where('id != ?', $id)
                ->order('order_number');
                
        
        $members = $cmsMembersDbTable->fetchAll($select);
        
        $this->view->members = $members;
        
        $this->view->member = $member;
    }

}

