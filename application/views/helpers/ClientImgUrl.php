<?php

class Zend_View_Helper_ClientImgUrl extends Zend_View_Helper_Abstract {

    /**
     * 
     * @param type $client
     * @return string
     */
    public function clientImgUrl($client) {

        $clientImgFileName = $client['id'] . '.jpg';  // '-' . date("Y-m-d") .

        $clientImgFilePath = PUBLIC_PATH . '/uploads/clients/' . $clientImgFileName;
        // Helper ima property view koji je Zend View
        // i preko kojeg pozivamo ostale view helpere
        // na primer $this->view->baseUrl();
        if (is_file($clientImgFilePath)) {
            return $this->view->baseUrl('/uploads/clients/' . $clientImgFileName);
        } else {
            return $this->view->baseUrl('/uploads/clients/no-image.jpg');
        }
    }

}
