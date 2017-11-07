<?php

class Magestore_Pdfinvoiceplus_Block_Adminhtml_Pdfinvoiceplus_Edit_Tab_Renderer_Companylogo extends Mage_Adminhtml_Block_Widget implements Varien_Data_Form_Element_Renderer_Interface {

    public function render(Varien_Data_Form_Element_Abstract $element) {
        if (!$this->getRequest()->getParam('id')) {
            $logo = Mage::getStoreConfig('sales/identity/logo');
            $html = '
            <td class="label"><label for="company_logo"> Logo </label></td>
            <td class="value">
            <img width="22" height="22" class="small-image-preview v-middle" alt="' . $logo . '" title="' . $logo . '" id="company_logo_image" src="' . Mage::getBaseUrl(Mage_Core_Model_Store::URL_TYPE_MEDIA) . 'sales/store/logo/' . $logo . '">
            <input type="file" class="input-file" value="" name="company_logo" id="company_logo">
            <span class="delete-image">
            <input type="checkbox" id="company_logo_delete" class="checkbox" value="1" name="company_logo_delete"><label for="company_logo_delete"> Delete Image</label>
            <input type="hidden" value="' . $logo . '" name="company_logo"><br/>
            The most ideal logo size is <strong>160x40</strong> pixels.<br/>
            Logo will be used in PDF and HTML document (jpeg, tiff, png).
            </td>';
        } else {
            $logo = Mage::getModel('pdfinvoiceplus/template')->load($this->getRequest()->getParam('id'))
                ->getCompanyLogo();
            $html = '
            <td class="label"><label for="company_logo"> Company Logo </label></td>
            <td class="value">
            <img width="22" height="22" class="small-image-preview v-middle" alt="' . $logo . '" title="' . $logo . '" id="company_logo_image" src="' . Mage::getBaseUrl(Mage_Core_Model_Store::URL_TYPE_MEDIA).  'magestore/pdfinvoiceplus/'.$logo.'">
            <input type="file" class="input-file" value="" name="company_logo" id="company_logo">
            <span class="delete-image">
            <input type="checkbox" id="company_logo_delete" class="checkbox" value="1" name="company_logo_delete"><label for="company_logo_delete"> Delete Image</label>
            <input type="hidden" value="' . $logo . '" name="company_logo"><br/>
            The most idea logo size is <strong>160x40</strong> pixels.<br/>
            Logo will be used in PDF and HTML document (jpeg, tiff, png).
            </td>';
        }
        return $html;
    }

}

?>
