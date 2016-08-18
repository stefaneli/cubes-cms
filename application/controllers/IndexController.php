<?php

class IndexController extends Zend_Controller_Action
{

    public function init()
    {
        $cmsIndexSlidesDbTable = new Application_Model_DbTable_CmsIndexSlides();
        
        $enabledSlides = $cmsIndexSlidesDbTable->search(array(
            'filters' => array(
                'status' => Application_Model_DbTable_CmsIndexSlides::STATUS_ENABLED
            ),
            'orders' => array(
                'order_number' => 'ASC'
            ),
//            'limit' => 4,
//            'page' => 2
        ));
        
        $cmsServicesDbTable = new Application_Model_DbTable_CmsServices();
        
        $services = $cmsServicesDbTable->search(array(
            'filters' => array(
                'status' => Application_Model_DbTable_CmsServices::STATUS_ENABLED
            ),
            'orders' => array(
                'order_number' => 'ASC'
            ),
            'limit' => 4
        ));
        
        $cmsSitemapDbTable = new Application_Model_DbTable_CmsSitemapPages();
        
        $servicesSitemapPages = $cmsSitemapDbTable->search(array(
            'filters' => array(
                'status' => Application_Model_DbTable_CmsSitemapPages::STATUS_ENABLED,
                'type' => 'ServicesPage'
            ),
            'limit' => 1
        ));
        
        $servicesSitemapPage = !empty($servicesSitemapPages) ? $servicesSitemapPages[0] : null;
        

        $this->view->enabledSlides = $enabledSlides;
        $this->view->services = $services;
        $this->view->sitemapServices = $servicesSitemapPage;
    }

    public function indexAction()
    {
       $cmsClientsDbTable = new Application_Model_DbTable_CmsClients();
        
         
        // Select je objekat klase Zend_Db_Table
        $select = $cmsClientsDbTable->select();
        
        $select->where('status = ?', Application_Model_DbTable_CmsClients::STATUS_ENABLED)
                ->order('order_number');
        
        // debug za db select - vraca se sql upit
        // die($select->assemble());
        
        
        $clients = $cmsClientsDbTable->fetchAll($select);
        
        $this->view->clients = $clients;
    }

    public function testAction(){
    
}

}

