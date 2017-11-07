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
class MDN_CrmTicket_Model_System_Config_Source_Status extends Mage_Eav_Model_Entity_Attribute_Source_Abstract
{
    protected $_options;

    public function toOptionArray($isMultiselect=false)
    {
        if (!$this->_options)
        {
            $this->getAllOptions();
        }
        return $this->_options;
    }

    public function getAllOptions()
    {
        if (!$this->_options) {
        	$this->_options = array();

			$collection = mage::getModel('CrmTicket/Ticket')->getStatuses();

			foreach($collection as $statusId => $statusName)
			{
	        	$this->_options[] = array(
                    'value' => $statusId,
                    'label' => $statusName);
			}
        }
        return $this->_options;
    }

}