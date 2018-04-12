<?php
/**
* @author Amasty Team
* @copyright Copyright (c) 2010-2011 Amasty (http://www.amasty.com)
*/
class Amasty_Meta_Block_Adminhtml_Config_Edit_Tabs extends Mage_Adminhtml_Block_Widget_Tabs
{
    public function __construct()
    {
        parent::__construct();
        $this->setId('configTabs');
        $this->setDestElementId('edit_form');
        $this->setTitle(Mage::helper('ammeta')->__('Template Configuration'));
    }

    protected function _beforeToHtml()
    {
        $name = Mage::helper('ammeta')->__('Conditions');
        $this->addTab('condition', array(
            'label'     => $name,
            'content'   => $this->getLayout()->createBlock('ammeta/adminhtml_config_edit_tab_condition')
                ->setTitle($name)->toHtml(),
        ));
        
        $name = Mage::helper('ammeta')->__('Product Tags');
        $this->addTab('tags', array(
            'label'     => $name,
            'content'   => $this->getLayout()->createBlock('ammeta/adminhtml_config_edit_tab_tags')
                ->setTitle($name)->toHtml(),
        ));

        return parent::_beforeToHtml();
    }
}