<?php

class MDN_quotation_Block_Frontend_View extends Mage_Core_Block_Template {

    private $quote = null;

    /**
     * Get current quote
     */
    public function getQuote() {
        if ($this->quote == null) {
            $QuoteId = $this->getRequest()->getParam('quote_id');
            $model = Mage::getModel('Quotation/Quotation');
            $this->quote = $model->load($QuoteId);

            if ($this->quote->GetLinkedProduct() == null) {
                $this->quote->commit();
            }
        }

        return $this->quote;
    }

    /**
     * Get product price html code
     */
    public function getPriceHtml() {
        $this->setTemplate('catalog/product/price.phtml');
        $this->setProduct($this->getQuote()->GetLinkedProduct());
        return $this->toHtml();
    }

    /**
     * Return url to print quote
     */
    public function getPrintUrl() {
        return $this->getUrl('Quotation/Quote/print', array('quote_id' => $this->getQuote()->getId()));
    }

    /**
     * Return url to add quote to cart
     */
    public function getCommitUrl() {
        return $this->getUrl('Quotation/Quote/commit', array('quote_id' => $this->getQuote()->getId()));
    }

    /**
     * return url to download attached PDF
     */
    public function getViewAttachmentUrl() {

        return $this->getUrl('Quotation/Quote/DownloadAdditionalPdf', array('quote_id' => $this->getQuote()->getId()));
    }

    public function hasAttachment()
    {
        return Mage::helper('quotation/Attachment')->attachmentExists($this->getQuote());
    }

    /**
     * Return bundle content as text
     *
     */
    public function getSubItemsDescription() {

        $retour = '';
        foreach ($this->getQuote()->getItems() as $item) {
            if ($item->getexclude() == 0) {
                $retour .= '<br>' . $item->getqty() . 'x ' . $item->getcaption();
                $options = $item->getOptionsValuesAsText(true);
                if ($options != '')
                    $retour .= '<br>' . $options;
                $retour .= '';
            }
        }

        return $retour;
    }

}
