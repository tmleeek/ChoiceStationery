<?php

/**
 * @author Amasty Team
 * @copyright Copyright (c) 2018 Amasty (https://www.amasty.com)
 * @package Magpleasure_Ajaxreviews
 */
class Magpleasure_Ajaxreviews_Block_Rating_Entity_Detailed extends Mage_Rating_Block_Entity_Detailed
{
    /**
     * Render block HTML
     *
     * @return string
     */
    public function _toHtml()
    {
        $entityId = Mage::app()->getRequest()->getParam('id');
        $ratingCollection = Mage::getModel('rating/rating')
            ->getResourceCollection()
            ->addEntityFilter('product')
            ->setPositionOrder()
            ->setStoreFilter(Mage::app()->getStore()->getId())
            ->addRatingPerStoreName(Mage::app()->getStore()->getId())
            ->load();

        if ($entityId) {
            $ratingCollection->addEntitySummaryToItem($entityId, Mage::app()->getStore()->getId());
        }

        $this->assign('collection', $ratingCollection);
        if (Mage::helper('ajaxreviews')->replaceStandardRate()) {
            $this->setTemplate('ajaxreviews/rating/detailed.phtml');
        }
        return parent::_toHtml();
    }
}