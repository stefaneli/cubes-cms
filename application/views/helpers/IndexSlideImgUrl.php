<?php

class Zend_View_Helper_IndexSlideImgUrl extends Zend_View_Helper_Abstract {

    /**
     * 
     * @param type $indexSlide
     * @return string
     */
    public function indexSlideImgUrl($indexSlide) {

        $indexSlideImgFileName = $indexSlide['id'] . '.jpg';  // '-' . date("Y-m-d") .

        $indexSlideImgFilePath = PUBLIC_PATH . '/uploads/index-slides/' . $indexSlideImgFileName;
        // Helper ima property view koji je Zend View
        // i preko kojeg pozivamo ostale view helpere
        // na primer $this->view->baseUrl();
        if (is_file($indexSlideImgFilePath)) {
            return $this->view->baseUrl('/uploads/index-slides/' . $indexSlideImgFileName . '?' . time()) ; // dodali smo time() da bi dodali na kraj parameta
                                                                                                   // vreme kako bi prevarili browser da refresuje uvek sliku
        } else {
            return '';
        }
    }

}
