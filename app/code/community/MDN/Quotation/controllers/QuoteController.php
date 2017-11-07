<?php

class MDN_Quotation_QuoteController extends Mage_Core_Controller_Front_Action {

    /**
     * Check if quote belong to current customer
     * @param <type> $quoteId
     * @return <type>
     */
    protected function checkQuoteOwner($quote) {
        $customerId = Mage::Helper('customer')->getCustomer()->getId();
        if ($quote->getcustomer_id() != $customerId)
            $this->_redirect('');
    }

    /**
     * Quote view
     */
    public function viewAction() {
        try
        {
            $QuoteId = $this->getRequest()->getParam('quote_id');
            $Quote = Mage::getModel('Quotation/Quotation')->load($QuoteId);
            $this->checkQuoteOwner($Quote);
            $this->loadLayout();
            $this->renderLayout();
        }
        catch(Exception $ex)
        {
            Mage::getSingleton('customer/session')->addError($ex->getMessage());
            $this->_redirect('*/*/List');
        }
    }

    /**
     * Print quote
     */
    public function printAction() {
        $QuoteId = $this->getRequest()->getParam('quote_id');
        $quote = Mage::getModel('Quotation/Quotation')->load($QuoteId);
        $this->checkQuoteOwner($quote);
        try {
            $this->loadLayout();
            $quote->commit();
            $pdf = Mage::getModel('Quotation/quotationpdf')->getPdf(array($quote));
            $name = Mage::helper('quotation')->__('quotation_') . $quote->getincrement_id() . '.pdf';
            $this->_prepareDownloadResponseV2($name, $pdf->render(), 'application/pdf');
        } catch (Exception $ex) {
            Mage::getSingleton('checkout/session')->addError($ex->getMessage());
            $this->_redirect('Quotation/Quote/View', array('quote_id' => $QuoteId));
        }
    }

    /**
     * Add quote to cart
     */
    public function commitAction() {

        $quoteId = $this->getRequest()->getParam('quote_id');
        $quote = Mage::getModel('Quotation/Quotation')->load($quoteId);
        $this->checkQuoteOwner($quote);

        try {
            $model = Mage::getModel('Quotation/Quotation_Cart');
            $model->addToCart($quote, $this);
            Mage::getSingleton('checkout/session')->addSuccess($this->__('Quote added to cart'));
            $this->_redirect('checkout/cart');
        } catch (Exception $ex) {
            Mage::getSingleton('checkout/session')->addError($ex->getMessage());
            $this->_redirect('checkout/cart');
        }
    }

    /**
     * Custom download response method for magento multi version compatibility
     */
    protected function _prepareDownloadResponseV2($fileName, $content, $contentType = 'application/octet-stream') {
        $this->getResponse()
                ->setHttpResponseCode(200)
                ->setHeader('Pragma', 'public', true)
                ->setHeader('Cache-Control', 'must-revalidate, post-check=0, pre-check=0', true)
                ->setHeader('Content-type', $contentType, true)
                ->setHeader('Content-Length', strlen($content))
                ->setHeader('Content-Disposition', 'attachment; filename=' . $fileName)
                ->setBody($content);
    }

    /**
     * Display quotes grid
     */
    public function ListAction() {
        $this->loadLayout();
        $this->_initLayoutMessages('customer/session');
        $this->renderLayout();
    }

    /**
     * Redirect customer to authentication page if not logged in and action = CreateRequest
     */
    public function preDispatch() {
        parent::preDispatch();

        $action = $this->getRequest()->getActionName();
        if ($action == 'RequestFromCart') {
            if (!Mage::getSingleton('customer/session')->isLoggedIn()) {
                $this->setFlag('', self::FLAG_NO_DISPATCH, true);
                Mage::getSingleton('customer/session')->addError($this->__('You must be logged in to request for a quotation.'));
                Mage::getSingleton('customer/session')->setBeforeAuthUrl(Mage::getUrl('Quotation/Quote/RequestFromCart', array('_current' => true)));
                $this->_redirectUrl(Mage::helper('customer')->getLoginUrl());
            }
        }
        if ($action == 'IndividualRequest') {
            if (!Mage::getSingleton('customer/session')->isLoggedIn()) {
                $this->setFlag('', self::FLAG_NO_DISPATCH, true);
                Mage::getSingleton('customer/session')->addError($this->__('You must be logged in to request for a quotation.'));
                Mage::getSingleton('customer/session')->setBeforeAuthUrl(Mage::getUrl('Quotation/Quote/IndividualRequest', array('_current' => true, 'product_id' => $this->getRequest()->getParam('product_id'))));
                $this->_redirectUrl(Mage::helper('customer')->getLoginUrl());
            }
        }

        return $this;
    }

