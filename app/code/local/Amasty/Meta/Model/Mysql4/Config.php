<?php
/**
* @author Amasty Team
* @copyright Copyright (c) 2010-2011 Amasty (http://www.amasty.com)
*/ 
class Amasty_Meta_Model_Mysql4_Config extends Mage_Core_Model_Mysql4_Abstract
{
    public function _construct()
    {    
        $this->_init('ammeta/config', 'config_id');
    }
}