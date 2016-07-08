<?php

class Admin_MembersController extends Zend_Controller_Action
{
    public function indexAction(){
        
        $flashMessenger = $this->getHelper('FlashMessenger');
        
        $systemMessages = array(
            'success' => $flashMessenger->getMessages('success'),
            'errors' => $flashMessenger->getMessages('errors')
        );
        
        // Prikaz svih membera
        
        $cmsMembersDbTable = new Application_Model_DbTable_CmsMembers();
        
         
        // Select je objekat klase Zend_Db_Table
        $select = $cmsMembersDbTable->select();
        
        $select->order('order_number');
        
        // debug za db select - vraca se sql upit
        // die($select->assemble());
        
        
        $members = $cmsMembersDbTable->fetchAll($select);
        
        $this->view->members = $members;
        $this->view->systemMessages = $systemMessages;
                
    }
    
    public function addAction() {
        
        $request = $this->getRequest();
        
        $flashMessenger = $this->getHelper('FlashMessenger');
        
          $systemMessages = array(
            'success' => $flashMessenger->getMessages('success'),
            'errors' => $flashMessenger->getMessages('errors')
        );

        $form = new Application_Form_Admin_MemberAdd();

        //default form data
        $form->populate(array(
            
        ));

      

        if ($request->isPost() && $request->getPost('task') === 'save') {

            try {

                //check form is valid
                if (!$form->isValid($request->getPost())) {
                    throw new Application_Model_Exception_InvalidInput('Invalid data was sent for new member.');
                }

                //get form data
                $formData = $form->getValues();
                
                // do actual task
                //save to database etc
             
                 // Insertujemo novi zapis u tabelu
                $cmsMembersTable = new Application_Model_DbTable_CmsMembers();
                
                $cmsMembersTable->insertMember($formData);
                
                
                //set system message
                $flashMessenger->addMessage('Member has been saved', 'success');

                //redirect to same or another page
                $redirector = $this->getHelper('Redirector');
                $redirector->setExit(true)
                        ->gotoRoute(array(
                            'controller' => 'admin_members',
                            'action' => 'index',
                            'param1' => $param1Value
                                ), 'default', true);
            } catch (Application_Model_Exception_InvalidInput $ex) {
                $systemMessages['errors'][] = $ex->getMessage();
            }
        }

        $this->view->systemMessages = $systemMessages;
        $this->view->form = $form;
    }
    
    public function editAction() {
        
        $request = $this->getRequest();
        
        $id = (int) $request->getParam('id');
        
        if($id <= 0){
            
            // Prekida se izvrsavanje programa i prikazuje se page not found
            throw  new Zend_Controller_Router_Exception('Invalid member id: ' . $id, 404);
        }
        
        $cmsMembersTable = new Application_Model_DbTable_CmsMembers();
        
        $member = $cmsMembersTable->getMemberById($id);
        
        if(empty($member)){
            throw new Zend_Controller_Router_Exception('No member is found with id: ' . $id, 404);
        }
        
        $flashMessenger = $this->getHelper('FlashMessenger');
        
        $systemMessages = array(
            'success' => $flashMessenger->getMessages('success'),
            'errors' => $flashMessenger->getMessages('errors')
        );

        $form = new Application_Form_Admin_MemberAdd();

        //default form data
        $form->populate($member);

      

        if ($request->isPost() && $request->getPost('task') === 'update') {

            try {

                //check form is valid
                if (!$form->isValid($request->getPost())) {
                    throw new Application_Model_Exception_InvalidInput('Invalid data was sent for member.');
                }

                //get form data
                $formData = $form->getValues();
                
                // do actual task
                //save to database etc
             
                 // Update postojeceg zapisa u tabeli
                
                $cmsMembersTable->updateMemberById($member['id'], $formData);
                
                
                //set system message
                $flashMessenger->addMessage('Member has been updated', 'success');

                //redirect to same or another page
                $redirector = $this->getHelper('Redirector');
                $redirector->setExit(true)
                        ->gotoRoute(array(
                            'controller' => 'admin_members',
                            'action' => 'index'
                                ), 'default', true);
            } catch (Application_Model_Exception_InvalidInput $ex) {
                $systemMessages['errors'][] = $ex->getMessage();
            }
        }

        $this->view->systemMessages = $systemMessages;
        $this->view->form = $form;
        
        $this->view->member = $member;
        
        
    }

}
