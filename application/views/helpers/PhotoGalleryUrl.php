<?php

class Zend_View_Helper_PhotoGalleryUrl extends Zend_View_Helper_Abstract {

    	protected $urlSlugFilter;
	
	protected function getUrlSlugFilter() {
		
		/*** Lazy Loading ***/
		// samo prvi put se instancira property, i savaki sledeci put se koristi taj isti
		if (!$this->urlSlugFilter) {
			$this->urlSlugFilter = new Application_Model_Filter_UrlSlug();
		}
		
		return $this->urlSlugFilter;
	}
    
        public function photoGalleryUrl($photoGallery) {
        
        // Moglo je i ovako $urlSlugFilter = new Application_Model_Filter_UrlSlug();
        // bez ove gore funkcije.
        $urlSlugFilter = $this->getUrlSlugFilter();
        
        return $this->view->url(array(
            'id' => $photoGallery['id'],
            'photo_gallery_slug' => $urlSlugFilter->filter($photoGallery['title'])
                
                ),'photo-gallery-route', true);
        
    }

}

