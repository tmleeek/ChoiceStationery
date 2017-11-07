<?php

class MDN_Quotation_AdminController extends Mage_Adminhtml_Controller_Action {

    /**
     * Select customer for new quote
     */
    public function SelectOrCreateCustomerAction() {
        $this->loadLayout();
        $this->renderLayout();
    }

    /**
     * Edit quote
     */
    public function editAction() {
        $quoteId = $this->getRequest()->getParam('quote_id');
        $quote = Mage::getModel('Quotation/Quotation')->load($quoteId);
        Mage::register('current_quote', $quote);
        if (!$quote->hasRealProduct())
            Mage::getSingleton('adminhtml/session')->addError($this->__('Quote must contain at least one real product'));

        // check product status (stock, enabled, fake or true)
        $check = $quote->checkProducts();

        if ($check['error'] === true)
            Mage::getSingleton('adminhtml/session')->addError($this->__($check['message']));
        $this->loadLayout();
        $this->renderLayout();
    }

    /**
     * Print quote
     */
    public function printAction() {
        try {

            $this->loadLayout();
            $error = false;
            $quoteId = $this->getRequest()->getParam('quote_id');
            $quote = Mage::getModel('Quotation/Quotation')->load($quoteId);

            //create bundle product if not exists
            if (($quote->getproduct_id() == null) || ($quote->getproduct_id() == 0)) {
                if ($quote->getItems()->getSize() > 0)
                    $quote->commit();
                else {
                    $error = true;
                    Mage::getSingleton('adminhtml/session')->addError($this->__('Impossible to print an empty quotation.'));
                }
            }

            //continue....
            if (!$error) {
                $pdf = Mage::getModel('Quotation/quotationpdf')->getPdf(array($quote));
                $name = Mage::helper('quotation')->__('quotation_') . $quote->getincrement_id() . '.pdf';
                $this->_prepareDownloadResponse($name, $pdf->render(), 'application/pdf');
            }
            else
                $this->_redirect('Quotation/Admin/edit', array('quote_id' => $quoteId));
        } catch (Exception $ex) {
            Mage::getSingleton('adminhtml/session')->addError($ex->getMessage());
            $this->_redirectReferer();
        }
    }

    /**
     * Load layout to create a new quote
     */
    public function newAction() {
        $this->loadLayout();
        $this->renderLayout();
    }

    /**
     * Save edited quote
     */
    public function postAction() {

        $post = $this->getRequest()->getPost();
        $data = $post['myform'];
        $quoteId = $post["myform"]["quotation_id"];
        $quote = Mage::getModel("Quotation/Quotation")->load($quoteId);

        //set quotation information
        foreach ($data as $key => $value) {
            $quote->setData($key, $value);
        }

        //process attachment
        try {
            $delete = (isset($post['delete_attachment']) ? $post['delete_attachment'] : 0);
            $this->postProcessAttachment($quote, $delete);
        } catch (Exception $ex) {
            Mage::getSingleton('adminhtml/session')->addError($this->__('Error processing attachment: %s', $ex->getMessage()));
        }

        //save products information
        try {
            $this->postProcessSaveProducts($quote, $post);
        } catch (Exception $ex) {
            Mage::getSingleton('adminhtml/session')->addError($this->__('Error saving products: %s', $ex->getMessage()));
        }

        //add products (standard or fake)
        try {
            $this->postProcessAddProducts($quote, $post);
        } catch (Exception $ex) {
            Mage::getSingleton('adminhtml/session')->addError($this->__('Error adding products: %s', $ex->getMessage()));
        }

        // commercial part
        try {
            $proposalData = (isset($post["myform"]["proposal"]) ? $post["myform"]["proposal"] : array());
            Mage::helper('quotation/Proposal')->save($proposalData, $quote);
        } catch (Exception $ex) {
            Mage::getSingleton('adminhtml/session')->addError($this->__('Error saving proposal: %s', $ex->getMessage()));
        }

        //shipping
        $this->postProcessShipping($quote, $post);

        //update datas
        if ($quote->getauto_calculate_price())
            $quote->CalculateQuotationPriceHt();
        if ($quote->getauto_calculate_weight())
            $quote->CalculateWeight();


        //delete associated product & promotion
        Mage::getModel('Quotation/Quotation_Bundle')->deleteBundle($quote);
        Mage::getModel('Quotation/Quotation_Promotion')->deletePromotion($quote);

        //save
        $quote->save();

        //confirm & redirect
        Mage::getSingleton('adminhtml/session')->addSuccess($this->__('quotation successfully saved.'));
        $this->_redirect('Quotation/Admin/edit', array('quote_id' => $quoteId, 'tab' => $this->getRequest()->getParam('tab_to_display')));
    }

