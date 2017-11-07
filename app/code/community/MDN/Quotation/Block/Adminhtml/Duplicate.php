<?php

class MDN_Quotation_Block_Adminhtml_Duplicate extends Mage_Adminhtml_Block_Widget_Form {

    /**
     * Return current quote
     */
    public function getQuote() {
        return Mage::registry('current_quote');
    }

}