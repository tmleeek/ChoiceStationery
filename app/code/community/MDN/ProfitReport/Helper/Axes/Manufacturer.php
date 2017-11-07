<?php

/**
 * 
 *
 */
class MDN_ProfitReport_Helper_Axes_Manufacturer extends MDN_ProfitReport_Helper_Axes_Abstract {

    /**
     * Return axes (manufacturers)
     */
    public function getAxes($dateStart, $dateEnd) {
        $retour = array();

        //if (mage::helper('ProfitReport/FlatOrder')->ordersUseEavModel())
            $manufacturerTable = 'catalog_product_entity_int';
        //else
        //    $manufacturerTable = 'catalog_product_entity_varchar';

        //get used manufacturers for the period
        $query = $this->getBaseQuery($dateStart, $dateEnd);
        $manufacturerAttributeId = mage::getModel('eav/entity_attribute')->loadByCode('catalog_product', 'manufacturer')->getId();
        $query->addSelect('distinct tbl_manufacturer.value');
        $query->addFrom(Mage::getConfig()->getTablePrefix() . $manufacturerTable . " tbl_manufacturer");
        $query->addWhere("tbl_manufacturer.entity_id = tbl_product.entity_id");
        $query->addWhere("tbl_manufacturer.attribute_id = " . $manufacturerAttributeId);
        $usedManufacturers = $query->getCol();


        $model = mage::getModel('catalog/product');
        $attribute = Mage::getResourceModel('eav/entity_attribute_collection')
                        ->setEntityTypeFilter($model->getResource()->getTypeId())
                        ->addFieldToFilter('attribute_code', 'manufacturer')
                        ->getFirstItem()
                        ->setEntity($model->getResource());

        $manufacturers = $attribute->getSource()->getAllOptions(false);
        foreach ($manufacturers as $manufacturer) {
            $manufacturerId = $manufacturer['value'];
            if (in_array($manufacturerId, $usedManufacturers)) {
                $item = array();
                $item['name'] = $manufacturer['label'];
                $item['id'] = $manufacturerId;
                $retour[] = $item;
            }
        }

        return $retour;
    }

    /**
     * Return sub axes
     */
    public function getSubAxes($attributesetId, $dateStart, $dateEnd) {
        //get base query
        $query = $this->getBaseQuery($dateStart, $dateEnd);

        //add products name select
        $query->addSelect('distinct tbl_product.entity_id id');
        $query->addSelect('tbl_product_name.value as name');

        //add products name table jointure
        $query->addFrom(Mage::getConfig()->getTablePrefix() . "catalog_product_entity_varchar tbl_product_name");

        //add products name jointure condition
        $query->addWhere("tbl_product.entity_id = tbl_product_name.entity_id");
        $query->addWhere("tbl_product_name.attribute_id = " . $this->getProductNameAttributeId());
        $query->addWhere("tbl_product_name.store_id = 0");

        //add attributeset filter
        $this->addAxeFilter($query, $attributesetId);

        //exclude already parsed products
        $query->addWhere('item_id not in (' . implode(',', $this->_parsedOrderItemIds) . ')');

        //return results
        $results = $query->getResults();
        return $results;
    }

    /**
     * Define axe filter
     */
    public function addAxeFilter(&$query, $axeId) {
        //add attribute set filter
        if (($axeId != 'null') && ($axeId != null)) {
            $manufacturerAttributeId = mage::getModel('eav/entity_attribute')->loadByCode('catalog_product', 'manufacturer')->getId();

            //if (mage::helper('ProfitReport/FlatOrder')->ordersUseEavModel())
                $manufacturerTable = 'catalog_product_entity_int';
            //else
            //    $manufacturerTable = 'catalog_product_entity_varchar';

            $query->addFrom(Mage::getConfig()->getTablePrefix() . $manufacturerTable . " tbl_manufacturer");
            $query->addWhere("tbl_manufacturer.entity_id = tbl_product.entity_id");
            $query->addWhere("tbl_manufacturer.attribute_id = " . $manufacturerAttributeId);
            $query->addWhere("tbl_manufacturer.value = " . $axeId);
        }
    }

}