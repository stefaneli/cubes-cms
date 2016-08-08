<?php

class Bootstrap extends Zend_Application_Bootstrap_Bootstrap
{

    protected function _initRouter() {
        
        $router = Zend_Controller_Front::getInstance()->getRouter();
        
        $router instanceof Zend_Controller_Router_Rewrite;
        
        $router->addRoute('about-us-route', new Zend_Controller_Router_Route_Static(
                'about-us',
                array(
                    'controller' => 'aboutus',
                    'action' => 'index'
                )
                ))->addRoute('member-route', new Zend_Controller_Router_Route(
                        'about-us/member/:id/:member_slug',
                        array(
                    'controller' => 'aboutus',
                    'action' => 'member',
                    'member_slug' => ''
                )
                        ))
                ->addRoute('contact-us-route', new Zend_Controller_Router_Route_Static(
                        'contact-us',
                        array(
                    'controller' => 'contact',
                    'action' => 'index'     
                )
                        ))
                ->addRoute('ask-member-route', new Zend_Controller_Router_Route(
                        'ask-member/:id/:member_slug',
                        array(
                    'controller' => 'contact',
                    'action' => 'askmember',
                    'member_slug' => ''
                )
                        ));
        
    
    }

}

