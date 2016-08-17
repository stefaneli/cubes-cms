<?php

class Admin_IndexslidesController extends Zend_Controller_Action
{
    public function indexAction(){
        
        $flashMessenger = $this->getHelper('FlashMessenger');
        
        $systemMessages = array(
            'success' => $flashMessenger->getMessages('success'),
            'errors' => $flashMessenger->getMessages('errors')
        );
        
        // Prikaz svih indexSlidea
        
        $cmsIndexSlidesDbTable = new Application_Model_DbTable_CmsIndexSlides();
        
        $indexSlides = $cmsIndexSlidesDbTable->search(array(
//            'filters' => array(
//                'first_name' => array('Aleksandar', 'Aleksandra', 'Bojan')
//            ),
            'orders' => array(
                'order_number' => 'ASC'
            ),
//            'limit' => 4,
//            'page' => 2
        ));
        
        $this->view->indexSlides = $indexSlides;
        $this->view->systemMessages = $systemMessages;
                
    }
    
    public function addAction() {
        
        $request = $this->getRequest();
        
        $flashMessenger = $this->getHelper('FlashMessenger');
        
          $systemMessages = array(
            'success' => $flashMessenger->getMessages('success'),
            'errors' => $flashMessenger->getMessages('errors')
        );

        $form = new Application_Form_Admin_IndexSlideAdd();

        //default form data
        $form->populate(array(
            
        ));

      

        if ($request->isPost() && $request->getPost('task') === 'save') {

            try {

                //check form is valid
                if (!$form->isValid($request->getPost())) {
                    throw new Application_Model_Exception_InvalidInput('Invalid data was sent for new indexSlide.');
                }

                //get form data
                $formData = $form->getValues();
                
                // remove key index_slide_photo from data because there is no column 'index_slide_photo' in cms_indexSlides table
                unset($formData['index_slide_photo']);
                // do actual task
                //save to database etc
             
                 // Insertujemo novi zapis u tabelu
                $cmsIndexSlidesTable = new Application_Model_DbTable_CmsIndexSlides();
                
                // insert indexSlide returns ID of the new indexSlide
                $indexSlideId = $cmsIndexSlidesTable->insertIndexSlide($formData);
                
                
                
                if($form->getElement('index_slide_photo')->isUploaded()){
                
                    // photo is uploaded
                    
                    $fileInfos = $form->getElement('index_slide_photo')->getFileInfo('index_slide_photo');
                    $fileInfo = $fileInfos['index_slide_photo'];
                    // $fileInfo = $_FILES['indexSlide_phpto'];
                    
                    try{
                        // Open uploaded photo in temporary directory
                        $indexSlidePhoto = Intervention\Image\ImageManagerStatic::make($fileInfo['tmp_name']);
                        
                        $indexSlidePhoto->fit(600, 400);
                        
                        $indexSlidePhoto->save(PUBLIC_PATH . '/uploads/index-slides/' . $indexSlideId . '.jpg');
                        
                        
                        
                    } catch (Exception $ex) {
                        $flashMessenger->addMessage('IndexSlide has been saved, but error ocured during image processing', 'errors');

                        //redirect to same or another page
                        $redirector = $this->getHelper('Redirector');
                        $redirector->setExit(true)
                                ->gotoRoute(array(
                                    'controller' => 'admin_indexslides',
                                    'action' => 'edit',
                                    'id' => $indexSlideId
                                        ), 'default', true);
                    }
                    
                } 
                
                //set system message
                $flashMessenger->addMessage('Slide has been saved', 'success');

                //redirect to same or another page
                $redirector = $this->getHelper('Redirector');
                $redirector->setExit(true)
                        ->gotoRoute(array(
                            'controller' => 'admin_indexslides',
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
            throw  new Zend_Controller_Router_Exception('Invalid indexSlide id: ' . $id, 404);
        }
        
        $cmsIndexSlidesTable = new Application_Model_DbTable_CmsIndexSlides();
        
        $indexSlide = $cmsIndexSlidesTable->getIndexSlideById($id);
        
        if(empty($indexSlide)){
            throw new Zend_Controller_Router_Exception('No indexSlide is found with id: ' . $id, 404);
        }
        
        $flashMessenger = $this->getHelper('FlashMessenger');
        
        $systemMessages = array(
            'success' => $flashMessenger->getMessages('success'),
            'errors' => $flashMessenger->getMessages('errors')
        );

        $form = new Application_Form_Admin_IndexSlideAdd();

        //default form data
        $form->populate($indexSlide);

      

        if ($request->isPost() && $request->getPost('task') === 'update') {

            try {

                //check form is valid
                if (!$form->isValid($request->getPost())) {
                    throw new Application_Model_Exception_InvalidInput('Invalid data was sent for indexSlide.');
                }

                //get form data
                $formData = $form->getValues();
                
                // do actual task
                //save to database etc
             
                 // Update postojeceg zapisa u tabeli
                
                unset($formData['index_slide_photo']);
                
                if($form->getElement('index_slide_photo')->isUploaded()){
                
                    // photo is uploaded
                    
                    $fileInfos = $form->getElement('index_slide_photo')->getFileInfo('index_slide_photo');
                    $fileInfo = $fileInfos['index_slide_photo'];
                    // $fileInfo = $_FILES['indexSlide_phpto'];
                    
                    try{
                        // Open uploaded photo in temporary directory
                        $indexSlidePhoto = Intervention\Image\ImageManagerStatic::make($fileInfo['tmp_name']);
                        
                        $indexSlidePhoto->fit(600, 400);
                        
                        $indexSlidePhoto->save(PUBLIC_PATH . '/uploads/index-slides/' . $indexSlide['id'] . '.jpg');
                        
                        //$membecl->clean(Zend_Cache::CLEANING_MODE_ALL);
                        
                    } catch (Exception $ex) {
                        
                        throw new Application_Model_Exception_InvalidInput('Error ocured during image processing');
                        
                    }
                    
                }
                
                $cmsIndexSlidesTable->updateIndexSlideById($indexSlide['id'], $formData);
                
                
                
                
                //set system message
                $flashMessenger->addMessage('Slide has been updated', 'success');

                //redirect to same or another page
                $redirector = $this->getHelper('Redirector');
                $redirector->setExit(true)
                        ->gotoRoute(array(
                            'controller' => 'admin_indexslides',
                            'action' => 'index'
                                ), 'default', true);
            } catch (Application_Model_Exception_InvalidInput $ex) {
                $systemMessages['errors'][] = $ex->getMessage();
            }
        }

        $this->view->systemMessages = $systemMessages;
        $this->view->form = $form;
        
        $this->view->indexSlide = $indexSlide;
        
        
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
                    'controller' => 'admin_indexslides',
                    'action' => 'index'
                    ), 'default', true);
        }
        
        $flashMessenger = $this->getHelper('FlashMessenger');
        
        try{

                // read $_POST['id']
            $id = (int) $request->getPost('id');

            if($id <= 0){
                
                throw new Application_Model_Exception_InvalidInput('Invalid indexSlide id: ' . $id);

                   }

            $cmsIndexSlidesTable = new Application_Model_DbTable_CmsIndexSlides();

            $indexSlide = $cmsIndexSlidesTable->getIndexSlideById($id);

            if(empty($indexSlide)){
                
                throw new Application_Model_Exception_InvalidInput('No indexSlide is found with id: ' . $id);
               
            }

            $cmsIndexSlidesTable->deleteIndexSlide($indexSlide);

            
            // Brisanje slike za obrisanog indexSlidea
            $indexSlideFilePath = PUBLIC_PATH . '/uploads/index-slides/' . $indexSlide['id'] . '.jpg';
            
            if(is_file($indexSlideFilePath)){
                unlink($indexSlideFilePath);
            }
            
            
            $flashMessenger->addMessage('Slide ' . $indexSlide['title'] . ' has been deleted.', 'success');

            $redirector = $this->getHelper('Redirector');
                $redirector->setExit(true)
                    ->gotoRoute(array(
                        'controller' => 'admin_indexslides',
                        'action' => 'index'
                        ), 'default', true);


        } catch (Application_Model_Exception_InvalidInput $ex) {
            $flashMessenger->addMessage($ex->getMessage(), 'errors');
            
             $redirector = $this->getHelper('Redirector');
                $redirector->setExit(true)
                    ->gotoRoute(array(
                        'controller' => 'admin_indexslides',
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
                    'controller' => 'admin_indexslides',
                    'action' => 'index'
                    ), 'default', true);
        }
        
        $flashMessenger = $this->getHelper('FlashMessenger');
        
        try{

                // read $_POST['id']
            $id = (int) $request->getPost('id');

            if($id <= 0){
                
                throw new Application_Model_Exception_InvalidInput('Invalid indexSlide id: ' . $id);

                   }

            $cmsIndexSlidesTable = new Application_Model_DbTable_CmsIndexSlides();

            $indexSlide = $cmsIndexSlidesTable->getIndexSlideById($id);

            if(empty($indexSlide)){
                
                throw new Application_Model_Exception_InvalidInput('No indexSlide is found with id: ' . $id);
               
            }

            $cmsIndexSlidesTable->disableIndexSlide($id);

            $flashMessenger->addMessage('Slide ' . $indexSlide['title'] . ' has been disabled.', 'success');

            $redirector = $this->getHelper('Redirector');
                $redirector->setExit(true)
                    ->gotoRoute(array(
                        'controller' => 'admin_indexslides',
                        'action' => 'index'
                        ), 'default', true);


        } catch (Application_Model_Exception_InvalidInput $ex) {
            $flashMessenger->addMessage($ex->getMessage(), 'errors');
            
             $redirector = $this->getHelper('Redirector');
                $redirector->setExit(true)
                    ->gotoRoute(array(
                        'controller' => 'admin_indexslides',
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
                    'controller' => 'admin_indexslides',
                    'action' => 'index'
                    ), 'default', true);
        }
        
        $flashMessenger = $this->getHelper('FlashMessenger');
        
        try{

                // read $_POST['id']
            $id = (int) $request->getPost('id');

            if($id <= 0){
                
                throw new Application_Model_Exception_InvalidInput('Invalid indexSlide id: ' . $id);

                   }

            $cmsIndexSlidesTable = new Application_Model_DbTable_CmsIndexSlides();

            $indexSlide = $cmsIndexSlidesTable->getIndexSlideById($id);

            if(empty($indexSlide)){
                
                throw new Application_Model_Exception_InvalidInput('No indexSlide is found with id: ' . $id);
               
            }

            $cmsIndexSlidesTable->enableIndexSlide($id);

            $flashMessenger->addMessage('Slide ' . $indexSlide['title'] . ' has been enabled.', 'success');

            $redirector = $this->getHelper('Redirector');
                $redirector->setExit(true)
                    ->gotoRoute(array(
                        'controller' => 'admin_indexslides',
                        'action' => 'index'
                        ), 'default', true);


        } catch (Application_Model_Exception_InvalidInput $ex) {
            $flashMessenger->addMessage($ex->getMessage(), 'errors');
            
             $redirector = $this->getHelper('Redirector');
                $redirector->setExit(true)
                    ->gotoRoute(array(
                        'controller' => 'admin_indexslides',
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
                    'controller' => 'admin_indexslides',
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
            
            $cmsIndexSlidesTable = new Application_Model_DbTable_CmsIndexSlides();
            
            $cmsIndexSlidesTable->updateIndexSlideOrder($sortedIds);
            
            $flashMessenger->addMessage('Order is successfuly saved', 'success');
            
             $redirector = $this->getHelper('Redirector');
                $redirector->setExit(true)
                    ->gotoRoute(array(
                        'controller' => 'admin_indexslides',
                        'action' => 'index'
                        ), 'default', true);
            
        } catch (Application_Model_Exception_InvalidInput $ex) {
             $flashMessenger->addMessage($ex->getMessage(), 'errors');
            
             $redirector = $this->getHelper('Redirector');
                $redirector->setExit(true)
                    ->gotoRoute(array(
                        'controller' => 'admin_indexslides',
                        'action' => 'index'
                        ), 'default', true);
        }
    }

    public function dashboardAction() {
        
            $cmsIndexSlidesDbTable = new Application_Model_DbTable_CmsIndexSlides();
            
            $total = $cmsIndexSlidesDbTable->count();
        
            $active = $cmsIndexSlidesDbTable->count(array(
                'status' => Application_Model_DbTable_CmsIndexSlides::STATUS_ENABLED
            ));
        
         
            $this->view->total = $total;
            $this->view->active = $active;
        
      
    }
    
}
