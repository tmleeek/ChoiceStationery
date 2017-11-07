<?php
/**
 * @category Customer Service Version 3.0
 * @package MageB2B_Sublogin
 * @author AIRBYTES GmbH <info@airbytes.de>
 * @copyright AIRBYTES GmbH
 * @license commercial
 * @date 09.01.2016
 */
class MageB2B_Sublogin_Model_Acl extends Mage_Core_Model_Abstract
{
    public function _construct()
    {
        parent::_construct();
        $this->_init('sublogin/acl');
    }
    
	/**
	 * throw exception in case of identifier is already created
	 * in the past
	 */
    protected function _beforeSave()
    {
		if (!$this->isIdentifierUnique()) {
			throw new Exception (Mage::helper('sublogin')->__('Identifier \'%s\' already exist.', $this->getIdentifier()));
		}
	}
    
    /*
     * function will check whether identifier is unique or 
	 * @return boolean
     */ 
    public function isIdentifierUnique($identifier = null)
    {
		if (!$identifier) {
			if (!($identifier = $this->getIdentifier())) {
				throw new Exception (Mage::helper('sublogin')->__('Identifier should not be blank.', $identifier));
			}
		}
		
		$collection = $this->getCollection()
			->addFieldToFilter('identifier', $identifier);
		// check for edit mode
		if ($this->getId()) {
			if ($collection->count() > 0) {
				$firstItem = $collection->getFirstItem();
				if ($this->getId() != $firstItem->getId()) {
					return false; // identifier is not unique and used by some other instance, so this identifier is not allowed
				}
			}
		}
		// new
		else {
			return ($collection->count() > 0) ? false : true;
		}
		return true;
	}
}
