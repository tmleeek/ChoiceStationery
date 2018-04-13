<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2018 Amasty (https://www.amasty.com)
 * @package Amasty_Meta
 */       
class Amasty_Meta_Block_Adminhtml_Config extends Mage_Adminhtml_Block_Widget_Grid_Container
{
    public function __construct()
    {
        $this->_controller      = isset($data['is_custom']) && $data['is_custom'] === true
			? 'adminhtml_custom' : 'adminhtml_config';
        $this->_blockGroup = 'ammeta';
        $this->_headerText      = Mage::helper('ammeta')->__($data['title']);
		$this->_addButtonConfig = Mage::helper('ammeta')->__('Add Template');
        
        parent::__construct();
    }
}
