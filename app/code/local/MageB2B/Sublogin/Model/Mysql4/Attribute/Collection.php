<?php
/**
 * @category Customer Service Version 3.0
 * @package MageB2B_Sublogin
 * @author AIRBYTES GmbH <info@airbytes.de>
 * @copyright AIRBYTES GmbH
 * @license commercial
 * @date 09.01.2016
 */
class MageB2B_Sublogin_Model_Mysql4_Attribute_Collection extends Mage_Eav_Model_Resource_Attribute_Collection
{
    /**
     * Default attribute entity type code
     *
     * @var string
     */
    protected $_entityTypeCode   = 'sublogin';

    /**
     * Default attribute entity type code
     *
     * @return string
     */
    protected function _getEntityTypeCode()
    {
        return $this->_entityTypeCode;
    }

    /**
     * Get EAV website table
     *
     * Get table, where website-dependent attribute parameters are stored
     * If realization doesn't demand this functionality, let this function just return null
     *
     * @return string|null
     */
    protected function _getEavWebsiteTable()
    {
        return null;
    }
	
	protected function _initSelect()
	{
		$entityTypeId = (int)Mage::getModel('eav/entity')->setType('sublogin')->getTypeId();
		
		$this->getSelect()
            ->from(array('main_table' => $this->getResource()->getMainTable()))
            ->where('main_table.entity_type_id = ?', $entityTypeId);
        return $this;
	}
}
