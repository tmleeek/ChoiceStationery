<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2018 Amasty (https://www.amasty.com)
 * @package Amasty_Meta
 */

require_once 'Amasty/Meta/controllers/Adminhtml/Ammeta/ConfigController.php';

class Amasty_Meta_Adminhtml_Ammeta_CustomController extends Amasty_Meta_Adminhtml_Ammeta_ConfigController
{
	protected $_title = 'Meta Tags Template (Custom URLs)';
	protected $_isCustom = true;
	protected $_blockName = 'custom';

    protected function _isAllowed()
    {
        return Mage::getSingleton('admin/session')->isAllowed('cms/amseotoolkit/ammeta/meta_сustom');
    }
}