<?php

class Ewall_Autocrosssell_Model_Api
{
    public function getAutocrosssellProductsFor($productIds, $storeId)
    {
        if (!is_array($productIds)) {
            $productIds = array(intval($productIds));
        }

        $helper = Mage::helper('autocrosssell');
        foreach ($productIds as $productId) {
            if (!$helper->isInstalledForProduct($productId, $storeId)) {
                $helper->installForProduct($productId, $storeId);
            }
        }

        $relatedCollection = Mage::getModel('autocrosssell/autocrosssell')
            ->getCollection()
            ->addProductFilter($productIds)
            ->addStoreFilter($storeId);

        $relatedIds = array();
        foreach ($relatedCollection as $item) {
            foreach ($item->getRelatedArray() as $id => $count) {
                if (array_key_exists($id, $relatedIds)) {
                    $relatedIds[$id] += $count;
                } else {
                    $relatedIds[$id] = $count;
                }
            }
        }

        $relatedIds = array_diff_key($relatedIds, array_flip($productIds));
        arsort($relatedIds);

        return $relatedIds;
    }
   
    public function getShowForCurrentCategory($storeId = null)
    {
        return Mage::helper('autocrosssell/config')->getGeneralSameCategory($storeId);
    }  
}
