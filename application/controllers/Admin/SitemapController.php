<?php

class Admin_SitemapController extends Zend_Controller_Action
{
    public function indexAction() {
        
        $request = $this->getRequest();
        
        $flashMessenger = $this->getHelper('FlashMessenger');
        
        $systemMessages = array(
            'success' => $flashMessenger->getMessages('success'),
            'errors' => $flashMessenger->getMessages('errors')
        );
        
        // if no request_id parameter is set, than $parameterID will be 0
        $id = (int) $request->getParam('id', 0);
        
        if($id < 0){
            throw new Zend_Controller_Router_Exception('Invalid id for sitemap pages.', 404);
        }
        
        
        $cmsSitemapDbTable = new Application_Model_DbTable_CmsSitemapPages();
        
        $sitemapPage = $cmsSitemapDbTable->getSitemapPageById($id);
        
        if(!$sitemapPage && $id != 0) {
            throw new Zend_Controller_Router_Exception('No sitemap pages is found', 404);
        }
        
        
        
        $childSitemapPages = $cmsSitemapDbTable->search(array(
            'filters' => array(
                'parent_id' => $id
            ),
            'orders' => array(
                'order_number' => 'ASC'
            ),
//            'limit' => 50,
//            'page' => 3
        ));
        
        $sitemapPageBradcrumbs = $cmsSitemapDbTable->getSitemapPageBreadcrumbs($id);
        
        $this->view->sitemapPageBreadcrumbs = $sitemapPageBradcrumbs;
        $this->view->childSitemapPages = $childSitemapPages;
        $this->view->systemMessages = $systemMessages;
    }
    
}
