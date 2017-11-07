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
 * @copyright  Copyright (c) 2009 Maison du Logiciel (http://www.maisondulogiciel.com)
 * @license    http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 * @package MDN_CrmTicket
 * @version 1.2
 */
class MDN_CrmTicket_Block_Admin_Widget_Grid_Column_Renderer_CustomerObject extends Mage_Adminhtml_Block_Widget_Grid_Column_Renderer_Abstract {

    public function render(Varien_Object $row) {

        if ($row->getct_object_id()) {
            list($objectType, $objectId) = explode('_', $row->getct_object_id());
            $class = Mage::getModel('CrmTicket/Customer_Object')->getClassByType($objectType);
            if ($class) {
                try {
                    $urlInfo = $class->getObjectAdminLink($objectId);
                    $url = $this->getUrl($urlInfo['url'], $urlInfo['param']);
                    $title = $class->getObjectTitle($objectId);
                    return '<a href="' . $url . '" target="_blank">' . $title . '</a>';

                } catch (Exception $ex) {
                    return '<font color="red">Error : ' . $ex->getMessage() . ' ('.  get_class($class).')</font>';
                }
            }
        }
    }

}