    /**
     * Process PDF attachment
     */
    protected function postProcessAttachment($quote, $delete) {
        if (isset($_FILES['upload_pdf']) && $_FILES['upload_pdf']['name'] != "") {
            $pdfAdditional = $_FILES['upload_pdf'];
            $uploader = new Varien_File_Uploader($pdfAdditional);
            $uploader->setAllowedExtensions(array('pdf'));
            $uploader->setAllowCreateFolders(true);
            $uploader->setAllowRenameFiles(true);
            $uploader->setFilesDispersion(false);
            $directory = Mage::helper('quotation/Attachment')->getAttachmentDirectory();
            $fileName = Mage::helper('quotation/Attachment')->getFileName($quote);
            $filePath = Mage::helper('quotation/Attachment')->getAttachmentPath($quote);
            if (file_exists($filePath))
                unlink($filePath);
            $uploader->save($directory, $fileName);
        }
        else {
            if ($delete) {
                $filePath = Mage::helper('quotation/Attachment')->getAttachmentPath($quote);
                if (file_exists($filePath))
                    unlink($filePath);
            }
        }
    }

    /**
     * Add products to quote (if applicable)
     */
    protected function postProcessAddProducts($quote, $post) {

        //add fake product
        if ($post['fake_name'] != '') {
            $quote->addFakeProduct($post['fake_name'], $post['fake_qty'], $post['fake_price'], $post['fake_weight']);
        }

        //add "regular" products
        if (isset($post['add_product_log'])) {
            $addString = $post['add_product_log'];
            $lines = explode(';', $addString);
            foreach ($lines as $line) {
                $t = explode('=', $line);
                if (count($t) == 2) {
                    $qty = $t[1];
                    $productId = str_replace('add_qty_', '', $t[0]);
                    $quote->addProduct($productId, $qty);
                }
            }
        }
    }

    /**
     * Process shipping
     */
    protected function postProcessShipping($quote, $post) {

        $shippingMethod = null;
        $shippingDescription = null;
        $shippingRate = null;

        if ($post["myform"]["shipping_method"]) {
            $shippingMethod = $post["myform"]["shipping_method"];
            $shippingObject = Mage::helper('quotation/ShippingRates')->getRate($quote, $quote->GetCustomerAddress(), $shippingMethod);
            $shippingDescription = $shippingObject['carrier_title'] . ' / ' . $shippingObject['method_title'];
            $shippingRate = $shippingObject['price'];
        } else {
            $shippingMethod = '';
            $shippingDescription = '';
            $shippingRate = '';
        }

        //save
        $quote->setshipping_method($shippingMethod)
                ->setshipping_description($shippingDescription)
                ->setshipping_rate($shippingRate);
    }

