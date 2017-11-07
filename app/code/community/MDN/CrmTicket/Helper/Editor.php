<?php
/**
 * Magento
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 *
 * @copyright  Copyright (c) 2013 BoostMyshop (http://www.boostmyshop.com)
 * @license    http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 * @package MDN_CrmTicket
 * @version 1.2
 */
class MDN_CrmTicket_Helper_Editor extends Mage_Core_Helper_Abstract
{
    /**
     * Create block and return html code
     * @param type $name 
     */
    public function getWysiwygHtml($name, $content = null, $required = false)
    {
        $block = Mage::getSingleton('core/layout')->createBlock('CrmTicket/Editor_Wysiwyg');
        return $block->getWysiwygControl($name, $content, $required);
    }
   
}