<?php

class Magestore_Pdfinvoiceplus_Block_Adminhtml_Pdfinvoiceplus_Edit_Tab_Information extends Mage_Adminhtml_Block_Widget_Form {

    protected function _prepareForm() {
        $form = new Varien_Data_Form();
        $this->setForm($form);

        if (Mage::getSingleton('adminhtml/session')->getPdfinvoiceplusData()) {
            $data = Mage::getSingleton('adminhtml/session')->getPdfinvoiceplusData();
            Mage::getSingleton('adminhtml/session')->setPdfinvoiceplusData(null);
        } elseif (Mage::registry('pdfinvoiceplus_data')) {
            $data = Mage::registry('pdfinvoiceplus_data')->getData();
        }
        
        $fieldset = $form->addFieldset('pdfinvoiceplus_information', array(
            'legend' => Mage::helper('pdfinvoiceplus')->__('General Information')
            ));
        
        $fieldset->addField('template_name', 'text', array(
            'label' => Mage::helper('pdfinvoiceplus')->__('Template Name'),
            'name' => 'template_name',
            'class' => 'required-entry',
            'required' => true,
        ));
        
        //end filename
        $fieldset->addField('note', 'textarea', array(
            'label' => Mage::helper('pdfinvoiceplus')->__('Notes'),
            'name' => 'note',
            'after_element_html' =>'
                                     <a id="note_info" href="JavaScript:void(0);">(view example)</a>
                                     <script stype="text/javascript">
                                        var tip = new Tooltip("note_info","'.Mage::getBaseUrl(Mage_Core_Model_Store::URL_TYPE_MEDIA).'magestore/pdfinvoiceplus/tooltip/note.png");
                                     </script>
                                   '
        ));
        $fieldset->addField('terms_conditions','textarea',array(
            'label' => Mage::helper('pdfinvoiceplus')->__('Terms And Conditions'),
            'name'  => 'terms_conditions',
            'after_element_html' =>'
                                     <a id="terms_info" href="JavaScript:void(0);">(view example)</a>
                                     <script stype="text/javascript">
                                        var tip = new Tooltip("terms_info","'.Mage::getBaseUrl(Mage_Core_Model_Store::URL_TYPE_MEDIA).'magestore/pdfinvoiceplus/tooltip/termscondition.png");
                                     </script>
                                   '
        ));
        $fieldset->addField('footer', 'textarea', array(
            'label' => Mage::helper('pdfinvoiceplus')->__('Footer'),
            'name' => 'footer',
            'after_element_html' =>'
                                     <a id="footer_info" href="JavaScript:void(0);">(view example)</a>
                                     <script stype="text/javascript">
                                        var tip = new Tooltip("footer_info","'.Mage::getBaseUrl(Mage_Core_Model_Store::URL_TYPE_MEDIA).'magestore/pdfinvoiceplus/tooltip/footer.png");
                                     </script>
                                   '
        ));
        $fieldset->addField('color','text',array(
            'label' => Mage::helper('pdfinvoiceplus')->__('Color'),
            'name' => 'color',
            'class' => 'colorpicker',
            'renderer' => 'pdfinvoiceplus/adminhtml_pdfinvoiceplus_edit_tab_renderer_color'
        ));
        $form->getElement('color')->setRenderer(
    		Mage::app()->getLayout()->createBlock(
		'pdfinvoiceplus/adminhtml_pdfinvoiceplus_edit_tab_renderer_color'));
        if(Mage::helper('pdfinvoiceplus')->useMultistore()){
            if (!Mage::app()->isSingleStoreMode()) {
                $fieldset->addField('stores', 'multiselect', array(
                    'name' => 'stores[]',
                    'label' => Mage::helper('pdfinvoiceplus')->__('Store View'),
                    'title' => Mage::helper('pdfinvoiceplus')->__('Store View'),
                    'required' => true,
                    'values' => Mage::getSingleton('adminhtml/system_store')->getStoreValuesForForm(false, true),
                ));
            } else {
                $fieldset->addField('store_id', 'hidden', array(
                    'name' => 'stores[]',
                    'value' => Mage::app()->getStore(true)->getId(),
                ));
            }
        }
        if(!$this->getRequest()->getParam('id'))
            $data['barcode']=2;
        $fieldset->addField('barcode','select',array(
            'label' => Mage::helper('pdfinvoiceplus')->__('Show Barcode'),
            'name' => 'barcode',
            'options' => array(
                1 => 'Yes',
                2 => 'No'
            ),
            'after_element_html' => '
                                        <a id="barcode_info" href="JavaScript:void(0);">(view example)</a>
                                        <script type="text/javascript">
                                            var tip = new Tooltip("barcode_info","'.Mage::getBaseUrl(Mage_Core_Model_Store::URL_TYPE_MEDIA).'magestore/pdfinvoiceplus/tooltip/barcode.png");
                                        </script>
                                    '
        ));
        
        $fieldset->addField('barcode_type','select',array(
            'label' => Mage::helper('pdfinvoiceplus')->__('Type of Barcode'),
            'name' => 'barcode_type',
            'options' => array(
                'EAN13' => 'EAN-13',
                'UPCA' => 'UPC-A',
                'UCPE' => 'UCP-E',
                'EAN8' => 'EAN-8',
                'IMB'  => 'Intelligent Mail Barcode',
                'RM4SCC' => 'Royal Mail 4-state Customer Barcode',
                'KIX' => 'Royal Mail 4-state Customer Barcode(Dutch)',
                'POSTNET' => 'POSTNET',
                'PLANET'  => 'PLANET',
                'C128A'   => 'Code 128',
                'EAN128A' => 'EAN-128',
                'C39'   => 'Code 39',
                'S25'   => 'Standard 2 of 5',
                'C93'   => 'Code 93',
                'MSI'   => 'MSI',
                'CODABAR'   => 'CODABAR',
                'CODE11'    => 'Code 11'
            )
        ));
        if(!$this->getRequest()->getParam('id'))
            $data['display_images']=2;
        $fieldset->addField('display_images','select',array(
            'label' => Mage::helper('pdfinvoiceplus')->__('Show Images Product'),
            'name'  => 'display_images',
            'options'=> array(
                1   => 'Yes',
                2   => 'No'
            ),
        ));
        

        
        
        $fieldset->addField('format', 'select', array(
            'label' => Mage::helper('pdfinvoiceplus')->__('Paper Size'),
            'name' => 'format',
            'class' => 'required-entry',
            'required' => true,
            'options'  => array(
                'Letter' => 'Letter',
                'A4' => 'A4',
                'A5' => 'A5',
            )
        ));
        
        $fieldset->addField('orientation', 'select', array(
            'label' => Mage::helper('pdfinvoiceplus')->__('Page orientation'),
            'name' => 'orientation',
            'class' => 'required-entry',
            'required' => true,
            'options'  => array(
                0 => 'Portrait',
                1 => 'Landscape'
            )
        ));
        $fieldset->addField('status', 'select', array(
            'label' => Mage::helper('pdfinvoiceplus')->__('Status'),
            'name' => 'status',
            'class' => 'required-entry',
            'required' => true,
            'options' => array(
                1 => 'Active',
                2 => 'Inactive'
            )
        ));
        
        $form->setValues($data);
        return parent::_prepareForm();
    }
    public function getVariablesWysiwygActionUrl($type){
        return Mage::getSingleton('adminhtml/url')->getUrl('*/adminhtml_variable/wysiwygPlugin').'type/'.$type;
    }
}

?>
