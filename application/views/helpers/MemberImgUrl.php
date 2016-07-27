<?php

class Zend_View_Helper_MemberImgUrl extends Zend_View_Helper_Abstract {

    /**
     * 
     * @param type $member
     * @return string
     */
    public function memberImgUrl($member) {

        $memberImgFileName = $member['id'] . '.jpg';  // '-' . date("Y-m-d") .

        $memberImgFilePath = PUBLIC_PATH . '/uploads/members/' . $memberImgFileName;
        // Helper ima property view koji je Zend View
        // i preko kojeg pozivamo ostale view helpere
        // na primer $this->view->baseUrl();
        if (is_file($memberImgFilePath)) {
            return $this->view->baseUrl('/uploads/members/' . $memberImgFileName . '?' . time()) ; // dodali smo time() da bi dodali na kraj parameta
                                                                                                   // vreme kako bi prevarili browser da refresuje uvek sliku
        } else {
            return $this->view->baseUrl('/uploads/members/no-image.jpg');
        }
    }

}
