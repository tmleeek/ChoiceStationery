<?php
class Magestore_Pdfinvoiceplus_Block_Adminhtml_Pdfinvoiceplus_Edit_Tab_Systemtemplate extends Mage_Adminhtml_Block_Widget_Form {

    protected function _prepareForm() {
        $form = new Varien_Data_Form();
        $this->setForm($form);
        
        $fieldset = $form->addFieldSet('pdfinvoiceplus_imageshow', array(
            'legend' => Mage::helper('pdfinvoiceplus')->__('Select a template to apply'),
            'class' => 'fieldset-wide'
        ));
        
        $fieldset->addField('image', 'text', array(
            'name' => 'image',
        ));
//        $fieldset2 = $form->addFieldSet('pdfinvoiceplus_templateupload', array(
//            'legend' => Mage::helper('pdfinvoiceplus')->__('Add new template'),
//            'class' => 'fieldset-wide'
//        ));
//         $fieldset2->addField('template_upload', 'file', array(
//            'label' => Mage::helper('pdfinvoiceplus')->__('Upload template'),
//            'name'      => 'template_upload',
//            'after_element_html' => '
//                <button type="button" onclick="uploadTemplate()">Upload</button>
//                <script type="text/javascript">
//                    function uploadTemplate(){
//                        editForm.submit($("edit_form").action+"back/edit/");
//                    }
//                </script>
//            ' 
//        ));  
        
        $form->getElement('image')->setRenderer(
    		Mage::app()->getLayout()->createBlock(
		'pdfinvoiceplus/adminhtml_pdfinvoiceplus_edit_tab_renderer_imageshow'));

    }
}
?>
