<?php

class Zend_View_Helper_PhotoUrl extends Zend_View_Helper_Abstract {

    /**
     * 
     * @param type $photo
     * @return string
     */
    public function photoUrl($photo) {

        $photoFileName = $photo['id'] . '.jpg';  // '-' . date("Y-m-d") .

        $photoFilePath = PUBLIC_PATH . '/uploads/photo-galleries/photos/' . $photoFileName;
        // Helper ima property view koji je Zend View
        // i preko kojeg pozivamo ostale view helpere
        // na primer $this->view->baseUrl();
        if (is_file($photoFilePath)) {
            return $this->view->baseUrl('/uploads/photo-galleries/photos/' . $photoFileName . '?' . time()) ; // dodali smo time() da bi dodali na kraj parameta
                                                                                                   // vreme kako bi prevarili browser da refresuje uvek sliku
        } else {
            return '';
        }
    }

}
