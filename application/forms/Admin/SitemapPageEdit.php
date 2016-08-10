<?php

class Application_Form_Admin_SitemapPageEdit extends Zend_Form
{
    protected $parentID;
    protected $sitemapPageID;


    public function __construct($sitemapPageID, $parentId, $options = null) {
        
        $this->sitemapPageID = $sitemapPageID;
        $this->parentID = $parentId;
        
        parent::__construct($options);
    }

    
    public function init() {
        //type
        // url_slug
        // short_title
        // title
        //description 
        //body
        
//        Zend_Form_Element_Select;
//        Zend_Form_Element_Multiselect;
//        Zend_Form_Element_MultiCheckbox;
        
        $type = new Zend_Form_Element_Select('type');
        $type->addMultiOption('', '-- Select Sitemap Page Type --')
                ->addMultiOptions(array(
                            'StaticPage' => 'Static Page',
                            'AboutUsPage' => 'About Us Page',
                            'ContactPage' => 'Contact Page', 
        ))->setRequired(true);
        
        $this->addElement($type);
        
        $urlSlug = new Zend_Form_Element_Text('url_slug');
        $urlSlug->addFilter('StringTrim')
                ->addFilter(new Application_Model_Filter_UrlSlug())
                ->addValidator(new Zend_Validate_Db_NoRecordExists(array(
                    'table' => 'cms_sitemap_pages',
                    'field' => 'url_slug',
                    'exclude' => 'parent_id = ' . $this->parentID . ' AND id != ' . $this->sitemapPageID
                )))
                ->addValidator('StringLength', false, array('min' => 2, 'max' => 255))
                ->setRequired(true);
                
        $this->addElement($urlSlug);
        
        $shortTitle = new Zend_Form_Element_Text('short_title');
        $shortTitle->addFilter('StringTrim')
                ->addValidator('StringLength', false, array('min' => 2, 'max' => 255))
                ->setRequired(true);
                
        $this->addElement($shortTitle);
        
        $title = new Zend_Form_Element_Text('title');
        $title->addFilter('StringTrim')
                ->addValidator('StringLength', false, array('min' => 2, 'max' => 500))
                ->setRequired(true);
                
        $this->addElement($title);
        
        $description = new Zend_Form_Element_Textarea('description');
        $description->addFilter('StringTrim')
                ->setRequired(false);
                
        $this->addElement($description);
        
        $body = new Zend_Form_Element_Textarea('body');
        $body->setRequired(false);
                
        $this->addElement($body);
    }

}

