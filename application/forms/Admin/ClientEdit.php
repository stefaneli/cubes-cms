<?php

class Application_Form_Admin_ClientEdit extends Zend_Form
{
    
    // Overajdovan init metoda
    public function init() {
        $name = new Zend_Form_Element_Text('name');
        //$name->addFilter(new Zend_Filter_StringTrim());
        //$name->addValidator(new Zend_Validate_StringLength(array('min' => 3, 'max' => 255)));
        
        $name->addFilter('StringTrim')
                ->addValidator('StringLength', false, array('min' => 3, 'max' => 255))
                ->setRequired(true);
        
        $this->addElement($name);
        
        
        $resume = new Zend_Form_Element_Textarea('description');
        $resume->addFilter('StringTrim')
                ->setRequired(false);
        $this->addElement($resume);
        
        $clientPhoto = new Zend_Form_Element_File('client_photo');
        $clientPhoto->addValidator('Count', true, 1) 
                ->addValidator('MimeType', true, array('image/gif', 'image/jpeg', 'image/png'))
                ->addValidator('ImageSize', false, array(
                    'minwidth' => 170,
                    'maxwidth' => 2000,
                    'minheight' => 70,
                    'maxheight' => 2000
                ))
                ->addValidator('Size', false, array(
                    'max' => '10MB'
                    ))
                // disable move file to destination when calling method getValues
                ->setValueDisabled(true)
                ->setRequired(false);
        $this->addElement($clientPhoto);
        
    }

    
}