    /**
     * Return an array with quote options seralized for quotation module
     *
     * @param unknown_type $quoteItem
     */
    private function getQuoteOptions($quoteItem) {
        $retour = array();

        if ($optionIds = $quoteItem->getOptionByCode('option_ids')) {
            $options = array();
            foreach (explode(',', $optionIds->getValue()) as $optionId) {
                if ($option = $quoteItem->getProduct()->getOptionById($optionId)) {

                    $quoteItemOption = $quoteItem->getOptionByCode('option_' . $option->getId());

                    $group = $option->groupFactory($option->getType())
                                    ->setOption($option)
                                    ->setQuoteItemOption($quoteItemOption);

                    $retour[$option->getId()] = $quoteItemOption->getValue();
                }
            }
        }

        $retour = Mage::helper('quotation/Serialization')->serializeObject($retour);
        return $retour;
    }

    /**
     * Authenticate customer, add quote to cart and redirect to cart
     *
     */
    public function DirectAuthAction() {
        $quote_id = $this->getRequest()->getParam('quote_id');
        $security_key = $this->getRequest()->getParam('security_key');
        $helper = Mage::helper('quotation/DirectAuth');
        $quote = $helper->getQuote($quote_id, $security_key);

        try {
            if ($quote == null)
                throw new Exception($this->__('Request invalid'));

            //authenticate customer
            $helper->authenticateCustomer($quote);

            //go in quote
            $this->_redirect('Quotation/Quote/View', array('quote_id' => $quote_id));
        } catch (Exception $ex) {
            Mage::getSingleton('customer/session')->addError($ex->getMessage());
            $this->_redirect('');
        }
    }

    //*********************************************************************************************************************************************************
    //*********************************************************************************************************************************************************
    //Customer REQUEST
    //*********************************************************************************************************************************************************
    //*********************************************************************************************************************************************************

    /**
     * Create a quote inquiry with cart's products
     *
     */
    public function RequestFromCartAction() {
        $this->loadLayout();
        $this->renderLayout();
    }

    /**
     * Create a quote inquiry with cart's products
     *
     */
    public function CreateIndividualRequestAction() {
        $this->loadLayout();
        $this->renderLayout();
    }

    /**
     * Quote request for one product
     * Disable add to cart button for individual request products : yes/no
     */
    public function IndividualRequestAction()
    {
            $this->loadLayout();
            $this->renderLayout();
    }

    /**
     * Send textual quote request
     *
     */
    public function SendTextualRequestAction() {

        //Create new quotation
        $customerId = Mage::Helper('customer')->getCustomer()->getId();
        $NewQuotation = Mage::getModel('Quotation/Quotation')
                        ->setcustomer_id($customerId)
                        ->setcaption($this->__('New request'))
                        ->setcustomer_msg($this->getRequest()->getPost('description'))
                        ->setcustomer_request(1)
                        ->setstatus(MDN_Quotation_Model_Quotation::STATUS_CUSTOMER_REQUEST)
                        ->save();

        //Notify admin
        $notificationModel = Mage::getModel('Quotation/Quotation_Notification');
        $notificationModel->NotifyCreationToAdmin($NewQuotation);

        //confirm & redirect
        Mage::getSingleton('customer/session')->addSuccess(__('You quotation request has been successfully sent. You will be notified once store administrator will have reply to your request'));
        $this->_redirect('Quotation/Quote/List/');
    }

    /**
     * 
     */
    public function SendIndividualRequestAction()
    {
        //Create new quotation
        $customerId = Mage::Helper('customer')->getCustomer()->getId();
        $NewQuotation = Mage::getModel('Quotation/Quotation')
                        ->setcustomer_id($customerId)
                        ->setcaption($this->__('New request'))
                        ->setcustomer_msg($this->getRequest()->getPost('description'))
                        ->setcustomer_request(1)
                        ->setstatus(MDN_Quotation_Model_Quotation::STATUS_CUSTOMER_REQUEST)
                        ->save();

        //Notify admin
        $notificationModel = Mage::getModel('Quotation/Quotation_Notification');
        $notificationModel->NotifyCreationToAdmin($NewQuotation);

        //add product
        $productId = $this->getRequest()->getPost('product_id');
        $qty = $this->getRequest()->getPost('qty');
        $options = $this->getRequest()->getPost('options');
        $quoteItem = $NewQuotation->addProduct($productId, $qty);
        $quoteItem->setoptions($options)->save();
        
        //confirm & redirect
        Mage::getSingleton('customer/session')->addSuccess(__('You quotation request has been successfully sent. You will be notified once store administrator will have reply to your request'));
        $this->_redirect('Quotation/Quote/List/');
        
    }

