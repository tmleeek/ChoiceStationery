<?php

class Magestore_Pdfinvoiceplus_Block_Adminhtml_Pdfinvoiceplus_Edit_Tab_Insertvariable extends Mage_Adminhtml_Block_Widget_Form{
    protected function _prepareForm(){
        $form = new Varien_Data_Form();
        $this->setForm($form);
         if (Mage::getSingleton('adminhtml/session')->getPdfinvoiceplusData()) {
            $data = Mage::getSingleton('adminhtml/session')->getPdfinvoiceplusData();
            Mage::getSingleton('adminhtml/session')->setPdfinvoiceplusData(null);
        } elseif (Mage::registry('pdfinvoiceplus_data')) {
            $data = Mage::registry('pdfinvoiceplus_data')->getData();
        }
         //order name
        $fieldset = $form->addFieldset('pdfinvoiceplus_insertvariable', array(
            'legend' => Mage::helper('pdfinvoiceplus')->__('Variable for PDF Order file')
            ));
        
        if(!$this->getRequest()->getParam('id')){
            $order_filename = 'order_{{var order_number}}_{{var order_date}}';
            $invoice_filename = 'invoice_{{var invoice_id}}_{{var invoice_date}}';
            $creditmemo_filename = 'creditmemo_{{var creditmemo_id}}';
            $barcode_order = '{{var order_number}}';
            $barcode_invoice = '{{var invoice_id}}';
            $barcode_creditmemo = '{{var creditmemo_id}}';
        }else{
            $order_filename = $data['order_filename'];
            $invoice_filename = $data['invoice_filename'];
            $creditmemo_filename = $data['creditmemo_filename'];
            $barcode_order = $data['barcode_order'];
            $barcode_invoice = $data['barcode_invoice'];
            $barcode_creditmemo = $data['barcode_creditmemo'];
        }
        
        $fieldset->addField('order_filename', 'text', array(
            'label' => Mage::helper('pdfinvoiceplus')->__('Name to save PDF order'),
            'name' => 'order_filename',
            'class' => 'required-entry',
            'required' => true,
            'after_element_html' => '
                <script type="text/javascript">
                    $("order_filename").value = "'.$order_filename.'";
                </script>'
        ));
        $insertVariableButton = $this->getLayout()
                ->createBlock('adminhtml/widget_button', '', array(
            'type' => 'button',
            'label' => Mage::helper('pdfinvoiceplus')->__('Insert Variable...'),
            'onclick' => 'MagentovariablePlugin.loadChooser(\'' . $this->getVariablesWysiwygActionUrl('order') . '\', \'order_filename\');'
                ));
        $fieldset->addField('insert_variableorder', 'note', array(
            'text' => $insertVariableButton->toHtml(),
            'after_element_html' => '<br/>'.Mage::helper('pdfinvoiceplus')->__('To save an order with custom name including: customer’s name, date, etc.')
        ));
        //barcode order
        $fieldset->addField('barcode_order','text',array(
            'label' =>Mage::helper('pdfinvoiceplus')->__('Information encoded in Barcode on printed Order '),
            'name'  => 'barcode_order',
            'after_element_html' => '
                <script type="text/javascript">
                    $("barcode_order").value = "'.$barcode_order.'";
                </script>'
        ));
        $insertVariableButtonBarcode = $this->getLayout()
                ->createBlock('adminhtml/widget_button', '', array(
            'type' => 'button',
            'label' => Mage::helper('pdfinvoiceplus')->__('Insert Barcode Variable...'),
            'onclick' => 'MagentovariablePlugin.loadChooser(\'' . $this->getVariablesWysiwygActionUrl('order') . '\', \'barcode_order\');'
                ));
        $fieldset->addField('insert_variablebarcodeorder', 'note', array(
            'text' => $insertVariableButtonBarcode->toHtml(),
            'after_element_html' => '<br/>'.Mage::helper('pdfinvoiceplus')->__('To choose information encoded in barcode on printed order, including customer’s name, date, etc.')
        ));
        //end order
        //------------------------------------------------------------------------//
        //invoice name
        $fieldsetinvoice = $form->addFieldset('pdfinvoiceplus_insertvariableinvoice', array(
            'legend' => Mage::helper('pdfinvoiceplus')->__('Variable for PDF Invoice file')
            ));
        $fieldsetinvoice->addField('invoice_filename','text',array(
            'label' => Mage::helper('pdfinvoiceplus')->__('Name to save PDF invoice'),
            'name'  => 'invoice_filename',
            'class' => 'required-entry',
            'required' => true,
            'after_element_html' => '
                <script type="text/javascript">
                    $("invoice_filename").value = "'.$invoice_filename.'";
                </script>'
        ));
        $insertVariableButtonInvoice = $this->getLayout()
                                ->createBlock('adminhtml/widget_button','',array(
              'type'    => 'button',
              'label'   => Mage::helper('pdfinvoiceplus')->__('Insert Variable...'),
              'onclick' => 'MagentovariablePlugin.loadChooser(\''.$this->getVariablesWysiwygActionUrl('invoice').'\',\'invoice_filename\', \'invoice\');'                     
         ));
        $fieldsetinvoice->addField('insert_variableinvoice', 'note', array(
            'text' => $insertVariableButtonInvoice->toHtml(),
            'after_element_html' => '<br/>'.Mage::helper('pdfinvoiceplus')->__('To save an invoice with custom name including: customer’s name, date, etc.')
        ));
        //barcode invoice
        $fieldsetinvoice->addField('barcode_invoice','text',array(
            'label' => Mage::helper('pdfinvoiceplus')->__('Information encoded in Barcode on printed Invoice'),
            'name'  => 'barcode_invoice',
            'after_element_html' => '
                <script type="text/javascript">
                    $("barcode_invoice").value = "'.$barcode_invoice.'";
                </script>'
        ));
        $insertVariableButtonInvoiceBarcode = $this->getLayout()
                                ->createBlock('adminhtml/widget_button','',array(
              'type'    => 'button',
              'label'   => Mage::helper('pdfinvoiceplus')->__('Insert Barcode Variable...'),
              'onclick' => 'MagentovariablePlugin.loadChooser(\''.$this->getVariablesWysiwygActionUrl('invoice').'\',\'barcode_invoice\', \'invoice\');'                     
         ));
        $fieldsetinvoice->addField('insert_variableinvoicebarcode', 'note', array(
            'text' => $insertVariableButtonInvoiceBarcode->toHtml(),
            'after_element_html' => '<br/>'.Mage::helper('pdfinvoiceplus')->__('To choose information encoded in barcode on printed invoice, including customer’s name, date, etc.')
        ));
        
        //end invoice
        //------------------------------------------------------------------------------//
        //creditmemo name
        $fieldsetcreditmemo = $form->addFieldset('pdfinvoiceplus_insertvariablecreditmemo', array(
            'legend' => Mage::helper('pdfinvoiceplus')->__('Variable for PDF Creditmemo file')
            ));
        $fieldsetcreditmemo->addField('creditmemo_filename', 'text', array(
            'label' => Mage::helper('pdfinvoiceplus')->__('Name to save PDF creditmemo'),
            'name' => 'creditmemo_filename',
            'class' => 'required-entry',
            'required' => true,
            'after_element_html' => '
                <script type="text/javascript">
                    $("creditmemo_filename").value = "'.$creditmemo_filename.'";
                </script>'
        ));
        $insertVariableButtonCreditmemo = $this->getLayout()
                ->createBlock('adminhtml/widget_button', '', array(
            'type' => 'button',
            'label' => Mage::helper('pdfinvoiceplus')->__('Insert Variable...'),
            'onclick' => 'MagentovariablePlugin.loadChooser(\'' . $this->getVariablesWysiwygActionUrl('creditmemo') . '\', \'creditmemo_filename\',\'creditmemo\');'
                ));
        $fieldsetcreditmemo->addField('insert_variablecreditmemo', 'note', array(
            'text' => $insertVariableButtonCreditmemo->toHtml(),
            'after_element_html' => '<br/>'.Mage::helper('pdfinvoiceplus')->__('To save an creditmemo with custom name including: customer’s name, date, etc.')
        ));
        //barcode creditmemo
        $fieldsetcreditmemo->addField('barcode_creditmemo', 'text', array(
            'label' => Mage::helper('pdfinvoiceplus')->__('Information encoded in Barcode on printed Credit Memo'),
            'name' => 'barcode_creditmemo',
            'after_element_html' => '
                <script type="text/javascript">
                    $("barcode_creditmemo").value = "'.$barcode_creditmemo.'";
                </script>'
        ));
        $insertVariableButtonCreditmemoBarcode = $this->getLayout()
                ->createBlock('adminhtml/widget_button', '', array(
            'type' => 'button',
            'label' => Mage::helper('pdfinvoiceplus')->__('Insert Barcode Variable...'),
            'onclick' => 'MagentovariablePlugin.loadChooser(\'' . $this->getVariablesWysiwygActionUrl('creditmemo') . '\', \'barcode_creditmemo\',\'creditmemo\');'
                ));
        $fieldsetcreditmemo->addField('insert_variablecreditmemobarcode', 'note', array(
            'text' => $insertVariableButtonCreditmemoBarcode->toHtml(),
            'after_element_html' => '<br/>'.Mage::helper('pdfinvoiceplus')->__('To choose information encoded in barcode on printed creditmemo, including customer’s name, date, etc.')
        ));
        
        //end filename
        
        $form->setValues($data);
        return parent::_prepareForm();
    }
    public function getVariablesWysiwygActionUrl($type){
        if($type == 'order'){
            return Mage::getSingleton('adminhtml/url')->getUrl('*/adminhtml_variable/wysiwygPluginOrder');
        }elseif($type == 'invoice'){
            return Mage::getSingleton('adminhtml/url')->getUrl('*/adminhtml_variable/wysiwygPluginInvoice');
        }else{
            return Mage::getSingleton('adminhtml/url')->getUrl('*/adminhtml_variable/wysiwygPluginCreditmemo');
        }
    }
}
?>
