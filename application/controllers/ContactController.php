<?php

class ContactController extends Zend_Controller_Action
{

    public function init()
    {
        /* Initialize action controller here */
    }

    public function indexAction()
    {
        // action body
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

