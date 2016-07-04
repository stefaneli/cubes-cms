<?php

class Admin_SessionController extends Zend_Controller_Action
{
    public function indexAction(){
        
       // Provera da li je korisnik ulogovan
       if( Zend_Auth::getInstance()->hasIdentity()) {
           //ulogovan je 
           
           // Redirext na admin_dashboard kontroler i index akciju
           
             $redirector = $this->getHelper('Redirector');
                    $redirector instanceof Zend_Controller_Action_Helper_Redirector;

                    $redirector->setExit(true)
                            ->gotoRoute(array(
                                
                                'controller' => 'admin_dashboard',
                                'action' => 'index'
                                
                                    ), 'default', true);
           
       } else {
           // Nije ulogovan
           
           $redirector = $this->getHelper('Redirector');
                    $redirector instanceof Zend_Controller_Action_Helper_Redirector;

                    $redirector->setExit(true)
                            ->gotoRoute(array(
                                
                                'controller' => 'admin_session',
                                'action' => 'login'
                                
                                    ), 'default', true);
       }
        
        
    }


    public function loginAction(){
        
        // Iskljucivanje layouta
        Zend_Layout::getMvcInstance()->disableLayout();
        
        $loginForm = new Application_Form_Admin_Login();
        
        // Ovo je vazno da imamo ako zelimo da imamo autocomplete
        $request = $this->getRequest();
        $request instanceof Zend_Controller_Request_Http;
        
        $flashMessenger = $this->getHelper('FlashMessenger');
        
        $systemMessages = array(
            'success' => $flashMessenger->getMessages('success'),
            'errors' => $flashMessenger->getMessages('errors')
        );

        if($request->isPost() && $request->getPost('task') === 'login'){
            
            if($loginForm->isValid($request->getPost())){
                
                $authAdapter = new Zend_Auth_Adapter_DbTable();
                $authAdapter->setTableName('cms_users')
                        ->setIdentityColumn('username')
                        ->setCredentialColumn('password')
                        ->setCredentialTreatment('MD5(?) AND status != 0');
                
                $authAdapter->setIdentity($loginForm->getValue('username'));
                $authAdapter->setCredential($loginForm->getValue('password'));
                      
                
                $auth = Zend_Auth::getInstance();
                
                $result = $auth->authenticate($authAdapter);
                
                if($result->isValid()){
                    
                    //Smestanje kompletnog reda iz tabele cms_users kao identifikator da je korisnik ulogovan
                    // Po default-u se smesta samo username, a ovako smestamo asocijativni niz, tj. row iz tabele
                    //Asocijativni niz user ima kljuceva koji su u stvari nazivi kolona u tabeli cms_users
                    $user = $authAdapter->getResultRowObject();
                    $auth->getStorage()->write($user);
                    
                    $redirector = $this->getHelper('Redirector');
                    $redirector instanceof Zend_Controller_Action_Helper_Redirector;

                    $redirector->setExit(true)
                            ->gotoRoute(array(
                                
                                'controller' => 'admin_dashboard',
                                'action' => 'index'
                                
                                    ), 'default', true);
                } else{
                    $systemMessages['errors'][] = 'Wrong username or password.';
                }
                
                
            } else {
                $systemMessages['errors'][] = 'Username and password are requiered';
            }
        }
        
        $this->view->systemMessages = $systemMessages;
        
    }
    
    public function logoutAction(){
        $auth = Zend_Auth::getInstance();
        
        // Brise indikator da je neko ulogovan
        $auth->clearIdentity();
        
        $flashMessenger = $this->getHelper('FlashMessenger');
        
        
        $flashMessenger->addMessage('You have been logged out.', 'success');
        
        //Ovde ide redirect na login stranu
        
        $redirector = $this->getHelper('Redirector');
        $redirector instanceof Zend_Controller_Action_Helper_Redirector;
        
        $redirector->setExit(true)
                ->gotoRoute(array(
                    'controller' => 'admin_session',
                    'action' => 'login'
                    
                ), 'default', true);
        
        //Redirect ako nema dodatnih parametara
//        $redirector->setExit(true)
//                ->gotoSimple('login', 'admin_session');
//        
        //Redirect na spoljni link
//        $redirector->setExit(true)
//                ->setPrependBase(false)
//                ->gotoUrl('https://www.facebook.com');
    }
}