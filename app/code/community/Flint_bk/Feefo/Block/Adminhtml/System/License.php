<?php
/**
 * Flint Technology Ltd
 *
 * This module was developed by Flint Technology Ltd (http://www.flinttechnology.co.uk).
 * For support or questions, contact us via feefo@flinttechnology.co.uk 
 * Support website: https://www.flinttechnology.co.uk/support/projects/feefo/
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the EULA bundled with this package in the file LICENSE.txt.
 * It is also available online at http://www.flinttechnology.co.uk/store/module-license-1.0
 *
 * @package     flint_feefo-ce-2.0.5.zip
 * @registrant  Paul Andrews, Choice Stationery Supplies
 * @license     FFFEA83A-B2B2-4E66-B4F5-AE27E326AAC3
 * @eula        Flint Module Single Installation License (http://www.flinttechnology.co.uk/store/module-license-1.0
 * @copyright   Copyright (c) 2014 Flint Technology Ltd (http://www.flinttechnology.co.uk)
 */
?>
<?php
//class Flint_Feefo_Block_Adminhtml_System_License extends Mage_Adminhtml_Block_System_Config_Form_Field
class Flint_Feefo_Block_Adminhtml_System_License extends Mage_Adminhtml_Block_System_Config_Form_Field
{

    protected function _getElementHtml( Varien_Data_Form_Element_Abstract $element ) {
        $this->setElement( $element );
        $version = $this->_getVersion();
        return "Company: Choice Stationery Supplies<br/>Key: FFFEA83A-B2B2-4E66-B4F5-AE27E326AAC3<br/>Version: $version </hr>";
    }

    protected function _getVersion() {
        return ( string ) Mage::getConfig()->getModuleConfig( 'Flint_Feefo' )->version;
    }

}
