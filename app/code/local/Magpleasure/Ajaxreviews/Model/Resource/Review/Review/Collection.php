<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2018 Amasty (https://www.amasty.com)
 * @package Magpleasure_Ajaxreviews
 */

class Magpleasure_Ajaxreviews_Model_Resource_Review_Review_Collection extends Mage_Review_Model_Resource_Review_Collection
{
    /**
     * Add entity filter
     *
     * @param int|string $entity
     * @param mixed $pkValue
     * @return Magpleasure_Ajaxreviews_Model_Resource_Review_Review_Collection
     */
    public function addEntityFilter($entity, $pkValue)
    {
        if (is_numeric($entity)) {
            $this->addFilter('entity',
                $this->getConnection()->quoteInto('main_table.entity_id=?', $entity),
                'string');
        } elseif (is_string($entity)) {
            $this->_select->join($this->_reviewEntityTable,
                'main_table.entity_id='.$this->_reviewEntityTable.'.entity_id',
                array('entity_code'));

            $this->addFilter('entity',
                $this->getConnection()->quoteInto($this->_reviewEntityTable.'.entity_code=?', $entity),
                'string');
        }

        $this->addFilter('entity_pk_value',
            $this->getConnection()->quoteInto('main_table.entity_pk_value IN (?)', $pkValue),
            'string');

        return $this;
    }

    /**
     * Add filters for get all reviews connected with product
     *
     * @param mixed $pkValue
     * @return Magpleasure_Ajaxreviews_Model_Resource_Review_Review_Collection
     */
    public function getProductLinkedReviews($pkValue)
    {
        $this
            ->addStoreFilter(Mage::app()->getStore()->getId())
            ->addStatusFilter(Mage_Review_Model_Review::STATUS_APPROVED)
            ->addEntityFilter('product', $pkValue);

        return $this;
    }

}