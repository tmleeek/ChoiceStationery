<?php
/**
 * OneStepCheckout
 * 
 * NOTICE OF LICENSE
 *
 * This source file is subject to One Step Checkout AS software license.
 *
 * License is available through the world-wide-web at this URL:
 * https://www.onestepcheckout.com/LICENSE.txt
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to mail@onestepcheckout.com so we can send you a copy immediately.
 *
 * @category   Idev
 * @package    Idev_OneStepCheckout
 * @copyright  Copyright (c) 2009 OneStepCheckout  (https://www.onestepcheckout.com/)
 * @license    https://www.onestepcheckout.com/LICENSE.txt
 */

class Idev_OneStepCheckout_Block_Adminhtml_Version extends Mage_Adminhtml_Block_System_Config_Form_Fieldset
{
    protected $_fieldRenderer;

    public function render(Varien_Data_Form_Element_Abstract $element)
    {
        $html = '
            <tr>
                <td class="label"><label for="'.$element->getHtmlId().'">'.$element->getLabel().'</label></td>
                <td class="value" id="version_info">'.Mage::getConfig()->getNode('modules/Idev_OneStepCheckout')->version.'</td>
            </tr>
        ';

        return $html;
    }

    protected function _getFieldRenderer()
    {
        if (empty($this->_fieldRenderer)) {
            $this->_fieldRenderer = Mage::getBlockSingleton('adminhtml/system_config_form_field');
        }

        return $this->_fieldRenderer;
    }

}
