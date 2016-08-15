<?php

class ContactController extends Zend_Controller_Action
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
          
        $this->view->sitemapPage = $sitemapPage;
    }
    
    public function askmemberAction() {
        
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
        
        $member = $foundMembers[0];
       
        $this->view->member = $member;
        
    }


}