    /**
     * Submit request from cart
     */
    public function SendRequestFromCartAction() {
        //Create new quotation
        $customerId = Mage::Helper('customer')->getCustomer()->getId();
        $NewQuotation = Mage::getModel('Quotation/Quotation')
                        ->setcustomer_id($customerId)
                        ->setcaption($this->__('New request'))
                        ->setcustomer_msg($this->getRequest()->getPost('description'))
                        ->setcustomer_request(1)
                        ->setstatus(MDN_Quotation_Model_Quotation::STATUS_CUSTOMER_REQUEST)
                        ->save();

        //add products to quote
        $cartProducts = Mage::helper('checkout/cart')->getCart()->getItems();
        foreach ($cartProducts as $cartProduct) {

            //skip group products
            if (($cartProduct->getProduct()->gettype_id() == 'configurable') || ($cartProduct->getProduct()->gettype_id() == 'bundle') ||($cartProduct->getProduct()->gettype_id() == 'grouped'))
                    continue;

            //set qty
            $qty = $cartProduct->getqty();
            if ($cartProduct->getParentItem())
                $qty = $cartProduct->getqty() * $cartProduct->getParentItem()->getqty();

            //add product
            $quoteItem = $NewQuotation->addProduct($cartProduct->getproduct_id(), $qty);

            //set options
            $quoteItem->setoptions($this->setQuotItemOptionFromCartItem($cartProduct))->save();
        }

        //Notify admin
        $notificationModel = Mage::getModel('Quotation/Quotation_Notification');
        $notificationModel->NotifyCreationToAdmin($NewQuotation);

        //empty cart if configured
        if (Mage::getStoreConfig('quotation/cart_options/empty_cart_after_quote_request'))
            Mage::helper('quotation/Cart')->emptyCart(true);

        //confirm & redirect
        Mage::getSingleton('customer/session')->addSuccess(__('You quotation request has been successfully sent. You will be notified once store administrator will have reply to your request'));
        $this->_redirect('Quotation/Quote/List/');
    }

    /**
     * 
     */
    protected function setQuotItemOptionFromCartItem($cartProduct)
    {
        $selectedOptions = array();

        if ($optionIds = $cartProduct->getOptionByCode('option_ids')) {
            $options = array();
            foreach (explode(',', $optionIds->getValue()) as $optionId) {
                if ($option = $cartProduct->getProduct()->getOptionById($optionId)) {
                    $quoteItemOption = $cartProduct->getOptionByCode('option_' . $option->getId());
                    $group = $option->groupFactory($option->getType())
                                    ->setOption($option)
                                    ->setQuoteItemOption($quoteItemOption);
                    $selectedOptions[$optionId] = $quoteItemOption->getValue();
                }
            }
        }

        return Mage::helper('quotation/Serialization')->serializeObject($selectedOptions);
    }

    //*********************************************************************************************************************************************************
    //*********************************************************************************************************************************************************
    //ANONYMOUS REQUEST
    //*********************************************************************************************************************************************************
    //*********************************************************************************************************************************************************

    /**
     * Display quote request form for anonymous
     *
     */
    public function anonymousQuoteRequestAction() {
        $this->loadLayout();
        $this->renderLayout();
    }

    /**
     * Download attached PDF
     */
    public function DownloadAdditionalPdfAction() {
        $QuoteId = $this->getRequest()->getParam('quote_id');
        $quote = Mage::getModel('Quotation/Quotation')->load($QuoteId);
        $this->checkQuoteOwner($quote);
        $filePath = Mage::helper('quotation/Attachment')->getAttachmentPath($quote);
        $content = file_get_contents($filePath);
        $this->_prepareDownloadResponseV2($quote->getadditional_pdf() . '.pdf', $content, 'application/pdf');
    }

}
