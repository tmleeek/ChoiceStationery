<?php

class MDN_Quotation_Block_Adminhtml_Edit_Tabs_General extends Mage_Adminhtml_Block_Widget_Form {

    protected $_product;

    /**
     * Return current quote
     */
    public function getQuote()
    {
        return Mage::registry('current_quote');
    }

    /**
     * Constructor
     */
    public function __construct() {

        parent::__construct();
        $this->setHtmlId('general');
        $this->setTemplate('Quotation/Edit/Tab/General.phtml');
    }

    /**
     * Title
     *
     * @return unknown
     */
    public function getTitle() {
        return $this->__('Quotation #%s', $this->getQuote()->getincrement_id());
    }

    /**
     * Return textual scope information (website + store)
     *
     */
    public function getScopeInformation() {
        $website = Mage::getModel('core/website')->load($this->getQuote()->getWebsiteId());
        $store = Mage::getModel('core/store')->load($this->getQuote()->getStoreId());

        $value = $website->getname() . ' / ' . $store->getname();
        return $value;
    }

    /**
     * Return customer information + link
     *
     */
    public function GetCustomerInfo() {
        $customer = $this->getQuote()->getCustomer();
        return "<a href=\"" . $this->GetBackUrl() . "\">" . $customer->getname() . "</a>";
    }

    /**
     * Url to go back to customer account
     */
    public function GetBackUrl() {
        return $this->getUrl('adminhtml/customer/edit', array('id' => $this->getQuote()->getcustomer_id()));
    }

    /**
     * Return delete url
     */
    public function getDeleteUrl() {
        return $this->getUrl('Quotation/Admin/delete', array('quote_id' => $this->getQuote()->getId()));
    }

    /**
     * return associated product
     *
     */
    public function GetProduct() {
        if (($this->_product == null) && ($this->getQuote()->getproduct_id() != null) && ($this->getQuote()->getproduct_id() != 0)) {
            $productId = $this->getQuote()->getproduct_id();
            $this->_product = Mage::getModel('catalog/product')->load($productId);
        }
        return $this->_product;
    }

    /**
     * Return url to view PDF
     */
    public function getViewAttachmentUrl() {
        return $this->getUrl('Quotation/Admin/DownloadAdditionalPdf/', array('quote_id' => $this->getQuote()->getquotation_id()));
    }

    /**
     * Return true if PDF attachment exists
     */
    public function hasAttachment()
    {
        return Mage::helper('quotation/Attachment')->attachmentExists($this->getQuote());
    }

    /**
     * Return carriers
     *
     */
    public function getCarriersAsCombo($name, $value) {
        $retour = '<select name="' . $name . '">';
        $retour .= '<option value=""></option>';
        $config = Mage::getStoreConfig('carriers');
        foreach ($config as $code => $methodConfig) {
            if (Mage::getStoreConfigFlag('carriers/' . $code . '/active')) {
                $selected = '';
                if ($code == $value)
                    $selected = ' selected ';
                $retour .= '<option value="' . $code . '" ' . $selected . '>' . $code . '</option>';
            }
        }

        $retour .= '</select>';
        return $retour;
    }

    /**
     * Return a combo box with yes / no
     */
    public function getYesNoCombo($name, $value, $onChange=null)
    {
        $retour = '<select id="' . $name . '" name="' . $name . '" onchange="'.$onChange.'">';
        $retour .= '<option value="0" '.($value?'':'selected').'>'.$this->__('No').'</option>';
        $retour .= '<option value="1"'.($value?'selected':'').'>'.$this->__('Yes').'</option>';
        $retour .= '</select>';
        return $retour;
    }

    /**
     * Return a dropdown menu with statuses
     *
     * @param unknown_type $name
     * @param unknown_type $value
     */
    public function getStatusesAsCombo($name, $value) {
        $retour = '<select id="' . $name . '" name="' . $name . '">';
        $config = Mage::helper('quotation')->getStatusesAsArray();
        foreach ($config as $code => $caption) {
            $selected = '';
            if ($code == $value)
                $selected = ' selected ';
            $retour .= '<option value="' . $code . '" ' . $selected . '>' . $caption . '</option>';
        }

        $retour .= '</select>';
        return $retour;
    }

    /**
     * return a combobox with users (manager)
     *
     * @param unknown_type $name
     * @param unknown_type $value
     * @return unknown
     */
    public function getUsersAsCombo($name, $value) {
        $retour = '<select id="' . $name . '" name="' . $name . '">';
        $retour .= '<option value="" ></option>';
        $config = Mage::helper('quotation')->getUsers();
        foreach ($config as $code => $caption) {
            $selected = '';
            if ($code == $value)
                $selected = ' selected ';
            $retour .= '<option value="' . $code . '" ' . $selected . '>' . $caption . '</option>';
        }

        $retour .= '</select>';
        return $retour;
    }

    /**
     * return url to print quote
     */
    public function getPrintUrl() {
        return $this->getUrl('Quotation/Admin/print', array('quote_id' => $this->getQuote()->getId()));
    }

    /**
     * Return url to notify customer
     */
    public function getNotifyUrl() {
        return $this->getUrl('Quotation/Admin/notify', array('quote_id' => $this->getQuote()->getId()));
    }

    /**
     * Return url to remind customer
     */
    public function getRemindUrl() {
        return $this->getUrl('Quotation/Admin/RemindCustomer', array('quote_id' => $this->getQuote()->getId()));
    }

    /**
     * Return quote statuses as combobox
     */
    public function getQuoteStatusAsCombo() {

        $html = '';

        $html .= '<select name="myform[bought]" id="bought"/>';
        $html .= '<option value=""></option>';

        foreach (Mage::getModel('Quotation/Quotation')->getBoughtStatusValues() as $k => $v) {

            $selected = ($this->getQuote()->getbought() == $k) ? 'selected' : '';
            $html .= '<option ' . $selected . ' value="' . $k . '">' . Mage::Helper('quotation')->__($v) . '</option>';
        }

        $html .= '</select>';
        return $html;
    }

}
