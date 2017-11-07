<?php
/**
 * Extended Register Form Block
 * 
 * @author     Design:Slider GbR <magento@design-slider.de>
 * @copyright  (C)Design:Slider GbR <www.design-slider.de>
 * @license    OSL <http://opensource.org/licenses/osl-3.0.php>
 * @link       http://www.design-slider.de/magento_onlineshop/magento-extensions/registration-address/
 * @package    DS_RegAddress
 */
class DS_RegAddress_Block_Form_Register extends Mage_Customer_Block_Form_Register
{
    /**
     * Overwritten method to manupilate showAddressFields variable.
     */    
    protected function _prepareLayout()
    {
        $_showAddressFields = false;
        switch (Mage::getStoreConfig('regaddress/defaultconfiguration/extendedregistration', Mage::app()->getStore())) {
            case 'optional_address':
            case 'forced_address':
                $_showAddressFields = true;
                break;
        }
        
        $this->setShowAddressFields($_showAddressFields);
        return parent::_prepareLayout();
    }

    /**
     * Returns information wether the address configuration in backend is set to 'Forced Address" or not.
     * @return bool
     */
    protected function getAddressForced()
    {
        $_addressForced = false;
        if (Mage::getStoreConfig('regaddress/defaultconfiguration/extendedregistration',Mage::app()->getStore()) == 'forced_address') {
            $_addressForced = true;
        }
        
        return $_addressForced;
    }
}