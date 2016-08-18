<?php

class Zend_View_Helper_PhotoGalleryLeadingPhotoUrl extends Zend_View_Helper_Abstract {

    /**
     * 
     * @param type $photoGallery
     * @return string
     */
    public function photoGalleryLeadingPhotoUrl($photoGallery) {

        $photoGalleryImgFileName = $photoGallery['id'] . '.jpg';  // '-' . date("Y-m-d") .

        $photoGalleryImgFilePath = PUBLIC_PATH . '/uploads/photo-galleries/' . $photoGalleryImgFileName;
        // Helper ima property view koji je Zend View
        // i preko kojeg pozivamo ostale view helpere
        // na primer $this->view->baseUrl();
        if (is_file($photoGalleryImgFilePath)) {
            return $this->view->baseUrl('/uploads/photo-galleries/' . $photoGalleryImgFileName . '?' . time()) ; // dodali smo time() da bi dodali na kraj parameta
                                                                                                   // vreme kako bi prevarili browser da refresuje uvek sliku
        } else {
            return '';
        }
    }

}
