<?php

class AboutusController extends Zend_Controller_Action
{

    public function init()
    {
        /* Initialize action controller here */
    }

    public function indexAction()
    {
        $cmsMembersDbTable = new Application_Model_DbTable_CmsMembers();
        
         
        // Select je objekat klase Zend_Db_Table
        $select = $cmsMembersDbTable->select();
        
        $select->where('status = ?', Application_Model_DbTable_CmsMembers::STATUS_ENABLED)
                ->order('order_number');
        
        // debug za db select - vraca se sql upit
        // die($select->assemble());
        
        
        $members = $cmsMembersDbTable->fetchAll($select);
        
        $this->view->members = $members;
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
        
        
        $member = $foundMembers[0];
        
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

