<?php

class Application_Form_Admin_SitemapPageAdd extends Zend_Form
{
    protected $parentID;
    protected $parentType;


    public function __construct($parentId, $parentType, $options = null) {
        
        $this->parentID = $parentId;
        
        $this->parentType = $parentType;
        
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
        
        $sitemapPageTypes = Zend_Registry::get('sitemapPageTypes');
        $rootSitemapPageTypes = Zend_Registry::get('rootSitemapPageTypes');
        
        if($this->parentID == 0) {
            $parentSubTypes = $rootSitemapPageTypes;
        } else {
            $parentSubTypes = $sitemapPageTypes[$this->parentType]['subtypes'];
        }
        
        $cmsSitemapPagesDbTable = new Application_Model_DbTable_CmsSitemapPages();
        
        $parentSubtypesCount = $cmsSitemapPagesDbTable->countByTypes(array(
            'parent_id' => $this->parentID
        ));
        
        
        $type = new Zend_Form_Element_Select('type');
        $type->addMultiOption('', '-- Select Sitemap Page Type --')
                ->setRequired(true);
        
        foreach ($parentSubTypes as $sitemapPageType => $sitemapPageTypeMax) {
            
            $sitemapPageTypeProperties = $sitemapPageTypes[$sitemapPageType];
            
            $totalExistingSitemapPagesOfType = isset($parentSubtypesCount[$sitemapPageType]) ? $parentSubtypesCount[$sitemapPageType] : 0 ;
            
            if($sitemapPageTypeMax == 0 || $sitemapPageTypeMax > $totalExistingSitemapPagesOfType ) {
                
                 $type->addMultiOption($sitemapPageType, $sitemapPageTypeProperties['title']);
            }
            
           
        }
        
        $this->addElement($type);
        
        $urlSlug = new Zend_Form_Element_Text('url_slug');
        $urlSlug->addFilter('StringTrim')
                ->addFilter(new Application_Model_Filter_UrlSlug())
                ->addValidator(new Zend_Validate_Db_NoRecordExists(array(
                    'table' => 'cms_sitemap_pages',
                    'field' => 'url_slug',
                    'exclude' => 'parent_id = ' . $this->parentID
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

