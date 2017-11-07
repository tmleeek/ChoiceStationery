<?php
/**
 * Mirasvit
 *
 * This source file is subject to the Mirasvit Software License, which is available at http://mirasvit.com/license/.
 * Do not edit or add to this file if you wish to upgrade the to newer versions in the future.
 * If you wish to customize this module for your needs
 * Please refer to http://www.magentocommerce.com for more information.
 *
 * @category  Mirasvit
 * @package   Sphinx Search Ultimate
 * @version   2.3.1
 * @revision  601
 * @copyright Copyright (C) 2013 Mirasvit (http://mirasvit.com/)
 */


class Mirasvit_SearchIndex_Model_Resource_Catalogsearch_Fulltext_Engine
    extends Mage_CatalogSearch_Model_Mysql4_Fulltext_Engine
{
    protected $_columns = null;

    /**
     * Add entity data to fulltext search table
     *
     * @param int $entityId
     * @param int $storeId
     * @param array $index
     * @param string $entity 'product'|'cms'
     * @return Mage_CatalogSearch_Model_Resource_Fulltext_Engine
     */
    public function saveEntityIndex($entityId, $storeId, $index, $entity = 'product')
    {
        $uid = Mage::helper('mstcore/debug')->start();

        $index['product_id'] = $entityId;
        $index['store_id']   = $storeId;
        $index['updated']    = 1;
        $this->_getWriteAdapter()->insert($this->getMainTable(), $index);

        Mage::helper('mstcore/debug')->end($uid, array(
            '$entityId' => $entityId,
            '$index'    => $index,
            '$storeId'  => $storeId));

        return $this;
    }

    /**
     * Multi add entities data to fulltext search table
     *
     * @param int $storeId
     * @param array $entityIndexes
     * @param string $entity 'product'|'cms'
     * @return Mage_CatalogSearch_Model_Resource_Fulltext_Engine
     */
    public function saveEntityIndexes($storeId, $entityIndexes, $entity = 'product')
    {
        $uid = Mage::helper('mstcore/debug')->start();

        $adapter = $this->_getWriteAdapter();
        $data    = array();
        $storeId = (int)$storeId;

        $this->_addCategoryData($entityIndexes, $storeId);
        $this->_addWeights($entityIndexes, $storeId);
        $this->_addTagData($entityIndexes);
        $this->_addEntityIdData($entityIndexes);

        foreach ($entityIndexes as $entityId => $index) {
            foreach ($index as $attr => $value) {
                if (!in_array($attr, array('product_id', 'store_id', 'updated', 'fulltext_id'))) {
                    $index[$attr] = Mage::helper('searchindex')->prepareString($value);
                }
            }
            $index['data_index'] = str_replace('|', ' | ', $index['data_index']);
            $index['product_id'] = $entityId;
            $index['store_id']   = $storeId;
            $index['updated']    = 1;
            $data[]              = $index;
        }

        if ($data) {
            $adapter->insertOnDuplicate($this->getMainTable(), $data, array('data_index'));
        }

        Mage::helper('mstcore/debug')->end($uid, array('$entityIndexes' => $entityIndexes, '$storeId' => $storeId));

        return $this;
    }

    /**
     * Prepare index array as a string glued by separator
     *
     * @param array $index
     * @param string $separator
     * @return array
     */
    public function prepareEntityIndex($index, $separator = ' ')
    {
        $uid = Mage::helper('mstcore/debug')->start();

        $values = array();
        $columns = $this->getTableColumns();

        $values['data_index'] = Mage::helper('catalogsearch')->prepareIndexdata($index, $separator);
        foreach ($index as $attributeCode => $value) {
            if (is_array($value)) {
                $value = implode(' ', array_unique($value));
            }

            $values[$attributeCode] = $value;
        }

        $result = array();

        foreach ($columns as $column) {
            if (in_array($column, array('product_id', 'store_id', 'fulltext_id'))) {
                continue;
            }

            if (isset($values[$column])) {
                $result[$column] = $values[$column];
            } else {
                $result[$column] = '';
            }
        }

        Mage::helper('mstcore/debug')->end($uid, array('$index' => $index, '$result' => $result));

        return $result;
    }

    /**
     * Return array of category
     *
     * @param array $productIds in keys
     * @return Mirasvit_SearchSphinx_Model_Resource_Fulltext_Engine
     */
    protected function _addCategoryData(&$index, $storeId)
    {
        $uid = Mage::helper('mstcore/debug')->start();

        if (!$this->getIndexModel()->getIndexInstance()->getProperty('include_category')) {
            return $this;
        }

        $tableColumns = $this->getTableColumns();
        $productIds   = array_keys($index);
        $adapter      = $this->_getWriteAdapter();
        $attrName     = $this->_getCategoryAttribute('name');

        $nameSelect = $adapter->select()
            ->from(array('cc' => $this->getTable('catalog/category')),
                    array(new Zend_Db_Expr("GROUP_CONCAT(vc.value SEPARATOR ' ')")))
            ->joinLeft(
                    array('vc' => $attrName->getBackend()->getTable()),
                    'cc.entity_id = vc.entity_id',
                    array())
            ->where("LOCATE(CONCAT('/', CONCAT(cc.entity_id, '/')), CONCAT(ce.path, '/'))")
            ->where('vc.attribute_id = ?', $attrName->getId());

        $columns = array(
            'product_id' => 'product_id',
            'category'   => $nameSelect,
        );

        $select = $adapter->select()
            ->from(array($this->getTable('catalog/category_product')), $columns)
            ->joinLeft(
                    array('ce' => $this->getTable('catalog/category')),
                    'category_id = ce.entity_id',
                    array())
            ->where('product_id IN (?)', $productIds);

        $result = array();

        if (in_array('category', $tableColumns)) {
            foreach ($index as $productId => $data) {
                $index[$productId]['category'] = '';
            }
        }

        foreach ($adapter->fetchAll($select) as $row) {
            $index[$row['product_id']]['data_index'] .= '|'.$row['category'];
            if (in_array('category', $tableColumns)) {
                $index[$row['product_id']]['category'] .= ' '.$row['category'];
            }
        }

        Mage::helper('mstcore/debug')->end($uid, array('$index' => $index));

        return $this;
    }

    protected function _addTagData(&$index)
    {
        $uid = Mage::helper('mstcore/debug')->start();

        if (!$this->getIndexModel()->getIndexInstance()->getProperty('include_tag')) {
            return $this;
        }

        $productIds = array_keys($index);

        $adapter = $this->_getWriteAdapter();
        $columns = array(
            'tags' => new Zend_Db_Expr("GROUP_CONCAT(name SEPARATOR ' ')"),
        );

        $select = $adapter->select()
            ->from(array('main_table' => $this->getTable('tag/tag')), $columns)
            ->joinLeft(
                    array('tr' => $this->getTable('tag/relation')),
                    'main_table.tag_id = tr.tag_id',
                    array('product_id'))
            ->where('tr.product_id IN (?)', $productIds)
            ->group('product_id');

        $result = array();
        foreach ($adapter->fetchAll($select) as $row) {
            $index[$row['product_id']]['data_index'] .= '|'.$row['tags'];
        }

        Mage::helper('mstcore/debug')->end($uid, array('$index' => $index));

        return $this;
    }

    protected function _addWeights(&$index, $storeId)
    {
        $productIds = array_keys($index);
        $adapter    = $this->_getWriteAdapter();

        $weightAttr = $this->_getCategoryAttribute('searchindex_weight');

        if ($weightAttr) {
            $columns = array(
                'product_id' => 'product_id',
                'weight'     => new Zend_Db_Expr("SUM(value)"),
            );

            $select = $adapter->select()
                ->from(array($this->getTable('catalog/category_product_index')), $columns)
                ->joinLeft(
                        array('vc' => $weightAttr->getBackend()->getTable()),
                        'category_id = vc.entity_id',
                        array('value'))
                ->where('product_id IN (?)', $productIds)
                ->where('vc.attribute_id = ?', $weightAttr->getId())
                ->group('product_id');

            foreach ($adapter->fetchAll($select) as $row) {
                $index[$row['product_id']]['searchindex_weight'] += $row['weight'];
            }
        }

        $weightAttr = $this->_getProductAttribute('searchindex_weight');

        if ($weightAttr) {
            $columns = array(
                'entity_id' => 'entity_id',
                'weight'    => new Zend_Db_Expr("SUM(value)"),
            );

            $select = $adapter->select()
                ->from(array($weightAttr->getBackend()->getTable()), $columns)
                ->where('entity_id IN (?)', $productIds)
                ->where('attribute_id = ?', $weightAttr->getId())
                ->group('entity_id');

            foreach ($adapter->fetchAll($select) as $row) {
                $index[$row['entity_id']]['searchindex_weight'] += $row['weight'];
            }
        }

        return $this;
    }

    protected function _addEntityIdData(&$index)
    {
        if (!$this->getIndexModel()->getIndexInstance()->getProperty('include_id')) {
            return $this;
        }

        foreach ($index as $entityId => $value) {
            $index[$entityId]['data_index'] .= '|'.$entityId;
        }

        return $this;
    }

    protected function _getCategoryAttribute($attributeCode)
    {
        $entityTypeId = Mage::getSingleton('eav/config')->getEntityType('catalog_category')->getId();

        $attribute = Mage::getModel('catalog/resource_eav_attribute')
            ->loadByCode($entityTypeId, $attributeCode);
        if (!$attribute->getId()) {
            return false;
        }
        $entity = Mage::getSingleton('eav/config')
            ->getEntityType(Mage_Catalog_Model_Category::ENTITY)
            ->getEntity();
        $attribute->setEntity($entity);

        return $attribute;
    }

    protected function _getProductAttribute($attributeCode)
    {
        $entityTypeId = Mage::getSingleton('eav/config')->getEntityType('catalog_product')->getId();

        $attribute = Mage::getModel('catalog/resource_eav_attribute')
            ->loadByCode($entityTypeId, $attributeCode);
        if (!$attribute->getId()) {
            return false;
        }
        $entity = Mage::getSingleton('eav/config')
            ->getEntityType(Mage_Catalog_Model_Product::ENTITY)
            ->getEntity();
        $attribute->setEntity($entity);

        return $attribute;
    }

    protected function getTableColumns()
    {
        $uid = Mage::helper('mstcore/debug')->start();

        if ($this->_columns == null) {
            $this->_columns = array_keys($this->_getWriteAdapter()->describeTable($this->getMainTable()));
        }

        Mage::helper('mstcore/debug')->end($uid, $this->_columns);

        return $this->_columns;
    }

    protected function getIndexModel()
    {
        return Mage::helper('searchindex/index')->getIndex('mage_catalog_product');
    }
}