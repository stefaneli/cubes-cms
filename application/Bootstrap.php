<?php

class Bootstrap extends Zend_Application_Bootstrap_Bootstrap
{

    protected function _initRouter() {
        // ensure that db is configured
        $this->bootstrap('db');
        
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
        
        $sitemapPagesMap = Application_Model_DbTable_CmsSitemapPages::getSitemapPagesMap();
        
        foreach ($sitemapPagesMap as $sitemapPageId => $sitemapPageMap ) {
            
            if($sitemapPageMap['type'] == 'StaticPage'){
                $router->addRoute('static-page-route-' . $sitemapPageId, new Zend_Controller_Router_Route_Static(
                        $sitemapPageMap['url'],
                        array(
                            'controller' => 'staticpage',
                            'action' => 'index',
                            'sitemap_page_id' => $sitemapPageId
                )
                        ));
            }
            
              if($sitemapPageMap['type'] == 'AboutUsPage'){
                $router->addRoute('static-page-route-' . $sitemapPageId, new Zend_Controller_Router_Route_Static(
                        $sitemapPageMap['url'],
                        array(
                            'controller' => 'aboutus',
                            'action' => 'index',
                            'sitemap_page_id' => $sitemapPageId
                )
                        ));
            }
            
              if($sitemapPageMap['type'] == 'ContactPage'){
                $router->addRoute('static-page-route-' . $sitemapPageId, new Zend_Controller_Router_Route_Static(
                        $sitemapPageMap['url'],
                        array(
                            'controller' => 'contact',
                            'action' => 'index',
                            'sitemap_page_id' => $sitemapPageId
                )
                        ));
            }
            
        }
        
    
    }

}

