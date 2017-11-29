<?php
/**
 * Mirasvit
 *
 * This source file is subject to the Mirasvit Software License, which is available at https://mirasvit.com/license/.
 * Do not edit or add to this file if you wish to upgrade the to newer versions in the future.
 * If you wish to customize this module for your needs.
 * Please refer to http://www.magentocommerce.com for more information.
 *
 * @category  Mirasvit
 * @package   mirasvit/extension_seo
 * @version   1.3.18
 * @copyright Copyright (C) 2017 Mirasvit (https://mirasvit.com/)
 */


class Mirasvit_Seo_Block_Adminhtml_Redirectimport_Edit extends Mage_Adminhtml_Block_Widget_Form_Container
{
    public function __construct ()
    {
        parent::__construct();
        $this->_removeButton('back');
        $this->_objectId = 'redirect_import';
        $this->_controller = 'adminhtml_redirectimport';
        $this->_blockGroup = 'seo';

        return $this;
    }

    public function getHeaderText ()
    {
        return Mage::helper('seo')->__('Import Urls for Redirect');
    }
}