<?php

class Application_Form_Admin_Login extends Zend_Form
{
    public function init() {
        
       // Kreiramo element
       $username = new Zend_Form_Element_Text('username');
       
       $username->addFilter('StringTrim')
               ->addFilter('StringToLower')
               ->setRequired(true); // naznacujemo da je element obavezan
       
       //Dodajemo ga u formu
       $this->addElement($username);
       
       $password = new Zend_Form_Element_Password('password');
       $password->setRequired(true);
       
       $this->addElement($password);
       
    }

}

