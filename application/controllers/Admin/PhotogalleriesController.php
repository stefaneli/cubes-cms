<?php

class Admin_PhotogalleriesController extends Zend_Controller_Action
{
    public function indexAction(){
        
        $flashMessenger = $this->getHelper('FlashMessenger');
        
        $systemMessages = array(
            'success' => $flashMessenger->getMessages('success'),
            'errors' => $flashMessenger->getMessages('errors')
        );
        
        // Prikaz svih photoGallerya
        
        $cmsPhotoGalleriesDbTable = new Application_Model_DbTable_CmsPhotoGalleries();
        
        $photoGalleries = $cmsPhotoGalleriesDbTable->search(array(
//            'filters' => array(
//                'first_name' => array('Aleksandar', 'Aleksandra', 'Bojan')
//            ),
            'orders' => array(
                'order_number' => 'ASC'
            ),
//            'limit' => 4,
//            'page' => 2
        ));
        
        $this->view->photoGalleries = $photoGalleries;
        $this->view->systemMessages = $systemMessages;
                
    }
    
    public function addAction() {
        
        $request = $this->getRequest();
        
        $flashMessenger = $this->getHelper('FlashMessenger');
        
          $systemMessages = array(
            'success' => $flashMessenger->getMessages('success'),
            'errors' => $flashMessenger->getMessages('errors')
        );

        $form = new Application_Form_Admin_PhotoGalleryAdd();

        //default form data
        $form->populate(array(
            
        ));

      

        if ($request->isPost() && $request->getPost('task') === 'save') {

            try {

                //check form is valid
                if (!$form->isValid($request->getPost())) {
                    throw new Application_Model_Exception_InvalidInput('Invalid data was sent for new photoGallery.');
                }

                //get form data
                $formData = $form->getValues();
                
                // remove key photo_gallery_leading_photo from data because there is no column 'photo_gallery_leading_photo' in cms_photoGalleries table
                unset($formData['photo_gallery_leading_photo']);
                // do actual task
                //save to database etc
             
                 // Insertujemo novi zapis u tabelu
                $cmsPhotoGalleriesTable = new Application_Model_DbTable_CmsPhotoGalleries();
                
                // insert photoGallery returns ID of the new photoGallery
                $photoGalleryId = $cmsPhotoGalleriesTable->insertPhotoGallery($formData);
                
                
                
                if($form->getElement('photo_gallery_leading_photo')->isUploaded()){
                
                    // photo is uploaded
                    
                    $fileInfos = $form->getElement('photo_gallery_leading_photo')->getFileInfo('photo_gallery_leading_photo');
                    $fileInfo = $fileInfos['photo_gallery_leading_photo'];
                    // $fileInfo = $_FILES['photoGallery_phpto'];
                    
                    try{
                        // Open uploaded photo in temporary directory
                        $photoGalleryPhoto = Intervention\Image\ImageManagerStatic::make($fileInfo['tmp_name']);
                        
                        $photoGalleryPhoto->fit(360, 270);
                        
                        $photoGalleryPhoto->save(PUBLIC_PATH . '/uploads/photo-galleries/' . $photoGalleryId . '.jpg');
                        
                        
                        
                    } catch (Exception $ex) {
                        $flashMessenger->addMessage('Photo Gallery has been saved, but error ocured during image processing', 'errors');

                        //redirect to same or another page
                        $redirector = $this->getHelper('Redirector');
                        $redirector->setExit(true)
                                ->gotoRoute(array(
                                    'controller' => 'admin_photogalleries',
                                    'action' => 'edit',
                                    'id' => $photoGalleryId
                                        ), 'default', true);
                    }
                    
                } 
                
                //set system message
                $flashMessenger->addMessage('Photo Gallery has been saved', 'success');

                //redirect to same or another page
                $redirector = $this->getHelper('Redirector');
                $redirector->setExit(true)
                        ->gotoRoute(array(
                            'controller' => 'admin_photogalleries',
                            'action' => 'edit',
                            'id' => $photoGalleryId
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
            throw  new Zend_Controller_Router_Exception('Invalid photo gallery id: ' . $id, 404);
        }
        
        $cmsPhotoGalleriesTable = new Application_Model_DbTable_CmsPhotoGalleries();
        
        $photoGallery = $cmsPhotoGalleriesTable->getPhotoGalleryById($id);
        
        if(empty($photoGallery)){
            throw new Zend_Controller_Router_Exception('No photo gallery is found with id: ' . $id, 404);
        }
        
        $flashMessenger = $this->getHelper('FlashMessenger');
        
        $systemMessages = array(
            'success' => $flashMessenger->getMessages('success'),
            'errors' => $flashMessenger->getMessages('errors')
        );

        $form = new Application_Form_Admin_PhotoGalleryEdit();

        //default form data
        $form->populate($photoGallery);

      

        if ($request->isPost() && $request->getPost('task') === 'update') {

            try {

                //check form is valid
                if (!$form->isValid($request->getPost())) {
                    throw new Application_Model_Exception_InvalidInput('Invalid data was sent for photo gallery.');
                }

                //get form data
                $formData = $form->getValues();
                
                // do actual task
                //save to database etc
             
                 // Update postojeceg zapisa u tabeli
                
                unset($formData['photo_gallery_leading_photo']);
                
                if($form->getElement('photo_gallery_leading_photo')->isUploaded()){
                
                    // photo is uploaded
                    
                    $fileInfos = $form->getElement('photo_gallery_leading_photo')->getFileInfo('photo_gallery_leading_photo');
                    $fileInfo = $fileInfos['photo_gallery_leading_photo'];
                    // $fileInfo = $_FILES['photoGallery_phpto'];
                    
                    try{
                        // Open uploaded photo in temporary directory
                        $photoGalleryPhoto = Intervention\Image\ImageManagerStatic::make($fileInfo['tmp_name']);
                        
                        $photoGalleryPhoto->fit(360, 270);
                        
                        $photoGalleryPhoto->save(PUBLIC_PATH . '/uploads/photo-galleries/' . $photoGallery['id'] . '.jpg');
                        
                        
                    } catch (Exception $ex) {
                        
                        throw new Application_Model_Exception_InvalidInput('Error ocured during image processing');
                        
                    }
                    
                }
                
                $cmsPhotoGalleriesTable->updatePhotoGalleryById($photoGallery['id'], $formData);
                
                
                
                
                //set system message
                $flashMessenger->addMessage('Photo Gallery has been updated', 'success');

                //redirect to same or another page
                $redirector = $this->getHelper('Redirector');
                $redirector->setExit(true)
                        ->gotoRoute(array(
                            'controller' => 'admin_photogalleries',
                            'action' => 'index'
                                ), 'default', true);
            } catch (Application_Model_Exception_InvalidInput $ex) {
                $systemMessages['errors'][] = $ex->getMessage();
            }
        }

        $cmsPhotosDbTable = new Application_Model_DbTable_CmsPhotos();
        
        $photos = $cmsPhotosDbTable->search(array(
            'filters' => array(
                'photo_gallery_id' => $photoGallery['id']
            ),
            'orders' => array(
                'order_number' => 'ASC'
            )
        ));
        
        $this->view->systemMessages = $systemMessages;
        $this->view->form = $form;
        
        $this->view->photoGallery = $photoGallery;
        $this->view->photos = $photos;
        
        
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
                    'controller' => 'admin_photogalleries',
                    'action' => 'index'
                    ), 'default', true);
        }
        
        $flashMessenger = $this->getHelper('FlashMessenger');
        
        try{

                // read $_POST['id']
            $id = (int) $request->getPost('id');

            if($id <= 0){
                
                throw new Application_Model_Exception_InvalidInput('Invalid photo gallery id: ' . $id);

                   }

            $cmsPhotoGalleriesTable = new Application_Model_DbTable_CmsPhotoGalleries();

            $photoGallery = $cmsPhotoGalleriesTable->getPhotoGalleryById($id);

            if(empty($photoGallery)){
                
                throw new Application_Model_Exception_InvalidInput('No photo gallery is found with id: ' . $id);
               
            }

            $cmsPhotoGalleriesTable->deletePhotoGallery($photoGallery);

            
            // Brisanje slike za obrisanog photoGallerya
            $photoGalleryFilePath = PUBLIC_PATH . '/uploads/photo-galleries/' . $photoGallery['id'] . '.jpg';
            
            if(is_file($photoGalleryFilePath)){
                unlink($photoGalleryFilePath);
            }
            
            
            $flashMessenger->addMessage('Photo Gallery ' . $photoGallery['tiltel'] . ' has been deleted.', 'success');

            $redirector = $this->getHelper('Redirector');
                $redirector->setExit(true)
                    ->gotoRoute(array(
                        'controller' => 'admin_photogalleries',
                        'action' => 'index'
                        ), 'default', true);


        } catch (Application_Model_Exception_InvalidInput $ex) {
            $flashMessenger->addMessage($ex->getMessage(), 'errors');
            
             $redirector = $this->getHelper('Redirector');
                $redirector->setExit(true)
                    ->gotoRoute(array(
                        'controller' => 'admin_photogalleries',
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
                    'controller' => 'admin_photogalleries',
                    'action' => 'index'
                    ), 'default', true);
        }
        
        $flashMessenger = $this->getHelper('FlashMessenger');
        
        try{

                // read $_POST['id']
            $id = (int) $request->getPost('id');

            if($id <= 0){
                
                throw new Application_Model_Exception_InvalidInput('Invalid photo gallery id: ' . $id);

                   }

            $cmsPhotoGalleriesTable = new Application_Model_DbTable_CmsPhotoGalleries();

            $photoGallery = $cmsPhotoGalleriesTable->getPhotoGalleryById($id);

            if(empty($photoGallery)){
                
                throw new Application_Model_Exception_InvalidInput('No photo gallery is found with id: ' . $id);
               
            }

            $cmsPhotoGalleriesTable->disablePhotoGallery($id);

            $flashMessenger->addMessage('Photo Gallery ' . $photoGallery['title'] . ' has been disabled.', 'success');

            $redirector = $this->getHelper('Redirector');
                $redirector->setExit(true)
                    ->gotoRoute(array(
                        'controller' => 'admin_photogalleries',
                        'action' => 'index'
                        ), 'default', true);


        } catch (Application_Model_Exception_InvalidInput $ex) {
            $flashMessenger->addMessage($ex->getMessage(), 'errors');
            
             $redirector = $this->getHelper('Redirector');
                $redirector->setExit(true)
                    ->gotoRoute(array(
                        'controller' => 'admin_photogalleries',
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
                    'controller' => 'admin_photogalleries',
                    'action' => 'index'
                    ), 'default', true);
        }
        
        $flashMessenger = $this->getHelper('FlashMessenger');
        
        try{

                // read $_POST['id']
            $id = (int) $request->getPost('id');

            if($id <= 0){
                
                throw new Application_Model_Exception_InvalidInput('Invalid photo gallery id: ' . $id);

                   }

            $cmsPhotoGalleriesTable = new Application_Model_DbTable_CmsPhotoGalleries();

            $photoGallery = $cmsPhotoGalleriesTable->getPhotoGalleryById($id);

            if(empty($photoGallery)){
                
                throw new Application_Model_Exception_InvalidInput('No photo gallery is found with id: ' . $id);
               
            }

            $cmsPhotoGalleriesTable->enablePhotoGallery($id);

            $flashMessenger->addMessage('Photo Gallery ' . $photoGallery['title'] . ' has been enabled.', 'success');

            $redirector = $this->getHelper('Redirector');
                $redirector->setExit(true)
                    ->gotoRoute(array(
                        'controller' => 'admin_photogalleries',
                        'action' => 'index'
                        ), 'default', true);


        } catch (Application_Model_Exception_InvalidInput $ex) {
            $flashMessenger->addMessage($ex->getMessage(), 'errors');
            
             $redirector = $this->getHelper('Redirector');
                $redirector->setExit(true)
                    ->gotoRoute(array(
                        'controller' => 'admin_photogalleries',
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
                    'controller' => 'admin_photogalleries',
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
            
            $cmsPhotoGalleriesTable = new Application_Model_DbTable_CmsPhotoGalleries();
            
            $cmsPhotoGalleriesTable->updatePhotoGalleryOrder($sortedIds);
            
            $flashMessenger->addMessage('Order is successfuly saved', 'success');
            
             $redirector = $this->getHelper('Redirector');
                $redirector->setExit(true)
                    ->gotoRoute(array(
                        'controller' => 'admin_photogalleries',
                        'action' => 'index'
                        ), 'default', true);
            
        } catch (Application_Model_Exception_InvalidInput $ex) {
             $flashMessenger->addMessage($ex->getMessage(), 'errors');
            
             $redirector = $this->getHelper('Redirector');
                $redirector->setExit(true)
                    ->gotoRoute(array(
                        'controller' => 'admin_photogalleries',
                        'action' => 'index'
                        ), 'default', true);
        }
    }

    public function dashboardAction() {
        
            $cmsPhotoGalleriesDbTable = new Application_Model_DbTable_CmsPhotoGalleries();
            
            $total = $cmsPhotoGalleriesDbTable->count();
        
            $active = $cmsPhotoGalleriesDbTable->count(array(
                'status' => Application_Model_DbTable_CmsPhotoGalleries::STATUS_ENABLED
            ));
        
         
            $this->view->total = $total;
            $this->view->active = $active;
        
      
    }
    
}
