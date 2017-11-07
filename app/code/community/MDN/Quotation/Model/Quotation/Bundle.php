<?php

class MDN_Quotation_Model_Quotation_Bundle extends Mage_Core_Model_Abstract {

    /**
     * Create bundle product for quote
     */
    public function createBundle($quote) {

        //init vars
        $storeId = $quote->getStoreId();
        $attributeSetId = Mage::getStoreConfig('quotation/general/attribute_set_id', $storeId);
        if (!$attributeSetId)
            throw new Exception(Mage::helper('quotation')->__('Default attributeset id is no set in system > configuration > quote !'));
        $TaxId = Mage::getStoreConfig('quotation/general/quotation_tax_class');
        if (!$TaxId)
            throw new Exception(Mage::helper('quotation')->__('Tax class is no set in system > configuration > quote !'));

        $name = Mage::helper('quotation')->__('Quote #%s - %s', $quote->getincrement_id(), $quote->getcaption());
        $sku = Mage::helper('quotation')->__('quotation_') . $quote->getid();

        $old_product = $quote->GetLinkedProduct();
        if ($old_product)
            return $old_product;

        $bundleProductRequired = $this->needBundleProduct($quote);
        $TaxId = Mage::getStoreConfig('quotation/general/quotation_tax_class');

        //If we have to create bundle
        if ($bundleProductRequired) {

            $hasNonProductOption = false;
            $hasProductOption = false;
            foreach ($quote->getItems() as $item) {
                if ($item->getexclude() == 0) {
                    if ($item->getproduct_id() == '')
                        $hasNonProductOption = true;
                    else
                        $hasProductOption = true;
                }
            }

            //Create bundle product
            $product = Mage::getModel('catalog/product')
                            ->setStoreId(0)
                            ->setName($name)
                            ->setDescription($name)
                            ->setShortDescription($name)
                            ->setattribute_set_id($attributeSetId)
                            ->setsku($sku)
                            ->settype_id('bundle')
                            ->setvisibility(Mage_Catalog_Model_Product_Visibility::VISIBILITY_NOT_VISIBLE)
                            ->setstatus(Mage_Catalog_Model_Product_Status::STATUS_ENABLED)
                            ->setPrice($quote->getprice_ht())
                            ->setWebsiteIds(array($quote->getWebsiteId()))
                            ->setprice_type(Mage_Bundle_Model_Product_Price::PRICE_TYPE_FIXED)
                            ->setweight_type(1)
                            ->setweight($quote->getweight())
                            ->settax_class_id($TaxId)
                            ->sethas_options($hasNonProductOption)
                            ->setquotation_id($quote->getId())
                            ->setis_quotation(1);

            //set category
            if (Mage::getStoreConfig('quotation/general/quotation_category_id', $storeId) != '')
                $product->setCategoryIds(Mage::getStoreConfig('quotation/general/quotation_category_id', $storeId));

            $product->save();
            Mage::register('product', $product);

            if (!$product->getId())
                throw new Exception(Mage::helper('quotation')->__('Unable to create bundle product for quote.'));

            //stockManagement (apply excepted for magento 1.4.2.0)
            if (Mage::helper('quotation/MagentoVersionCompatibility')->createStockItemForBundle()) {
                $stockItem = Mage::getModel('cataloginventory/stock_item')
                                ->setproduct_id($product->getId())
                                ->setstock_id(1)
                                ->setis_in_stock(1)
                                ->setmanage_stock(0)
                                ->setuse_config_manage_stock(0)
                                ->save();
            }

            //Add option in bundle to contain simple products
            if ($hasProductOption) {
                $my_option = new Mage_Bundle_Model_Option();
                $my_option->setparent_id($product->getId())
                        ->setrequired(0)
                        ->setposition(0)
                        ->settype('checkbox')
                        ->settitle('Description')
                        ->setstore_id(0)
                        ->save();
            }

            //add option to contain fake products
            $my_custom_option = null;
            if ($hasNonProductOption) {
                $my_custom_option_values = array();
                $my_custom_option = Mage::getModel('catalog/product_option');
                $my_custom_option->setproduct_id($product->getId())
                        ->setis_require(0)
                        ->setsort_order(0)
                        ->settype('checkbox')
                        ->settitle('Services')
                        ->setstore_id(0);
            }

            //Add products in option
            foreach ($quote->getItems() as $item) {
                if ($item->getexclude() == 0) {

                    if ($item->getproduct_id() != '') {
                        $my_product_option = new Mage_Bundle_Model_Selection();
                        $my_product_option->setoption_id($my_option->getId())
                                ->setparent_product_id($product->getId())
                                ->setproduct_id($item->getproduct_id())
                                ->set_position(0)
                                ->setis_default(1)
                                ->setselection_qty($item->getqty())
                                ->setselection_can_change_qty(0)
                                ->save();
                    } else {
                        $value = array();
                        $value['option_type_id'] = -1;
                        $value['title'] = $item->getqty() . ' x ' . $item->getcaption();
                        $value['price'] = 0;
                        $value['price_type'] = 'fixed';
                        $value['sku'] = $item->getsku();
                        $value['sort_order'] = $item->getorder();
                        $my_custom_option_values[] = $value;
                    }
                }
            }

            //Add fake products in option
            if ($my_custom_option != null) {
                $my_custom_option->setData('values', $my_custom_option_values)->save();
            }

            //store product id in quote
            $quote->setproduct_id($product->getId())->save();
        }
    }

    /**
     * delete bundle
     */
    public function deleteBundle($quote) {
        $productId = $quote->getproduct_id();
        if ($productId) {

            //delete bundle
            Mage::getModel('catalog/product')->setId($productId)->delete();
            $quote->setproduct_id('')->save();
        }
    }

    /**
     * Return true if quote need a bundle product
     */
    public function needBundleProduct($quote) {
        $bundleProductRequired = false;
        foreach ($quote->getItems() as $item) {
            if ($item->getexclude() == 0)
                $bundleProductRequired = true;
        }
        return $bundleProductRequired;
    }

}