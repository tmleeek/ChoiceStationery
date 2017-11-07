<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2017 Amasty (https://www.amasty.com)
 * @package Amasty_Brands
 */


/**
 * Class Brand
 *
 * @author Artem Brunevski
 */
class Amasty_Brands_Model_Import_Entity_Brand
    extends Mage_ImportExport_Model_Import_Entity_Abstract
    implements Amasty_Brands_Model_Abstract_ImportExportInterface
{
    /**
     * Default Scope
     */
    const SCOPE_DEFAULT = 1;

    /**
     * Store Scope
     */
    const SCOPE_STORE   = 0;

    /**
     * Null Scope
     */
    const SCOPE_NULL    = -1;

    /**
     * Website scope
     */
    const SCOPE_WEBSITE = 2;

    /**
     * All stores code-ID pairs.
     *
     * @var array
     */
    protected $_storeCodeToId = array();

    /** @var Mage_Eav_Model_Entity_Attribute */
    protected $_brandAttribute;

    /** @var  array */
    protected $_brandAttributeOptions;

    /** @var array  */
    protected $_oldBrand = array();

    /** @var array  */
    protected $_newBrand = array();

    /** @var array  */
    protected $_attributes = array();

    /** @var array  */
    protected $_attributesOptions = array();

    /**
     * Errors
     */
    const ERROR_DUPLICATE_OPTION                = 'duplicateOption';

    const ERROR_OPTION_NOT_FOUND_FOR_DELETE     = 'optionNotFoundToDelete';

    const ERROR_INVALID_STORE                   = 'invalidStore';

    const ERROR_OPTION_IS_EMPTY                 = 'optionIsEmpty';

    /**
     * Validation failure message template definitions
     *
     * @var array
     */
    protected $_messageTemplates = array(
        self::ERROR_DUPLICATE_OPTION => 'Duplicate brand option',
        self::ERROR_OPTION_NOT_FOUND_FOR_DELETE => 'Brand with specified option not found',
        self::ERROR_INVALID_STORE  => 'Invalid value in Store column (store does not exists?)',
        self::ERROR_OPTION_IS_EMPTY => 'Brand option is empty'
    );

    public function __construct()
    {
        parent::__construct();

        $this
            ->_initAttributes()
            ->_initBrandAttribute()
            ->_initStores()
            ->_initBrands()
        ;
    }

    /**
     * Column names that holds values with particular meaning.
     *
     * @var array
     */
    protected $_particularAttributes = array(
        self::COL_BRAND_OPTION_NAME,
        self::COL_STORE
    );

    /**
     * @return $this
     */
    protected function _initBrands()
    {
        /** @var Amasty_Brands_Model_Brand $brand */
        foreach(Mage::getModel('ambrands/brand')->getCollection() as $brand)
        {
            $this->_oldBrand[$brand->getOptionId()] = array(
                'entity_id' => $brand->getId()
            );
        }

        return $this;
    }

    /**
     * @return $this
     */
    protected function _initAttributes()
    {
        /** @var Amasty_Brands_Model_Resource_Eav_Attribute_Collection $collection */
        $collection = Mage::getResourceModel('ambrands/eav_attribute_collection')
            ->addFieldToSelect('*');

        /** @var Amasty_Brands_Model_Resource_Eav_Attribute $attribute */
        foreach ($collection as $attribute) {

            $attributeCode = $attribute->getAttributeCode();
            $this->_attributes[$attributeCode] = $attribute;
            $this->_attributesOptions[$attributeCode] = $this->getAttributeOptions($attribute);
        }

        return $this;
    }

    /**
     * Initialize stores hash.
     *
     * @return Mage_ImportExport_Model_Import_Entity_Product
     */
    protected function _initStores()
    {
        foreach (Mage::app()->getStores() as $store) {
            $this->_storeCodeToId[$store->getCode()] = $store->getId();
        }
        return $this;
    }

    protected function _initBrandAttribute()
    {
        /** @var Amasty_Brands_Model_Config $config */
        $config = Mage::getSingleton('ambrands/config');
        $this->_brandAttribute = $config->getBrandAttribute();
        foreach($config->getBrandAttributeOptions(false) as $option) {
            $this->_brandAttributeOptions[$option['value']] = $option['label'];
        }
        return $this;
    }

    /**
     * Delete products.
     *
     * @return Mage_ImportExport_Model_Import_Entity_Product
     */
    protected function _deleteBrands()
    {
        $entityTable = Mage::getResourceSingleton('ambrands/brand')->getEntityTable();

        if (count($this->_oldBrand) > 0) {
            while ($bunch = $this->_dataSourceModel->getNextBunch()) {
                $idToDelete = array();

                foreach ($bunch as $rowNum => $rowData) {
                    if ($this->validateRow($rowData, $rowNum) && self::SCOPE_DEFAULT == $this->getRowScope($rowData)) {
                        $idToDelete[] = $this->_oldBrand[$rowData[self::COL_BRAND_OPTION]]['entity_id'];
                    }
                }
                if ($idToDelete) {
                    $this->_connection->query(
                        $this->_connection->quoteInto(
                            "DELETE FROM `{$entityTable}` WHERE `entity_id` IN (?)", $idToDelete
                        )
                    );
                }
            }
        }
        return $this;
    }

    /**
     * Import data rows.
     *
     * @return boolean
     */
    protected function _importData()
    {
        if (Mage_ImportExport_Model_Import::BEHAVIOR_DELETE == $this->getBehavior()) {
            $this->_deleteBrands();
        } else {
            $this->_saveBrands();
        }

        return true;
    }

    protected function _saveBrands()
    {
        $brandLimit   = null;
        $brandQty    = null;

        while ($bunch = $this->_dataSourceModel->getNextBunch()) {
            $entityRowsIn = array();
            $entityRowsUp = array();
            $attributes   = array();

            foreach ($bunch as $rowNum => $rowData) {
                $this->_filterRowData($rowData);

                if (!$this->validateRow($rowData, $rowNum)) {
                    continue;
                }

                $rowScope = $this->getRowScope($rowData);

                if (self::SCOPE_DEFAULT == $rowScope) {
                    $rowOptionId = $rowData[self::COL_BRAND_OPTION];
                    $urlKey = $rowData[self::COL_BRAND_URL_KEY];
                    // 1. Entity phase
                    if (isset($this->_oldBrand[$rowOptionId])) { // existing row
                        $entityRowsUp[] = array(
                            'entity_type_id'   => $this->_entityTypeId,
                            'option_id'  => $rowOptionId,
                            'updated_at' => now(),
                            'created_at' => now(),
                            'entity_id'  => $this->_oldBrand[$rowOptionId]['entity_id'],
                            'url_key'    => $urlKey,
                        );
                    } else { // new row
                        $entityRowsIn[$rowOptionId] = array(
                            'entity_type_id'   => $this->_entityTypeId,
                            'option_id'        => $rowOptionId,
                            'url_key'          => $urlKey,
                            'created_at'       => now(),
                            'updated_at'       => now()
                        );
                    }
                }

                $rowStore = self::SCOPE_STORE == $rowScope ?
                    $this->_storeCodeToId[$rowData[self::COL_STORE]]
                    : 0;

                try {
                    $attributes = $this->_prepareAttributes(
                        $rowData,
                        $rowScope,
                        $attributes,
                        $rowOptionId,
                        $rowStore
                    );
                } catch (Exception $e) {
                    Mage::logException($e);
                    continue;
                }
            }

            $this->_saveBrandEntity($entityRowsIn, $entityRowsUp)
                ->_saveBrandAttributes($attributes);
        }
    }

    /**
     * @param array $entityRowsIn
     * @param array $entityRowsUp
     * @return $this
     */
    protected function _saveBrandEntity(array $entityRowsIn, array $entityRowsUp)
    {
        $entityTable = Mage::getResourceSingleton('ambrands/brand')->getEntityTable();

        if (Mage_ImportExport_Model_Import::BEHAVIOR_APPEND !== $this->getBehavior() &&
            count($this->_oldBrand) > 0
        ) {
            $this->_connection->delete(
                $entityTable,
                $this->_connection->quoteInto('`option_id` IN (?)', array_keys($this->_oldBrand))
            );
        }

        if ($entityRowsUp) {
            $this->_connection->insertOnDuplicate(
                $entityTable,
                $entityRowsUp,
                array('url_key', 'updated_at', 'created_at', 'option_id', 'entity_type_id')
            );
        }
        if ($entityRowsIn) {
            $this->_connection->insertMultiple($entityTable, $entityRowsIn);

            $newBrands = $this->_connection->fetchPairs($this->_connection->select()
                ->from($entityTable, array('option_id', 'entity_id', 'url_key'))
                ->where('option_id IN (?)', array_keys($entityRowsIn))
            );
            foreach ($newBrands as $optionId => $newId) { // fill up entity_id for new products
                $this->_newBrand[$optionId]['entity_id'] = $newId;
            }
        }
        return $this;
    }

    /**
     * @param array $attributesData
     * @return $this
     */
    protected function _saveBrandAttributes(array $attributesData)
    {
        $entityTable = Mage::getResourceSingleton('ambrands/brand')->getEntityTable();

        foreach ($attributesData as $tableName => $brandData) {
            $tableData = array();

            foreach ($brandData as $optionId => $attributes) {
                $brandId = $this->_newBrand[$optionId]['entity_id'];

                foreach ($attributes as $attributeId => $storeValues) {
                    foreach ($storeValues as $storeId => $storeValue) {
                        $tableData[] = array(
                            'entity_id'      => $brandId,
                            'entity_type_id' => $this->_entityTypeId,
                            'attribute_id'   => $attributeId,
                            'store_id'       => $storeId,
                            'value'          => $storeValue
                        );
                    }

                    /*
                    If the store based values are not provided for a particular store,
                    we default to the default scope values.
                    In this case, remove all the existing store based values stored in the table.
                    */
                    $where =
                        $this->_connection->quoteInto(' entity_id = ?', $brandId);

                    if ($entityTable !== $tableName){
                        $where .= $this->_connection->quoteInto(' AND store_id NOT IN (?)', array_keys($storeValues)) .
                            $this->_connection->quoteInto(' AND attribute_id = ?', $attributeId) .
                            $this->_connection->quoteInto(' AND entity_type_id = ?', $this->_entityTypeId);
                    }

                    $this->_connection->delete(
                        $tableName, $where
                    );
                }
            }

            $this->_connection->insertOnDuplicate($tableName, $tableData, array('value'));
        }
        return $this;
    }

    /**
     * Prepare attributes data
     *
     * @param array $rowData
     * @param int $rowScope
     * @param array $attributes
     * @param string|null $rowOptionId
     * @param int $rowStore
     * @return array
     */
    protected function _prepareAttributes($rowData, $rowScope, $attributes, $rowOptionId, $rowStore)
    {
        /** @var Amasty_Brands_Model_Brand $brand */
        $brand = Mage::getModel('ambrands/brand')
            ->setData($rowData);

        foreach ($rowData as $attrCode => $attrValue) {
            $attrValue = $attrValue === null ? '' : $attrValue;
            /** @var Amasty_Brands_Model_Resource_Eav_Attribute $attribute */
            if (array_key_exists($attrCode, $this->_attributes) &&
                ($attribute = $this->_attributes[$attrCode]) &&
                $attribute->getBackendType() != 'static'
            ) {

                $attribute = $this->_attributes[$attrCode];

                if ('multiselect' != Mage_ImportExport_Model_Import::getAttributeType($attribute)
                    && self::SCOPE_NULL == $rowScope
                ) {
                    continue; // skip attribute processing for SCOPE_NULL rows
                }
                $attrId = $attribute->getId();
                $attrTable = $attribute->getBackend()->getTable();
                $storeIds = array(0);

                if (array_key_exists($attrCode, $this->_attributesOptions) &&
                    count($this->_attributesOptions[$attrCode]) > 0 &&
                    array_key_exists(strtolower($attrValue), $this->_attributesOptions[$attrCode])
                ){
                    $attrValue = $this->_attributesOptions[$attrCode][strtolower($attrValue)];
                }

                if ('datetime' == $attribute->getBackendType() && strtotime($attrValue)) {
                    $attrValue = gmstrftime($this->_getStrftimeFormat(), strtotime($attrValue));
                }

                if (self::SCOPE_STORE == $rowScope) {
                    if (self::SCOPE_WEBSITE == $attribute->getIsGlobal()) {
                        // check website defaults already set
                        if (!isset($attributes[$attrTable][$rowOptionId][$attrId][$rowStore])) {
                            $storeIds = $this->_storeIdToWebsiteStoreIds[$rowStore];
                        }
                    } elseif (self::SCOPE_STORE == $attribute->getIsGlobal()) {
                        $storeIds = array($rowStore);
                    }
                }
                foreach ($storeIds as $storeId) {
                    if ('multiselect' == Mage_ImportExport_Model_Import::getAttributeType($attribute)) {
                        if (!isset($attributes[$attrTable][$rowOptionId][$attrId][$storeId])) {
                            $attributes[$attrTable][$rowOptionId][$attrId][$storeId] = '';
                        } else {
                            $attributes[$attrTable][$rowOptionId][$attrId][$storeId] .= ',';
                        }
                        $attributes[$attrTable][$rowOptionId][$attrId][$storeId] .= $attrValue;
                    } else {
                        $attributes[$attrTable][$rowOptionId][$attrId][$storeId] = $attrValue;
                    }
                }
            }
        }

        return $attributes;
    }

    /**
     * EAV entity type code getter.
     *
     * @return string
     */
    public function getEntityTypeCode()
    {
        return Amasty_Brands_Model_Brand::ENTITY;
    }

    /**
     * @param array $rowData
     */
    protected function _filterRowData(array &$rowData)
    {
        if (!array_key_exists(self::COL_BRAND_OPTION, $rowData) &&
            array_key_exists(self::COL_BRAND_OPTION, $rowData) &&
            in_array($rowData[self::COL_BRAND_OPTION_NAME], $this->_brandAttributeOptions)
        ){
            $rowData[self::COL_BRAND_OPTION] = array_search(
                $rowData[self::COL_BRAND_OPTION_NAME],
                $this->_brandAttributeOptions
            );
        }
    }

    /**
     * Obtain scope of the row from row data.
     *
     * @param array $rowData
     * @return int
     */
    public function getRowScope(array $rowData)
    {
        if (isset($rowData[self::COL_BRAND_OPTION]) && strlen(trim($rowData[self::COL_BRAND_OPTION]))) {
            return self::SCOPE_DEFAULT;
        } elseif (empty($rowData[self::COL_STORE])) {
            return self::SCOPE_NULL;
        } else {
            return self::SCOPE_STORE;
        }
    }

    /**
     * Validate data row.
     *
     * @param array $rowData
     * @param int $rowNum
     * @return boolean
     */
    public function validateRow(array $rowData, $rowNum)
    {
        if (isset($this->_validatedRows[$rowNum])) { // check that row is already validated
            return !isset($this->_invalidRows[$rowNum]);
        }
        $this->_validatedRows[$rowNum] = true;

        if (isset($this->_newBrand[$rowData[self::COL_BRAND_OPTION]])) {
            $this->addRowError(self::ERROR_DUPLICATE_OPTION, $rowNum);
            return false;
        }

        $rowScope = $this->getRowScope($rowData);

        if (Mage_ImportExport_Model_Import::BEHAVIOR_DELETE == $this->getBehavior()) {
            if (self::SCOPE_DEFAULT == $rowScope && !isset($this->_oldBrand[$rowData[self::COL_BRAND_OPTION]])) {
                $this->addRowError(self::ERROR_OPTION_NOT_FOUND_FOR_DELETE, $rowNum);
                return false;
            }
            return true;
        }

        if (self::SCOPE_DEFAULT == $rowScope) {
            $this->_processedEntitiesCount++;

            $optionId = $rowData[self::COL_BRAND_OPTION];

            if ($optionId && isset($this->_oldBrand[$optionId])) {
                $this->_newBrand[$optionId] = array(
                    'entity_id'     => $this->_oldBrand[$optionId]['entity_id']
                );
            }
        } else {
            if (
                (self::SCOPE_DEFAULT == $rowScope) &&
                (!array_key_exists(self::COL_BRAND_OPTION, $rowData) || empty($rowData[self::COL_BRAND_OPTION]))
            ){
                $this->addRowError(self::ERROR_OPTION_IS_EMPTY, $rowNum);
            } else if (self::SCOPE_STORE == $rowScope && !isset($this->_storeCodeToId[$rowData[self::COL_STORE]])) {
                $this->addRowError(self::ERROR_INVALID_STORE, $rowNum);
            }
        }

        return !isset($this->_invalidRows[$rowNum]);
    }
}