    /**
     * Save product information
     */
    protected function postProcessSaveProducts($quote, $post) {

        foreach ($quote->getItems() as $item) {

            if (isset($post["remove_" . $item->getId()])) {
                $remove = $post["remove_" . $item->getId()];
                if ($remove) {
                    $item->delete();
                    continue;
                }
            }

            $exclude = 0;
            if (isset($post["exclude_" . $item->getId()]))
                $exclude = $post["exclude_" . $item->getId()];
            $weight = $post["weight_" . $item->getId()];

            //retrieve options and serialize
            $options = '';
            $optionsValue = array();
            $product = Mage::getModel('catalog/product')->load($item->getproduct_id());
            foreach ($product->getProductOptionsCollection() as $option) {
                switch ($option->getType()) {
                    case Mage_Catalog_Model_Product_Option::OPTION_TYPE_MULTIPLE:
                    case Mage_Catalog_Model_Product_Option::OPTION_TYPE_CHECKBOX:
                        $values = array();
                        foreach ($option->getValues() as $possibleValue) {
                            $chName = 'product_' . $item->getId() . '_option_' . $option->getId() . '_' . $possibleValue->getId();
                            if (isset($post[$chName]))
                                $values[] = $possibleValue->getId();
                        }
                        if (count($values) > 0)
                            $optionsValue[$option->getId()] = $values;
                        break;
                    case Mage_Catalog_Model_Product_Option::OPTION_TYPE_DATE:
                        $optionFieldName = 'product_' . $item->getid() . '_option_' . $option->getid();
                        if ((isset($post[$optionFieldName])) && ($post[$optionFieldName] != '')) {

                            $tmp = explode('-', $post[$optionFieldName]);

                            if (count($tmp) >= 3) {
                                $optionsValue[$option->getid()] = array(
                                    'year' => (int) $tmp[0],
                                    'month' => (int) $tmp[1],
                                    'day' => (int) $tmp[2]
                                );
                            }
                        }
                        break;
                    default:
                        $optionFieldName = 'product_' . $item->getId() . '_option_' . $option->getId();
                        if ((isset($post[$optionFieldName])) && ($post[$optionFieldName] != ''))
                            $optionsValue[$option->getId()] = $post[$optionFieldName];
                        break;
                }
            }
            $options = Mage::helper('quotation/Serialization')->serializeObject($optionsValue);

            //save data
            $item
                    ->setorder($post["order_" . $item->getId()])
                    ->setcaption($post["caption_" . $item->getId()])
                    ->setsku($post["sku_" . $item->getId()])
                    ->setweight($weight)
                    ->setqty($post["qty_" . $item->getId()])
                    ->setprice_ht($post["price_ht_" . $item->getId()])
                    ->setdiscount_purcent($post["discount_purcent_" . $item->getId()])
                    ->setexclude($exclude)
                    ->setoptions($options);

            if (isset($post["description_" . $item->getId()]))
                $item->setdescription($post["description_" . $item->getId()]);
            $item->save();
        }

        $quote->resetItems();
    }

    /**
     * Create new quote
     */
    public function createAction() {

        $post = $this->getRequest()->getPost();
        $customerId = $post["myform"]["customer_id"];
        $caption = $post["myform"]["caption"];
        $manager = Mage::getSingleton('admin/session')->getUser()->getusername();

        //create quote
        $quote = Mage::getModel("Quotation/Quotation")
                ->setCaption($caption)
                ->setcustomer_id($customerId)
                ->setmanager($manager)
                ->save();

        //confirm & redirect
        Mage::getSingleton('adminhtml/session')->addSuccess($this->__('quotation successfully created.'));
        $this->_redirect('Quotation/Admin/edit', array('quote_id' => $quote->getId()));
    }

    /**
     * Delete quote
     */
    public function deleteAction() {
        try {
            $quoteId = $this->getRequest()->getParam('quote_id');
            $quote = Mage::getModel("Quotation/Quotation")->load($quoteId);
            $quote->delete();
            Mage::getSingleton('adminhtml/session')->addSuccess($this->__('Quotation successfully deleted.'));
        } catch (Exception $ex) {
            Mage::getSingleton('adminhtml/session')->addError('Error while deleting Quotation: ' . $ex->getMessage());
        }
        $this->_redirect('Quotation/Admin/List');
    }

    /**
     * Display quote grid
     */
    public function ListAction() {
        $this->loadLayout();
        $this->renderLayout();
    }

    /**
     * Quote duplication (select customer)
     *
     */
    public function DuplicateAction() {
        $this->loadLayout();

        $quoteId = $this->getRequest()->getParam('quotation_id');
        $quote = Mage::getModel('Quotation/Quotation')->load($quoteId);
        Mage::register('current_quote', $quote);

        $this->getLayout()->getBlock('quotationduplicatecustomer')->setMode('duplicate');
        $this->renderLayout();
    }

    /**
     * Duplicate quote
     *
     */
    public function ApplyDuplicateAction() {

        $quoteId = $this->getRequest()->getParam('quotation_id');
        $customerId = $this->getRequest()->getParam('customer_id');
        $oldQuotation = Mage::getModel('Quotation/Quotation')->load($quoteId);
        $newQuotation = $oldQuotation->duplicate($customerId);

        //Confirm & redirect
        Mage::getSingleton('adminhtml/session')->addSuccess($this->__('Quotation successfully duplicated.'));
        $this->_redirect('Quotation/Admin/edit', array('quote_id' => $newQuotation->getId()));
    }

    /**
     * Get products grid
     *
     */
    public function productSelectionGridAction() {
        $quoteId = $this->getRequest()->getParam('quote_id');
        $quote = Mage::getModel('Quotation/Quotation')->load($quoteId);
        Mage::register('current_quote', $quote);

        $block = $this->getLayout()->createBlock('Quotation/Adminhtml_ProductSelection');
        $this->getResponse()->setBody($block->toHtml());
    }

