<?php

/**
 * price filter - override price breaks for customer price grouping 
 *
 * @category    Mage
 * @package   	Mage_Catalog
 * @author      Sergey Stepanchuk
 */
class Bintime_Sinchimport_Model_Layer_Filter_Price extends Mage_Catalog_Model_Layer_Filter_Price
{
    /**
     * Get price range for building filter steps
     *
     * @return int
     *//*
	public function getPriceRange()
    {
        
        $range = $this->getData('price_range');
        if (is_null($range)) {
            $maxPrice = $this->getMaxPriceInt();
        $index = 2;
            if ($maxPrice<1000) {
        $index = 1;
        }
            do {
                $range = pow(10, (strlen(floor($maxPrice))-$index));
                $items = $this->getRangeItemCounts($range);
                $index++;
            }
            while($range>self::MIN_RANGE_POWER && count($items)<2);

            $this->setData('price_range', $range);
        }
        return $range;
    }
    
*/
    /**
     * Apply price range filter to collection
     *
     * @param Zend_Controller_Request_Abstract $request
     * @param $filterBlock
     *
     * @return Mage_Catalog_Model_Layer_Filter_Price
     */
    public function apply(Zend_Controller_Request_Abstract $request, $filterBlock)
    {
        /**
         * Filter must be string: $index,$range
         */
        $filter = $request->getParam($this->getRequestVar());
        if (!$filter) {
            return $this;
        }
/*        echo "<pre>";
        var_dump($filter);
        echo "</pre>";
*/        
        if(strstr($filter, ',')){
            $filter = explode(',', $filter);
            if (count($filter) != 2) {
                return $this;
            }
            list($index, $range) = $filter;
  //          echo $index.$range."AAAAAAAa";
            if ((int)$index && (int)$range) {
                $this->setPriceRange((int)$range);

                $this->_applyToCollection($range, $index);
                $this->getLayer()->getState()->addFilter(
                        $this->_createItem($this->_renderItemLabel($range, $index), $filter)
                        );

                $this->_items = array();
            }

        }elseif(strstr($filter, '-')){
            $filter = explode('-', $filter);
            if (count($filter) != 2) {
                return $this;
            }

            list($minPrice, $maxPrice) = $filter;
//            echo $minPrice."BBBBBB".$maxPrice;
            
//            if ((int)$minPrice && (int)$maxPrice) {
              if(((int)$minPrice || $minPrice==0) && ((int)$maxPrice || $maxPrice=='*')){    
                //                $this->setPriceRange((int)$range);

                $this->_applyToCollectionMinMaxPrice($minPrice, $maxPrice);
                            $this->getLayer()->getState()->addFilter(
                                $this->_createItem($this->_renderItemLabelMinMaxPrice($minPrice, $maxPrice), $filter)
                                );

                $this->_items = array();
            }

        }

        return $this;
    }
    /**
     * Prepare text of item label
     *
     * @param   int $fromPrice 
     * @param   int 
     * @return  string
     */

    protected function _renderItemLabelMinMaxPrice($fromPrice, $toPrice)
    {
        $store      = Mage::app()->getStore();
        $toPriceLabel=$toPrice;
        $fromPrice  = $store->formatPrice($fromPrice);
        $toPrice    = $store->formatPrice($toPrice);
        $label= Mage::helper('catalog')->__('%s - %s', $fromPrice, $toPrice);
        if( $toPriceLabel=='' || $toPriceLabel=='*'){
            $label=$fromPrice." + ";
        }

        return $label;
    }

    /**
     * Get data for build price filter items
     *
     * @return array
     */
    protected function _getItemsData()
    {
//        $dataConf = Mage::getStoreConfig('sinchimport_root/sinch_ftp');
//        $price_breaks=$dataConf['price_breaks'];
        $import=Mage::getModel('sinchimport/sinch');
        $price_breaks=$import->price_breaks_filter;

        if(strstr($price_breaks, ';')){
            $price_ranges = explode(';', $price_breaks);

            foreach ($price_ranges as $price_range) {
                $price_range_value=trim($price_range);
                if($price_range && $price_range!=''){

                    $price_range = explode('-', $price_range);
                    list($minPrice, $maxPrice) = $price_range;
                    if(((int)$minPrice || $minPrice==0) && ((int)$maxPrice || $maxPrice=='*')){
                        $count=$this->_getResource()->getCountMinMaxPrice($this, $minPrice, $maxPrice);
                        if($count){
                            $data[] = array(
                                    'label' => $this->_renderItemLabelMinMaxPrice($minPrice, $maxPrice),
                                    'value' =>$price_range_value,
                                    'count' => $count,
                                    );
                        }
                    }
                }
            }
            if($data){
                return $data;
            }

        }

        $range      = $this->getPriceRange();
        $dbRanges   = $this->getRangeItemCounts($range);
        $data       = array();

        foreach ($dbRanges as $index=>$count) {
            $data[] = array(
                    'label' => $this->_renderItemLabel($range, $index),
                    'value' => $index . ',' . $range,
                    'count' => $count,
                    );
        }

        return $data;
    }

    /**
     * Apply filter value to product collection based on customer price breaks and selected value
     *
     * @param int $minPrice
     * @param int $maxPrice
     * @return Mage_Catalog_Model_Layer_Filter_Price
     */

    protected function _applyToCollectionMinMaxPrice($minPrice, $maxPrice)
    {
        $this->_getResource()->applyFilterToCollectionMinMaxPrice($this, $minPrice, $maxPrice);
        return $this;
    }

}
