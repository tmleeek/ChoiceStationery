<?php
/**
 * @copyright   Copyright (c) 2009-2011 Amasty (http://www.amasty.com)
 */ 
class Amasty_Meta_Model_Observer
{
    public function setCategoryData($observer)
    {
        //return;
        if (!Mage::getStoreConfig('ammeta/cat/enabled'))
            return;
            
        $cat = $observer->getEvent()->getCategory();
        $cat->setCategory(new Varien_Object(array('name'=>$cat->getName())));
        

        //assign attributes
        $attributes = Mage::getSingleton('catalog/layer')->getFilterableAttributes();
        foreach ($attributes as $a){
           $code = $a->getAttributeCode(); 
           $v = Mage::app()->getRequest()->getParam($code); 
           if (is_numeric($v)){
               $v = $a->getFrontend()->getOption($v);
               $cat->setData($code, $v); 
           }
        }


	$path   = Mage::helper('catalog')->getBreadcrumbPath();
        $prefix = '';
        if (count($path) > 1){// child
            $prefix = 'sub_';
            
            //assign parent name
            $title = array();
            foreach ($path as $breadcrumb) {
                $title[] = $breadcrumb['label'];
            }
            array_pop($title); // category itself
            $cat->setData('meta_parent_category', array_pop($title));          
        }
        
        $replace = array('meta_title', 'meta_keywords', 'meta_description', 'description');
        foreach ($replace as $key){
            if ($cat->getData($key)){
                continue;    
            }
            
            $pattern = Mage::getStoreConfig('ammeta/cat/' . $prefix . $key);
            if (!$pattern){
                continue;    
            }
            
            $tag = Mage::helper('ammeta')->parse($cat, $pattern);
            $max = (int)Mage::getStoreConfig('ammeta/general/max_' . $key);
            if ($max) {
                $tag = substr($tag, 0, $max);
            }  
                              
            $cat->setData($key, $tag);
        }  
        
    }
    
}