    /**
     * Return customer grid
     */
    public function customerSelectionGridAction() {
        $this->loadLayout();
        $mode = $this->getRequest()->getParam('mode');
        $quoteId = $this->getRequest()->getParam('quotation_id');
        if ($quoteId) {
            $quote = Mage::getModel('Quotation/Quotation')->load($quoteId);
            Mage::register('current_quote', $quote);
        }
        $block = $this->getLayout()->createBlock('Quotation/Adminhtml_SelectCustomer');
        $block->setMode($mode);
        $this->getResponse()->setBody($block->toHtml());
    }

    /**
     * Notify customer
     */
    public function notifyAction() {
        try {
            $quoteId = $this->getRequest()->getParam('quote_id');
            $quote = Mage::getModel('Quotation/Quotation')->load($quoteId);
            $quote->NotifyCustomer();

            //confirm
            Mage::getSingleton('adminhtml/session')->addSuccess($this->__('customer successfully notified.'));
        } catch (Exception $ex) {
            Mage::getSingleton('adminhtml/session')->addError($this->__('Unable to notify customer : %s', $ex->getMessage()));
        }

        //redirect
        $this->_redirect('Quotation/Admin/edit', array('quote_id' => $quoteId));
    }

    /**
     * Remind customer
     */
    public function RemindCustomerAction() {
        try {
            $quoteId = $this->getRequest()->getParam('quote_id');
            $quote = Mage::getModel('Quotation/Quotation')->load($quoteId);
            mage::getModel('Quotation/Quotation_Reminder')->sendCustomerReminder($quote);

            //confirm
            Mage::getSingleton('adminhtml/session')->addSuccess($this->__('Customer successfully reminded.'));
        } catch (Exception $ex) {
            Mage::getSingleton('adminhtml/session')->addError($this->__('An error occured : %s', $ex->getMessage()));
        }
        $this->_redirect('Quotation/Admin/edit', array('quote_id' => $quoteId));
    }

    /**
     * Ajax refresh for quote grid in customer view
     */
    public function SelectedQuotationGridAction() {
        $this->loadLayout();
        $this->getResponse()->setBody(
                $this->getLayout()->createBlock('Quotation/Adminhtml_Customer_Edit_Tab_Quotations')->setData('AjaxGrid', true)->toHtml()
        );
    }

    /**
     * Remove attached PDF
     */
    public function DeleteAdditionalPdfAction() {
        $quoteId = $this->getRequest()->getParam('quote_id');
        $quote = Mage::getModel('Quotation/Quotation')->load($quoteId);
        $path = Mage::helper('quotation/Attachment')->getAttachmentDirectory();
        $file = $path . $quote->getadditional_pdf();

        if (file_exists($file))
            unlink($file);

        $quote->setadditional_pdf('');
        $quote->save();

        Mage::getSingleton('adminhtml/session')->addSuccess($this->__('Additional pdf deleted.'));

        $this->_redirect('Quotation/Admin/edit', array('quote_id' => $quoteId));
    }

    /**
     * Download attached PDF
     */
    public function DownloadAdditionalPdfAction() {
        $quoteId = $this->getRequest()->getParam('quote_id');
        try {
            $quote = Mage::getModel('Quotation/Quotation')->load($quoteId);
            $filePath = Mage::helper('quotation/Attachment')->getAttachmentPath($quote);
            $content = file_get_contents($filePath);
            $this->_prepareDownloadResponse($quote->getadditional_pdf() . '.pdf', $content, 'application/pdf');
        } catch (Exception $ex) {
            Mage::getSingleton('adminhtml/session')->addSuccess($this->__('Unable to download attachment : %s', $ex->getMessage()));
            $this->_redirect('Quotation/Admin/edit', array('quote_id' => $quoteId));
        }
    }

    /**
     * Ajax history grid
     */
    public function gridAjaxAction() {
        try {
            $quoteId = $this->getRequest()->getParam('quote_id');
            $block = $this->getLayout()->createBlock('Quotation/Adminhtml_History');
            $block->setCurrentQuote($quoteId);

            $this->getResponse()->setBody(
                    $block->toHtml()
            );
        } catch (Exception $e) {
            $this->getResponse()->setBody($e->getMessage() . ' : ' . $e->getTraceAsString());
        }
    }

    /**
     * Check ACL
     */
    protected function _isAllowed() {
        return Mage::getSingleton('admin/session')->isAllowed('customer/quotation_list');
    }

}