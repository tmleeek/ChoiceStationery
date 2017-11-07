<?php
/**
 * Productexport.php
 * CommerceThemes @ InterSEC Solutions LLC.
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the EULA
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://www.commercethemes.com/LICENSE-M1.txt
 *
 * @category   Product
 * @package    Productexport
 * @copyright  Copyright (c) 2003-2009 CommerceThemes @ InterSEC Solutions LLC. (http://www.commercethemes.com)
 * @license    http://www.commercethemes.com/LICENSE-M1.txt
 */ 


class CommerceExtensions_Productimportexport_Model_Convert_Parser_Productexportskupriceonly
    extends Mage_Eav_Model_Convert_Parser_Abstract
{
    

    /**
     * @deprecated not used anymore
     */
    public function parse()
    {
        $data = $this->getData();

        $entityTypeId = Mage::getSingleton('eav/config')->getEntityType('catalog_product')->getId();

        $result = array();
        $inventoryFields = array();
        foreach ($data as $i=>$row) {
            $this->setPosition('Line: '.($i+1));
            try {
                // validate SKU
                if (empty($row['sku'])) {
                    $this->addException(Mage::helper('catalog')->__('Missing SKU, skipping the record'), Mage_Dataflow_Model_Convert_Exception::ERROR);
                    continue;
                }
                $this->setPosition('Line: '.($i+1).', SKU: '.$row['sku']);

                // try to get entity_id by sku if not set
                if (empty($row['entity_id'])) {
                    $row['entity_id'] = $this->getResource()->getProductIdBySku($row['sku']);
                }

                // if attribute_set not set use default
                if (empty($row['attribute_set'])) {
                    $row['attribute_set'] = 'Default';
                }
                // get attribute_set_id, if not throw error
                $row['attribute_set_id'] = $this->getAttributeSetId($entityTypeId, $row['attribute_set']);
                if (!$row['attribute_set_id']) {
                    $this->addException(Mage::helper('catalog')->__("Invalid attribute set specified, skipping the record"), Mage_Dataflow_Model_Convert_Exception::ERROR);
                    continue;
                }

                if (empty($row['type'])) {
                    $row['type'] = 'Simple';
                }
                // get product type_id, if not throw error
                $row['type_id'] = $this->getProductTypeId($row['type']);
                if (!$row['type_id']) {
                    $this->addException(Mage::helper('catalog')->__("Invalid product type specified, skipping the record"), Mage_Dataflow_Model_Convert_Exception::ERROR);
                    continue;
                }

                // get store ids
                $storeIds = $this->getStoreIds(isset($row['store']) ? $row['store'] : $this->getVar('store'));
                if (!$storeIds) {
                    $this->addException(Mage::helper('catalog')->__("Invalid store specified, skipping the record"), Mage_Dataflow_Model_Convert_Exception::ERROR);
                    continue;
                }

                // import data
                $rowError = false;
                foreach ($storeIds as $storeId) {
                    $collection = $this->getCollection($storeId);
                    $entity = $collection->getEntity();

                    $model = Mage::getModel('catalog/product');
                    $model->setStoreId($storeId);
                    if (!empty($row['entity_id'])) {
                        $model->load($row['entity_id']);
                    }
                    foreach ($row as $field=>$value) {
                        $attribute = $entity->getAttribute($field);

                        if (!$attribute) {
                            //$inventoryFields[$row['sku']][$field] = $value;

                            if (in_array($field, $this->_inventoryFields)) {
                                $inventoryFields[$row['sku']][$field] = $value;
                            }
                            continue;
                            #$this->addException(Mage::helper('catalog')->__("Unknown attribute: %s", $field), Mage_Dataflow_Model_Convert_Exception::ERROR);
                        }
                        if ($attribute->usesSource()) {
                            $source = $attribute->getSource();
                            $optionId = $this->getSourceOptionId($source, $value);
                            if (is_null($optionId)) {
                                $rowError = true;
                                $this->addException(Mage::helper('catalog')->__("Invalid attribute option specified for attribute %s (%s), skipping the record", $field, $value), Mage_Dataflow_Model_Convert_Exception::ERROR);
                                continue;
                            }
                            $value = $optionId;
                        }
                        $model->setData($field, $value);

                    }//foreach ($row as $field=>$value)

                    //echo 'Before **********************<br/><pre>';
                    //print_r($model->getData());
                    if (!$rowError) {
                        $collection->addItem($model);
                    }
                    unset($model);
                } //foreach ($storeIds as $storeId)
            } catch (Exception $e) {
                if (!$e instanceof Mage_Dataflow_Model_Convert_Exception) {
                    $this->addException(Mage::helper('catalog')->__("Error during retrieval of option value: %s", $e->getMessage()), Mage_Dataflow_Model_Convert_Exception::FATAL);
                }
            }
        }

        // set importinted to adaptor
        if (sizeof($inventoryFields) > 0) {
            Mage::register('current_imported_inventory', $inventoryFields);
            //$this->setInventoryItems($inventoryFields);
        } // end setting imported to adaptor

        $this->setData($this->_collections);
        return $this;
    }

    public function setInventoryItems($items)
    {
        $this->_inventoryItems = $items;
    }

    public function getInventoryItems()
    {
        return $this->_inventoryItems;
    }

    /**
     * Unparse (prepare data) loaded products
     *
     * @return Mage_Catalog_Model_Convert_Parser_Product
     */
    public function unparse()
    {
      		 $storeID = $this->getVar('store');
			 $export_multi_store = $this->getVar('export_multi_store');
			 $recordlimitstart = $this->getVar('recordlimitstart');
			 $recordlimitend = $this->getVar('recordlimitend') - $this->getVar('recordlimitstart');
			 
			 $row = array();
			 $resource = Mage::getSingleton('core/resource');
			 $prefix = Mage::getConfig()->getNode('global/resources/db/table_prefix');
			 $read = $resource->getConnection('core_read');
			 $entity_type_id = Mage::getModel('eav/entity')->setType('catalog_product')->getTypeId();
			 
			 $select_qry = "SELECT DISTINCT P.entity_id AS ProductID, P.type_id, P.sku, C.value AS Cost, D.store_id, D.value AS Price, S.value AS Special_Price, SDF.value As Special_Date_From, SDT.value As Special_Date_To, X.value AS Msrp
    FROM ".$prefix."catalog_product_entity AS P INNER JOIN
    ".$prefix."catalog_product_entity_varchar AS V ON P.entity_id = V.entity_id  AND V.attribute_id = ( SELECT attribute_id FROM ".$prefix."eav_attribute AS eav WHERE eav.attribute_code = 'name' and eav.entity_type_id ='".$entity_type_id."') LEFT JOIN
    ".$prefix."catalog_product_entity_decimal AS D ON P.entity_id = D.entity_id  AND D.attribute_id = ( SELECT attribute_id FROM ".$prefix."eav_attribute AS eav WHERE eav.attribute_code = 'price' and eav.entity_type_id ='".$entity_type_id."') LEFT JOIN
    ".$prefix."catalog_product_entity_datetime AS SDF ON P.entity_id = SDF.entity_id  AND SDF.attribute_id  = ( SELECT attribute_id FROM ".$prefix."eav_attribute AS eav WHERE eav.attribute_code = 'special_from_date' and eav.entity_type_id ='".$entity_type_id."') LEFT JOIN
    ".$prefix."catalog_product_entity_datetime AS SDT ON P.entity_id = SDT.entity_id  AND SDT.attribute_id  = ( SELECT attribute_id FROM ".$prefix."eav_attribute AS eav WHERE eav.attribute_code = 'special_to_date' and eav.entity_type_id ='".$entity_type_id."') LEFT JOIN
    ".$prefix."catalog_product_entity_decimal AS X ON P.entity_id = X.entity_id  AND X.attribute_id  = ( SELECT attribute_id FROM ".$prefix."eav_attribute AS eav WHERE eav.attribute_code = 'msrp' and eav.entity_type_id ='".$entity_type_id."') LEFT JOIN
    ".$prefix."catalog_product_entity_decimal AS S ON P.entity_id = S.entity_id  AND S.attribute_id = ( SELECT attribute_id FROM ".$prefix."eav_attribute AS eav WHERE eav.attribute_code = 'special_price' and eav.entity_type_id ='".$entity_type_id."') LEFT JOIN
    ".$prefix."catalog_product_entity_decimal AS C ON P.entity_id = C.entity_id  AND C.attribute_id = ( SELECT attribute_id FROM ".$prefix."eav_attribute AS eav WHERE eav.attribute_code = 'cost' and eav.entity_type_id ='".$entity_type_id."') LIMIT ".$recordlimitstart.",".$recordlimitend;
	
			$rows = $read->fetchAll($select_qry);
			foreach($rows as $data)
			 { 
					 if($export_multi_store == "true") { $row["store_id"] = $data['store_id']; }
					 $row["sku"] = $data['sku'];
					 $row["price"] = $data['Price'];	
					 $row["special_price"] = $data['Special_Price'];
					 $row["special_from_date"] = $data['Special_Date_From'];
					 $row["special_to_date"] = $data['Special_Date_To'];
					 #$row["msrp"] = $data['Msrp'];
					 $row["cost"] = $data['Cost'];
					 
					 $batchExport = $this->getBatchExportModel()
										 ->setId(null)
										 ->setBatchId($this->getBatchModel()->getId())
										 ->setBatchData($row)
										 ->setStatus(1)
										 ->save();
			 }
					
        return $this;
    }

    
}