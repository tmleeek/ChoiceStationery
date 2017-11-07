<?php

class MDN_Quotation_Model_Quotation_Promotion extends Mage_Core_Model_Abstract {

    public function createPromotion($quote) {

        $this->deletePromotion($quote);

        //create promotion only if free shipping is enabled
        if ($quote->getfree_shipping() == 1) {
            $product = $quote->GetLinkedProduct();
            if ($product) {
                $promo = Mage::getModel('salesrule/rule');
                $promo->setname('Free Shipping for quotation #' . $quote->getincrement_id());
                $promo->setdescription('Free Shipping for quotation #' . $quote->getincrement_id());
                $promo->setcustomer_group_ids($this->getCustomerGroupIds());
                $promo->setis_active(1);
                $promo->setsimple_free_shipping(1);
                $promo->setwebsite_ids(array($quote->getCustomer()->getWebsiteId()));
                $promo->setsimple_action(Mage_SalesRule_Model_Rule::BY_FIXED_ACTION);
                $promo->setfrom_date(date('Y-m-d'));
                $promo->setto_date($quote->getvalid_end_time());
                $promo->setis_advanced(1);
                $promo->save();

                //set condition
                $SkuLength = strlen($product->getSku());
                $condition = 'a:7:{s:4:"type";s:32:"salesrule/rule_condition_combine";s:9:"attribute";N;s:8:"operator";N;s:5:"value";s:1:"1";s:18:"is_value_processed";N;s:10:"aggregator";s:3:"all";s:10:"conditions";a:1:{i:0;a:7:{s:4:"type";s:38:"salesrule/rule_condition_product_found";s:9:"attribute";N;s:8:"operator";N;s:5:"value";s:1:"1";s:18:"is_value_processed";N;s:10:"aggregator";s:3:"all";s:10:"conditions";a:1:{i:0;a:5:{s:4:"type";s:32:"salesrule/rule_condition_product";s:9:"attribute";s:3:"sku";s:8:"operator";s:2:"==";s:5:"value";s:' . $SkuLength . ':"' . $product->getSku() . '";s:18:"is_value_processed";b:0;}}}}}';
                $sql = "update " . mage::getModel('Quotation/Constant')->getTablePrefix() . "salesrule set conditions_serialized = '" . $condition . "' where rule_id = " . $promo->getrule_id();
                mage::getResourceModel('sales/order_item_collection')->getConnection()->query($sql);

                //save promotion id
                $quote->setpromo_id($promo->getrule_id())->save();
            }
        }
    }

    /**
     * Delete promotion
     *
     */
    public function deletePromotion($quote) {
        if ($quote->getpromo_id() > 0) {
            $promo = Mage::getModel('salesrule/rule')
                            ->load($quote->getpromo_id())
                            ->delete();
            $quote->setpromo_id(0)->save();
        }
    }

    /**
     * Return customer groups ids as array
     */
    protected function getCustomerGroupIds() {
        $ids = Mage::getModel('customer/group')
                        ->getCollection()
                        ->getAllIds();
        return $ids;
    }

}