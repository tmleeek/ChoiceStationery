<?php
    class Magestore_Pdfinvoiceplus_Block_Adminhtml_Pdfinvoiceplus_Edit_Tab_Companyinfo extends Mage_Adminhtml_Block_Widget_Form {

    protected function _prepareForm() {
        $form = new Varien_Data_Form();
        $this->setForm($form);

        if (Mage::getSingleton('adminhtml/session')->getPdfinvoiceplusData()) {
            $data = Mage::getSingleton('adminhtml/session')->getPdfinvoiceplusData();
            Mage::getSingleton('adminhtml/session')->setPdfinvoiceplusData(null);
        } elseif (Mage::registry('pdfinvoiceplus_data')) {
            $data = Mage::registry('pdfinvoiceplus_data')->getData();
        }

        $fieldset =$form->addFieldSet('pdfinvoiceplus_companyinformation', array(
            'legend' => Mage::helper('pdfinvoiceplus')->__('Business info <a id="business_info" href="JavaScript:void(0);">(?)</a>'),
            
            )); 
        
        $fieldset->addField('company_name', 'text', array(
            'label' => Mage::helper('pdfinvoiceplus')->__('Company Name'),
            'name' => 'company_name',
            'class' => 'required-entry',
            'required' => true,
            'after_element_html' =>'
                                     <script stype="text/javascript">
                                        var tip = new Tooltip("business_info","'.Mage::getBaseUrl(Mage_Core_Model_Store::URL_TYPE_MEDIA).'magestore/pdfinvoiceplus/tooltip/businessinfo.png");
                                     </script>
                                   '
        ));
        
        $fieldset->addField('vat_number', 'text', array(
            'label' => Mage::helper('pdfinvoiceplus')->__('VAT Number'),
            'name' => 'vat_number',
        ));
        
        $fieldset->addField('vat_office', 'text', array(
            'label' => Mage::helper('pdfinvoiceplus')->__('VAT Office'),
            'name' => 'vat_office',
        ));
        
        $fieldset->addField('business_id','text',array(
            'label' => Mage::helper('pdfinvoiceplus')->__('Business ID'),
            'name'  => 'business_id',
        ));
        if($this->getRequest()->getParam('id')){
            $address = Mage::getModel('pdfinvoiceplus/template')->getCollection()
            ->addFieldToFilter('template_id',$this->getRequest()->getParam('id'))
            ->getFirstItem()
            ->getCompanyAddress();
        }else{
            $address = Mage::getStoreConfig('sales/identity/address');
        } 
        $fieldset->addField('company_address', 'text', array(
            'label' => Mage::helper('pdfinvoiceplus')->__('Address'),
            'name' => 'company_address',
            'after_element_html'    => '
                <script type="text/javascript">
                    $("company_address").value = "'.$address.'";
                </script>'
        ));
        
        $fieldset->addField('company_logo', 'file', array(
            'label' => Mage::helper('pdfinvoiceplus')->__('Logo'),
            'name' => 'company_logo',
            'class' => 'input-file',
            'after_element_html'    => 
            '<br/><span>
            Logo, will be used in PDF and HTML documents.
            <br/>
            (jpeg, tiff, png) If you see image distortion in PDF, try to use larger image
            </span>'
        ));
        $form->getElement('company_logo')->setRenderer(
    		Mage::app()->getLayout()->createBlock(
		'pdfinvoiceplus/adminhtml_pdfinvoiceplus_edit_tab_renderer_companylogo'));
        
        $fieldset->addField('company_email', 'text', array(
            'label' => Mage::helper('pdfinvoiceplus')->__('Email'),
            'name' => 'company_email',
        ));
        
        $fieldset->addField('company_telephone', 'text', array(
            'label' => Mage::helper('pdfinvoiceplus')->__('Phone'),
            'name' => 'company_telephone',
        ));
        
        $fieldset->addField('company_fax', 'text', array(
            'label' => Mage::helper('pdfinvoiceplus')->__('Fax'),
            'name' => 'company_fax',
        ));
        
        
        $form->setValues($data);
        return parent::_prepareForm();
    }
}
?>
