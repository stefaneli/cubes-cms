<?php

class Admin_ClientsController extends Zend_Controller_Action
{
    public function indexAction(){
        
        $flashMessenger = $this->getHelper('FlashMessenger');
        
        $systemMessages = array(
            'success' => $flashMessenger->getMessages('success'),
            'errors' => $flashMessenger->getMessages('errors')
        );
        
        // Prikaz svih membera
        
        $cmsClientsDbTable = new Application_Model_DbTable_CmsClients();
        
         
        // Select je objekat klase Zend_Db_Table
        $select = $cmsClientsDbTable->select();
        
        $select->order('order_number');
        
        // debug za db select - vraca se sql upit
        // die($select->assemble());
        
        
        $clients = $cmsClientsDbTable->fetchAll($select);
        
        $this->view->clients = $clients;
        $this->view->systemMessages = $systemMessages;
                
    }
    
    public function addAction() {
        
        $request = $this->getRequest();
        
        $flashMessenger = $this->getHelper('FlashMessenger');
        
          $systemMessages = array(
            'success' => $flashMessenger->getMessages('success'),
            'errors' => $flashMessenger->getMessages('errors')
        );

        $form = new Application_Form_Admin_ClientAdd();

        //default form data
        $form->populate(array(
            
        ));

      

        if ($request->isPost() && $request->getPost('task') === 'save') {

            try {

                //check form is valid
                if (!$form->isValid($request->getPost())) {
                    throw new Application_Model_Exception_InvalidInput('Invalid data was sent for new client.');
                }

                //get form data
                $formData = $form->getValues();
                
                // remove key member_photo from data because there is no column 'member_photo' in cms_members table
                unset($formData['client_photo']);
               
                 // Insertujemo novi zapis u tabelu
                $cmsCleintTable = new Application_Model_DbTable_CmsClients();
                
                // insert member returns ID of the new member
                $clientId = $cmsCleintTable->insertClient($formData);
                
                
                
                if($form->getElement('client_photo')->isUploaded()){
                
                    // photo is uploaded
                    
                    $fileInfos = $form->getElement('client_photo')->getFileInfo('client_photo');
                    $fileInfo = $fileInfos['client_photo'];
                    // $fileInfo = $_FILES['member_phpto'];
                    
                    try{
                        // Open uploaded photo in temporary directory
                        $clientPhoto = Intervention\Image\ImageManagerStatic::make($fileInfo['tmp_name']);
                        
                        $clientPhoto->fit(170, 70);
                        
                        $clientPhoto->save(PUBLIC_PATH . '/uploads/clients/' . $clientId . '.jpg');
                        
                        
                    } catch (Exception $ex) {
                        $flashMessenger->addMessage('Client has been saved, but error ocured during image processing', 'errors');

                        //redirect to same or another page
                        $redirector = $this->getHelper('Redirector');
                        $redirector->setExit(true)
                                ->gotoRoute(array(
                                    'controller' => 'admin_clients',
                                    'action' => 'edit',
                                    'id' => $clientId
                                        ), 'default', true);
                    }
                    
                } 
                
                //set system message
                $flashMessenger->addMessage('Client has been saved', 'success');

                //redirect to same or another page
                $redirector = $this->getHelper('Redirector');
                $redirector->setExit(true)
                        ->gotoRoute(array(
                            'controller' => 'admin_clients',
                            'action' => 'index'
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
            throw  new Zend_Controller_Router_Exception('Invalid client id: ' . $id, 404);
        }
        
        $cmsClientsTable = new Application_Model_DbTable_CmsClients();
        
        $client = $cmsClientsTable->getClientById($id);
        
        if(empty($client)){
            throw new Zend_Controller_Router_Exception('No client is found with id: ' . $id, 404);
        }
        
        $flashMessenger = $this->getHelper('FlashMessenger');
        
        $systemMessages = array(
            'success' => $flashMessenger->getMessages('success'),
            'errors' => $flashMessenger->getMessages('errors')
        );

        $form = new Application_Form_Admin_ClientEdit();

        //default form data
        $form->populate($client);

      

        if ($request->isPost() && $request->getPost('task') === 'update') {

            try {

                //check form is valid
                if (!$form->isValid($request->getPost())) {
                    throw new Application_Model_Exception_InvalidInput('Invalid data was sent for client.');
                }

                //get form data
                $formData = $form->getValues();
                
                // do actual task
                //save to database etc
             
                 // Update postojeceg zapisa u tabeli
                
                unset($formData['client_photo']);
                
                if($form->getElement('client_photo')->isUploaded()){
                
                    // photo is uploaded
                    
                    $fileInfos = $form->getElement('client_photo')->getFileInfo('client_photo');
                    $fileInfo = $fileInfos['client_photo'];
                    // $fileInfo = $_FILES['member_phpto'];
                    
                    try{
                        // Open uploaded photo in temporary directory
                        $clientPhoto = Intervention\Image\ImageManagerStatic::make($fileInfo['tmp_name']);
                        
                        $clientPhoto->fit(170, 70);
                        
                        $clientPhoto->save(PUBLIC_PATH . '/uploads/clients/' . $client['id'] . '.jpg');
                        
                        //$membecl->clean(Zend_Cache::CLEANING_MODE_ALL);
                        
                    } catch (Exception $ex) {
                        
                        throw new Application_Model_Exception_InvalidInput('Error ocured during image processing');
                        
                    }
                    
                }
                
                $cmsClientsTable->updateClientById($client['id'], $formData);
                
                
                
                
                //set system message
                $flashMessenger->addMessage('Member has been updated', 'success');

                //redirect to same or another page
                $redirector = $this->getHelper('Redirector');
                $redirector->setExit(true)
                        ->gotoRoute(array(
                            'controller' => 'admin_clients',
                            'action' => 'index'
                                ), 'default', true);
            } catch (Application_Model_Exception_InvalidInput $ex) {
                $systemMessages['errors'][] = $ex->getMessage();
            }
        }

        $this->view->systemMessages = $systemMessages;
        $this->view->form = $form;
        
        $this->view->client = $client;
        
        
    }
    
    public function deleteAction() {
        
        $request = $this->getRequest();
        
        if(!$request->isPost() || $request->getPost('task') != 'delete'){
            // request is not post
            // or task is not 'delete'
            // redirect to index
            
            $redirector = $this->getHelper('Redirector');
            $redirector->setExit(true)
                ->gotoRoute(array(
                    'controller' => 'admin_clients',
                    'action' => 'index'
                    ), 'default', true);
        }
        
        $flashMessenger = $this->getHelper('FlashMessenger');
        
        try{

                // read $_POST['id']
            $id = (int) $request->getPost('id');

            if($id <= 0){
                
                throw new Application_Model_Exception_InvalidInput('Invalid client id: ' . $id);

                   }

            $cmsClientsTable = new Application_Model_DbTable_CmsClients();

            $client = $cmsClientsTable->getClientById($id);

            if(empty($client)){
                
                throw new Application_Model_Exception_InvalidInput('No client is found with id: ' . $id);
               
            }

            $cmsClientsTable->deleteClient($client);

            $flashMessenger->addMessage('Client ' . $client['name'] . ' has been deleted.', 'success');

            $redirector = $this->getHelper('Redirector');
                $redirector->setExit(true)
                    ->gotoRoute(array(
                        'controller' => 'admin_clients',
                        'action' => 'index'
                        ), 'default', true);


        } catch (Application_Model_Exception_InvalidInput $ex) {
            $flashMessenger->addMessage($ex->getMessage(), 'errors');
            
             $redirector = $this->getHelper('Redirector');
                $redirector->setExit(true)
                    ->gotoRoute(array(
                        'controller' => 'admin_clients',
                        'action' => 'index'
                        ), 'default', true);
        }
        
    }
    
    public function disableAction() {
        
        $request = $this->getRequest();
        
        if(!$request->isPost() || $request->getPost('task') != 'disable'){
            // request is not post
            // or task is not 'delete'
            // redirect to index
            
            $redirector = $this->getHelper('Redirector');
            $redirector->setExit(true)
                ->gotoRoute(array(
                    'controller' => 'admin_clients',
                    'action' => 'index'
                    ), 'default', true);
        }
        
        $flashMessenger = $this->getHelper('FlashMessenger');
        
        try{

                // read $_POST['id']
            $id = (int) $request->getPost('id');

            if($id <= 0){
                
                throw new Application_Model_Exception_InvalidInput('Invalid client id: ' . $id);

                   }

            $cmsClientsTable = new Application_Model_DbTable_CmsClients();

            $client = $cmsClientsTable->getClientById($id);

            if(empty($client)){
                
                throw new Application_Model_Exception_InvalidInput('No client is found with id: ' . $id);
               
            }

            $cmsClientsTable->disableClient($id);

            $flashMessenger->addMessage('Client ' . $client['name'] . ' ' . ' has been disabled.', 'success');

            $redirector = $this->getHelper('Redirector');
                $redirector->setExit(true)
                    ->gotoRoute(array(
                        'controller' => 'admin_clients',
                        'action' => 'index'
                        ), 'default', true);


        } catch (Application_Model_Exception_InvalidInput $ex) {
            $flashMessenger->addMessage($ex->getMessage(), 'errors');
            
             $redirector = $this->getHelper('Redirector');
                $redirector->setExit(true)
                    ->gotoRoute(array(
                        'controller' => 'admin_clients',
                        'action' => 'index'
                        ), 'default', true);
        }
        
    }
    
    public function enableAction() {
        
        $request = $this->getRequest();
        
        if(!$request->isPost() || $request->getPost('task') != 'enable'){
            // request is not post
            // or task is not 'delete'
            // redirect to index
            
            $redirector = $this->getHelper('Redirector');
            $redirector->setExit(true)
                ->gotoRoute(array(
                    'controller' => 'admin_clients',
                    'action' => 'index'
                    ), 'default', true);
        }
        
        $flashMessenger = $this->getHelper('FlashMessenger');
        
        try{

                // read $_POST['id']
            $id = (int) $request->getPost('id');

            if($id <= 0){
                
                throw new Application_Model_Exception_InvalidInput('Invalid client id: ' . $id);

                   }

            $cmsClientsTable = new Application_Model_DbTable_CmsClients();

            $client = $cmsClientsTable->getClientById($id);

            if(empty($client)){
                
                throw new Application_Model_Exception_InvalidInput('No client is found with id: ' . $id);
               
            }

            $cmsClientsTable->enableClient($id);

            $flashMessenger->addMessage('Client ' . $client['name'] . ' ' . ' has been enabled.', 'success');

            $redirector = $this->getHelper('Redirector');
                $redirector->setExit(true)
                    ->gotoRoute(array(
                        'controller' => 'admin_clients',
                        'action' => 'index'
                        ), 'default', true);


        } catch (Application_Model_Exception_InvalidInput $ex) {
            $flashMessenger->addMessage($ex->getMessage(), 'errors');
            
             $redirector = $this->getHelper('Redirector');
                $redirector->setExit(true)
                    ->gotoRoute(array(
                        'controller' => 'admin_clients',
                        'action' => 'index'
                        ), 'default', true);
        }
        
    }
    
    public function updateorderAction() {
       
        $request = $this->getRequest();
        
        if(!$request->isPost() || $request->getPost('task') != 'saveOrder'){
            // request is not post
            // or task is not 'delete'
            // redirect to index
            
            $redirector = $this->getHelper('Redirector');
            $redirector->setExit(true)
                ->gotoRoute(array(
                    'controller' => 'admin_clients',
                    'action' => 'index'
                    ), 'default', true);
        }
        
        $flashMessenger = $this->getHelper('FlashMessenger');
        
        try{
            
            $sortedIds = $request->getPost('sorted_ids');
            
            if(empty($sortedIds)) {
                throw Application_Model_Exception_InvalidInput('Sorted ids are not sent');
            }
            
            $sortedIds = trim($sortedIds, ' ,');
            
            if(!preg_match('/^[0-9]+(,[0-9]+)*$/', $sortedIds)) {
                throw Application_Model_Exception_InvalidInput('Invalid sorted ids: ' . $sortedIds);
            }
            
            $sortedIds = explode(',', $sortedIds);
            
            $cmsClientsTable = new Application_Model_DbTable_CmsClients();
            
            $cmsClientsTable->updateClintOrder($sortedIds);
            
            $flashMessenger->addMessage('Order is successfuly saved', 'success');
            
             $redirector = $this->getHelper('Redirector');
                $redirector->setExit(true)
                    ->gotoRoute(array(
                        'controller' => 'admin_clients',
                        'action' => 'index'
                        ), 'default', true);
            
        } catch (Application_Model_Exception_InvalidInput $ex) {
             $flashMessenger->addMessage($ex->getMessage(), 'errors');
            
             $redirector = $this->getHelper('Redirector');
                $redirector->setExit(true)
                    ->gotoRoute(array(
                        'controller' => 'admin_clients',
                        'action' => 'index'
                        ), 'default', true);
        }
    }

    
}
