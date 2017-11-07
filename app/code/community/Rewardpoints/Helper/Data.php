<?php
/**
 * J2T RewardsPoint2
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@j2t-design.com so we can send you a copy immediately.
 *
 * @category   Magento extension
 * @package    RewardsPoint2
 * @copyright  Copyright (c) 2011 J2T DESIGN. (http://www.j2t-design.com)
 * @license    http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class Rewardpoints_Helper_Data extends Mage_Core_Helper_Abstract {
    public function getReferalUrl()
    {
        return $this->_getUrl('rewardpoints/');
    }
    
    
    public function processRecordFlatAction ($customerId, $store_id, $check_date = false) {
        if (Mage::getModel('customer/customer')->load($customerId)){
            $reward_model = Mage::getModel('rewardpoints/stats');
	    $points_current = $reward_model->getPointsCurrent($customerId, $store_id);
            $points_received = $reward_model->getRealPointsReceivedNoExpiry($customerId, $store_id);
            $points_spent = $reward_model->getPointsSpent($customerId, $store_id);
            $points_awaiting_validation = $reward_model->getPointsWaitingValidation($customerId, $store_id);
            $points_lost = $reward_model->getRealPointsLost($customerId, $store_id);

            $reward_flat_model = Mage::getModel('rewardpoints/flatstats');
            $reward_flat_model->loadByCustomerStore($customerId, $store_id);
            $reward_flat_model->setPointsCollected($points_received);
            $reward_flat_model->setPointsUsed($points_spent);
            $reward_flat_model->setPointsWaiting($points_awaiting_validation);
            $reward_flat_model->setPointsCurrent($points_current);
            $reward_flat_model->setPointsLost($points_lost);
            $reward_flat_model->setStoreId($store_id);
            $reward_flat_model->setUserId($customerId);
            
            if ($check_date && ($date_check = $reward_flat_model->getLastCheck())){
                $date_array = explode("-", $reward_flat_model->getLastCheck());
                if ($reward_flat_model->getLastCheck() == date("Y-m-d")){
                    return false;
                }
            }
            $reward_flat_model->setLastCheck(date("Y-m-d"));
            $reward_flat_model->save();
        }
    }
    
    
    public function getResizedUrl($imgName,$x,$y=NULL){
        $imgPathFull=Mage::getBaseDir("media").DS.$imgName;
 
        $widht=$x;
        $y?$height=$y:$height=$x;
        $resizeFolder="j2t_resized";
        $imageResizedPath=Mage::getBaseDir("media").DS.$resizeFolder.DS.$imgName;
        
        if (!file_exists($imageResizedPath) && file_exists($imgPathFull)){
            $imageObj = new Varien_Image($imgPathFull);
            $imageObj->constrainOnly(true);
            $imageObj->keepAspectRatio(true);
            $imageObj->keepTransparency(true);
            $imageObj->resize($widht,$height);
            $imageObj->save($imageResizedPath);
        }
        
        //return Mage::getBaseUrl(Mage_Core_Model_Store::URL_TYPE_MEDIA).$resizeFolder.DS.$imgName;
        return Mage::getBaseUrl(Mage_Core_Model_Store::URL_TYPE_MEDIA).$resizeFolder.'/'.$imgName;
    }
    
    
    
    public function getProductPointsText($product, $noCeil = false, $from_list = false){
        $math_method = Mage::getStoreConfig('rewardpoints/default/math_method', Mage::app()->getStore()->getId());
        /*if ($math_method == 1 || $math_method == 2){
            $noCeil = true;
        }*/
        
        //$points = $this->getProductPoints($product, $noCeil, $from_list);
        $point_no_ceil = $this->getProductPoints($product, true, $from_list);
        $points = $point_no_ceil;
        /*if (!$noCeil){
            $points = ceil($point_no_ceil);
        }*/
        
        $img = '';
        if (Mage::getStoreConfig('rewardpoints/design/small_inline_image_show', Mage::app()->getStore()->getId())){
            $img = '<img src="'.$this->getResizedUrl('j2t_image_small.png', 16, 16) .'" alt="" width="16" height="16" /> ';
        }
        //J2T CEIL MODIFICATION
        $points = ceil($points);
        
        if ($points && $product->getTypeId() == Mage_Catalog_Model_Product_Type::TYPE_BUNDLE && $product->getPriceType() == Mage_Bundle_Model_Product_Price::PRICE_TYPE_FIXED){
            //$return = '<p class="j2t-loyalty-points inline-points">'.$img. Mage::helper('rewardpoints')->__("With this product, you earn <span id='j2t-pts'>%d</span> loyalty point(s).", $points) . $this->getEquivalence($points) . '</p>';
            list($points_min, $points_max) = $this->_getMinimalBundleOptionsPoint($product, true, $from_list);
            
            //echo "$points_min $points_max";
            
            $points_min = ceil($points_min+$point_no_ceil);
            $points_max = ceil($points_max+$point_no_ceil);
            
            if ($from_list && $points_min == $points_max && $points_min == 0){
                return '';
            } else if ($from_list && $points_min == $points_max){
                return '<p class="j2t-loyalty-points inline-points">'.$img. Mage::helper('rewardpoints')->__("With this product, you earn %d loyalty point(s).", $points_min) . $this->getEquivalence($points_min) .'</p>';
            } else if ($from_list){ 
                return '<p class="j2t-loyalty-points inline-points">'.$img. Mage::helper('rewardpoints')->__("With this product, you earn from %d to %d loyalty point(s).", $points_min, $points_max) . $this->getEquivalence($points_min, $points_max) .'</p>';
            } else {
                return '<p class="j2t-loyalty-points inline-points" style="display:none;">'.$img. Mage::helper('rewardpoints')->__("With this product, you earn <span id='j2t-pts'>%d</span> loyalty point(s).", $points_min) . $this->getEquivalence($points_min) . '</p>';
            }
        } else if ($points){
            /*if (Mage::getStoreConfig('rewardpoints/design/small_inline_image_show', Mage::app()->getStore()->getId())){
                $img = '<img src="'.$this->getResizedUrl('j2t_image_small.png', 16, 16) .'" alt="" width="16" height="16" /> ';
            }*/
            $return = '<p class="j2t-loyalty-points inline-points">'.$img. Mage::helper('rewardpoints')->__("With this product, you earn <span id='j2t-pts'>%d</span> loyalty point(s).", $points) . $this->getEquivalence($points) . '</p>';
            return $return;
        } else if($from_list) {
            //try to get from price
            /*if (Mage::getStoreConfig('rewardpoints/design/small_inline_image_show', Mage::app()->getStore()->getId())){
                $img = '<img src="'.$this->getResizedUrl('j2t_image_small.png', 16, 16) .'" alt="" width="16" height="16" /> ';
            }*/
            //return $product->getTypeId();
            $attribute_restriction = Mage::getStoreConfig('rewardpoints/default/process_restriction', Mage::app()->getStore()->getId());
            if ($product->getTypeId() == Mage_Catalog_Model_Product_Type::TYPE_GROUPED && !$attribute_restriction) {
                $product_default_points = $this->getDefaultProductPoints($product, Mage::app()->getStore()->getId(), $from_list, $noCeil);
                $catalog_points = Mage::getModel('rewardpoints/catalogpointrules')->getAllCatalogRulePointsGathered($product, $product_default_points);
                
                if ($catalog_points !== false){
                    $_associatedProducts = $product->getTypeInstance(true)->getAssociatedProducts($product);
                    $product_points = array();
                    foreach ($_associatedProducts as $curent_asso_product){
                        $product_points[] = $this->getProductPoints($curent_asso_product, $noCeil, $from_list);
                    }
                    if (sizeof($product_points)){
                        $points_min = ceil(min($product_points));
                        return '<p class="j2t-loyalty-points inline-points">'.$img. Mage::helper('rewardpoints')->__("With this product, you earn %d loyalty point(s).", $points_min) . $this->getEquivalence($points_min) .'</p>';
                    }
                }
            }
            else if ($product->getTypeId() == Mage_Catalog_Model_Product_Type::TYPE_BUNDLE && !$attribute_restriction){
                $product_default_points = $this->getDefaultProductPoints($product, Mage::app()->getStore()->getId(), $from_list, $noCeil);
                $catalog_points = Mage::getModel('rewardpoints/catalogpointrules')->getAllCatalogRulePointsGathered($product, $product_default_points);
                
                //Fix bundle prices
                if ($catalog_points !== false || $product->getTypeId() == Mage_Catalog_Model_Product_Type::TYPE_BUNDLE){
                    //get points
                    $_priceModel  = $product->getPriceModel();
                    //list($_minimalPriceTax, $_maximalPriceTax) = $_priceModel->getTotalPrices($product, null, null, false);
                    //list($_minimalPriceInclTax, $_maximalPriceInclTax) = $_priceModel->getTotalPrices($product, null, true, false);
                    
                    //Fix bundle prices
                    if ($product->getTypeId() == Mage_Catalog_Model_Product_Type::TYPE_BUNDLE){
                        list($points_min, $points_max) = $this->_getMinimalBundleOptionsPoint($product, $noCeil, $from_list);
                    } else {
                        if (Mage::getStoreConfig('rewardpoints/default/exclude_tax', Mage::app()->getStore()->getId())){
                            if (version_compare(Mage::getVersion(), '1.5.0', '<')){
                                list($_minimalPrice, $_maximalPrice) = $_priceModel->getPrices($product);
                            } else {
                                list($_minimalPrice, $_maximalPrice) = $_priceModel->getTotalPrices($product, null, null, false);
                            }
                        } else {
                            if (version_compare(Mage::getVersion(), '1.5.0', '<')){
                                list($_minimalPrice, $_maximalPrice) = $_priceModel->getPrices($product);
                                $_minimalPrice = Mage::helper('tax')->getPrice($product, $_minimalPrice);
                                $_maximalPrice = Mage::helper('tax')->getPrice($product, $_maximalPrice, true);
                            } else {
                                list($_minimalPrice, $_maximalPrice) = $_priceModel->getTotalPrices($product, null, true, false);
                            }
                        }
                        $points_min = $this->convertProductMoneyToPoints($_minimalPrice);
                        $points_max = $this->convertProductMoneyToPoints($_maximalPrice);
                    }
                    
                    //J2T CEIL MODIFICATION
                    $points_min = ceil($points_min);
                    $points_max = ceil($points_max);
                    if ($from_list && $points_min == $points_max && $points_min == 0){
                        return '';
                    } else if ($from_list && $points_min == $points_max){
                        return '<p class="j2t-loyalty-points inline-points">'.$img. Mage::helper('rewardpoints')->__("With this product, you earn %d loyalty point(s).", $points_min) . $this->getEquivalence($points_min) .'</p>';
                    } else if ($from_list){ 
                        return '<p class="j2t-loyalty-points inline-points">'.$img. Mage::helper('rewardpoints')->__("With this product, you earn from %d to %d loyalty point(s).", $points_min, $points_max) . $this->getEquivalence($points_min, $points_max) .'</p>';
                    } else {
                        return '<p class="j2t-loyalty-points inline-points" style="display:none;">'.$img. Mage::helper('rewardpoints')->__("With this product, you earn <span id='j2t-pts'>%d</span> loyalty point(s).", $points_min) . $this->getEquivalence($points_min) . '</p>';
                    }
                }
            } 
        }
        //J2T CEIL MODIFICATION
        //check bundle product single required options
        $points_min = 0;
        if ($product->getTypeId() == Mage_Catalog_Model_Product_Type::TYPE_BUNDLE){
            list($points_min, $points_max) = $this->_getMinimalBundleOptionsPoint($product, true, $from_list);
        }
        $points = ceil($points+$points_min);
        return '<p class="j2t-loyalty-points inline-points" style="display:none;">'.$img. Mage::helper('rewardpoints')->__("With this product, you earn <span id='j2t-pts'>%d</span> loyalty point(s).", $points) . $this->getEquivalence($points) . '</p>';
    }
    
    
    public function checkBundleMandatoryPrice($product, $type='min', $from_list = false){
        $points_min = 0;
        $points_max = 0;
        if ($product->getTypeId() == Mage_Catalog_Model_Product_Type::TYPE_BUNDLE){
            list($points_min, $points_max) = $this->_getMinimalBundleOptionsPoint($product, true, $from_list, true);
        }
        if ($type == 'min'){
            return $points_min;
        }
        return $points_max;
    }
    
    /**
     * Bundle product point min/max
     * @param object $product
     * @param boolean $noCeil
     * @param boolean $from_list
     * @param boolean $onlyUnicMandatory
     * @return int
     */
    protected function _getMinimalBundleOptionsPoint($product, $noCeil, $from_list, $onlyUnicMandatory = false)
    {
        //$options = $product->getTypeInstance()->getOptions($product);
        if (version_compare(Mage::getVersion(), '1.8.0', '<')){
            $optionCollection = $product->getTypeInstance()->getOptionsCollection();
            $selectionCollection = $product->getTypeInstance()->getSelectionsCollection($product->getTypeInstance()->getOptionsIds());
            $options = $optionCollection->appendSelections($selectionCollection);
        } else {
            $options = $product->getTypeInstance()->getOptions($product);
        }
        
        $minimalPrice = 0;
        $minimalPriceWithTax = 0;
        $hasRequiredOptions = false;
        if ($options) {
            foreach ($options as $option) {
                if ($option->getRequired()) {
                    $hasRequiredOptions = true;
                }
            }
        }
        
        
        $selectionMinimalPoints = array();
        $selectionMinimalPointsWithTax = array();

        
        if (!$options) {
            return $minimalPrice;
        }
        
        $isPriceFixedType = ($product->getPriceType() == Mage_Bundle_Model_Product_Price::PRICE_TYPE_FIXED);
        
        $min_acc = 0;
        $max_acc = 0;

        foreach ($options as $option) {
            /* @var $option Mage_Bundle_Model_Option */
            $selections = $option->getSelections();
            if ($selections){
                $current_val = 0;
                $current_vals = array();
                foreach ($selections as $selection) {
                    /* @var $selection Mage_Bundle_Model_Selection */
                    if (!$selection->isSalable()) {
                        continue;
                    }
                    //$item = $isPriceFixedType ? $product : $selection;
                    //$item = $selection;
                    $subprice = $product->getPriceModel()->getSelectionPreFinalPrice($product, $selection, 1);
                    //$subprice = $selection->getPrice();
                    
                    //echo "{$selection->getId()} : $subprice // ";
                    
                    $tierprice_incl_tax = Mage::helper('tax')->getPrice($product, $subprice, true);
                    $tierprice_excl_tax = Mage::helper('tax')->getPrice($product, $subprice);
                    
                    $current_point = $this->getProductPoints($selection, $noCeil, $from_list, false, $tierprice_incl_tax, $tierprice_excl_tax);
                    
                    //$current_point = $this->getProductPoints($item, $noCeil, $from_list);
                    
                    $current_vals[] = $current_point;
                }
                
                if ($option->getRequired() && !$onlyUnicMandatory || ($option->getRequired() && $onlyUnicMandatory && sizeof($selections) == 1)){
                    $min_acc += min($current_vals);
                }
                $max_acc += max($current_vals);
            }
        }
        
        return array($min_acc, $max_acc);
    }
    
    public function getEquivalence($points, $points_max = 0){
        $equivalence = '';
        $points = (int)$points;
        //if ($points > 0){
            if (Mage::getStoreConfig('rewardpoints/default/point_equivalence', Mage::app()->getStore()->getId())){
                $formattedPrice = Mage::helper('core')->currency($this->convertPointsToMoneyEquivalence(floor($points)), true, false);
                if ($points_max){
                    $formattedMaxPrice = Mage::helper('core')->currency($this->convertPointsToMoneyEquivalence(floor($points_max)), true, false);
                    $equivalence = ' <span class="j2t-point-equivalence">'.Mage::helper('rewardpoints')->__("%d points = %s and %d points = %s.", $points, $formattedPrice, $points_max, $formattedMaxPrice).'</span>';
                } else {
                    $equivalence = ' <span class="j2t-point-equivalence">'.Mage::helper('rewardpoints')->__("%d points = %s.", $points, $formattedPrice).'</span>';
                }
            }
        //}
        
        return $equivalence;
    }
    
    public function checkPointsInsertionCustomer($customer_id, $store_id, $type) {
        $collection = Mage::getModel("rewardpoints/stats")->getCollection();
        $collection->getSelect()->columns(array("item_qty" => "COUNT(main_table.rewardpoints_account_id)"));
        $collection->getSelect()->where("main_table.customer_id = ?", $customer_id);
        $collection->getSelect()->where("main_table.store_id = ?", $store_id);
        $collection->getSelect()->where("main_table.order_id = ?", $type);
        
        $db = Mage::getSingleton('core/resource')->getConnection('core_read');
        $result = $db->query($collection->getSelect()->__toString());
        
        if(!$result) {
            return 0;
        }
        $rows = $result->fetch(PDO::FETCH_ASSOC);

        if(!$rows) {
            return 0;
        }
        return $rows['item_qty'];
    }
    
    
    public function processMathBaseValue($amount, $specific_rate = null){
        $math_method = Mage::getStoreConfig('rewardpoints/default/math_method', Mage::app()->getStore()->getId());
        if ($math_method == 1){
            $amount = round($amount);
        } elseif ($math_method == 0) {
            $amount = floor($amount);
        }
        return $amount;
    }
    

    public function processMathValue($amount, $specific_rate = null){
        $math_method = Mage::getStoreConfig('rewardpoints/default/math_method', Mage::app()->getStore()->getId());
        if ($math_method == 1){
            $amount = round($amount);
        } elseif ($math_method == 0) {
            $amount = floor($amount);
        }
        return $this->ratePointCorrection($amount, $specific_rate);
    }

    public function processMathValueCart($amount, $specific_rate = null){
        $math_method = Mage::getStoreConfig('rewardpoints/default/math_method', Mage::app()->getStore()->getId());
        if ($math_method == 1){
            $amount = round($amount);
        } elseif ($math_method == 0) {
            $amount = floor($amount);
        }
        return $amount;
        //return $this->ratePointCorrection($amount, $specific_rate);
    }

    public function ratePointCorrection($points, $rate = null){
        if ($rate == null){
            $baseCurrency = Mage::app()->getBaseCurrencyCode();
            $currentCurrency = Mage::app()->getStore()->getCurrentCurrencyCode();
            $rate = Mage::getModel('directory/currency')->load($baseCurrency)->getRate($currentCurrency);
        }
        if (Mage::getStoreConfig('rewardpoints/default/process_rate', Mage::app()->getStore()->getId())){
            /*if ($rate > 1){
                return $points * $rate;
            } else {*/
                return $points / $rate;
            //}
        } else {
            return $points;
        }
    }

    public function rateMoneyCorrection($money, $rate = null){
        if ($rate == null){
            $baseCurrency = Mage::app()->getBaseCurrencyCode();
            $currentCurrency = Mage::app()->getStore()->getCurrentCurrencyCode();
            $rate = Mage::getModel('directory/currency')->load($baseCurrency)->getRate($currentCurrency);
        }
        
        if (Mage::getStoreConfig('rewardpoints/default/process_rate', Mage::app()->getStore()->getId())){
            /*if ($rate < 1){
                return $money / $rate;
            } else {
                return $money * $rate;
            }*/
                
            return $money * $rate;
        } else {
            return $money;
        }
        
    }

    public function isCustomProductPoints($product){
        $product_default_points = $this->getDefaultProductPoints($product, Mage::app()->getStore()->getId());
        $catalog_points = Mage::getModel('rewardpoints/catalogpointrules')->getAllCatalogRulePointsGathered($product, $product_default_points);
        if ($catalog_points === false){
            return true;
        }
        $attribute_restriction = Mage::getStoreConfig('rewardpoints/default/process_restriction', Mage::app()->getStore()->getId());
        $product_points = $product->getData('reward_points');
        if ($product_points > 0){
            return true;
        }
        return false;
    }
    
    public function getProductPoints($product, $noCeil = false, $from_list = false, $money_points = false, $tierprice_incl_tax = null, $tierprice_excl_tax = null){
        if ($from_list){
            $product = Mage::getModel('catalog/product')->load($product->getId());            
        }
       
	//J2T TIER PRICE UPDATE
        $product_default_points = $this->getDefaultProductPoints($product, Mage::app()->getStore()->getId(), $money_points, $noCeil, false, null, $tierprice_incl_tax, $tierprice_excl_tax);
 
        //$product_default_points = $this->getDefaultProductPoints($product, Mage::app()->getStore()->getId(), $money_points, $noCeil);
        $catalog_points = Mage::getModel('rewardpoints/catalogpointrules')->getAllCatalogRulePointsGathered($product, $product_default_points);
        
        if ($catalog_points === false){
            return 0;
        }
        
        $attribute_restriction = Mage::getStoreConfig('rewardpoints/default/process_restriction', Mage::app()->getStore()->getId());
        $product_points = $product->getRewardPoints();
        $points_tobeused = 0;
        
        if ($product_points > 0){
            $points_tobeused = $product_points + (int)$catalog_points;
            if (Mage::getStoreConfig('rewardpoints/default/max_point_collect_order', Mage::app()->getStore()->getId())){
                if ((int)Mage::getStoreConfig('rewardpoints/default/max_point_collect_order', Mage::app()->getStore()->getId()) < $points_tobeused){
                    return Mage::getStoreConfig('rewardpoints/default/max_point_collect_order', Mage::app()->getStore()->getId());
                }
            }
            return ($points_tobeused);
        } else if (!$attribute_restriction) {
            //get product price include vat
            
            $_finalPriceInclTax = Mage::helper('tax')->getPrice($product, $product->getFinalPrice(), true);
            $_weeeTaxAmount = Mage::helper('weee')->getAmount($product);
            
            // fix for amount issue
            if (Mage::getStoreConfig('rewardpoints/default/exclude_tax', Mage::app()->getStore()->getId())){
                $price = Mage::helper('tax')->getPrice($product, $product->getFinalPrice(), false);
                $price = ($tierprice_excl_tax !== null) ? $tierprice_excl_tax : $price;
            } else {
                $price = $_finalPriceInclTax+$_weeeTaxAmount;
                $price = ($tierprice_incl_tax !== null) ? $tierprice_incl_tax : $price;
            }
            
            // fix rounding points
            //$price = ceil($price);
            
            if ($money_points !== false){
                $money_to_points = $money_points;
            } else {
                $money_to_points = Mage::getStoreConfig('rewardpoints/default/money_points', Mage::app()->getStore()->getId());
            }
            
            if ($money_to_points > 0){
                $price = $price * $money_to_points;
                $points_tobeused = $this->processMathValue($price + (int)$catalog_points);
            } else {
                $points_tobeused += (int)$catalog_points;
            }
            if (Mage::getStoreConfig('rewardpoints/default/max_point_collect_order', Mage::app()->getStore()->getId())){
                if ((int)Mage::getStoreConfig('rewardpoints/default/max_point_collect_order', Mage::app()->getStore()->getId()) < $points_tobeused){
                    return Mage::getStoreConfig('rewardpoints/default/max_point_collect_order', Mage::app()->getStore()->getId());
                }
            }
            /*if ($noCeil)
                return $points_tobeused;
            else {
                return ceil($points_tobeused);
            }*/
            //J2T CEIL MODIFICATION
            return $points_tobeused;

        }
        return 0;
    }

    public function convertMoneyToPoints($money, $no_correction=false){
        $points_to_get_money = Mage::getStoreConfig('rewardpoints/default/points_money', Mage::app()->getStore()->getId());
        $money_amount = $this->processMathValue($money*$points_to_get_money);
        
        if ($no_correction){
            return $money_amount;
        }
        return $this->rateMoneyCorrection($money_amount);
        //return $money_amount;
    }
    
    
    public function convertBaseMoneyToPoints($money){
        $points_to_get_money = Mage::getStoreConfig('rewardpoints/default/points_money', Mage::app()->getStore()->getId());
        $money_amount = $this->processMathBaseValue($money*$points_to_get_money);
        
        return $money_amount;
    }


    public function convertProductMoneyToPoints($money, $money_points = false){
        if ($money_points !== false){
            $points_to_get_money = $money_points;
        } else {
            $points_to_get_money = Mage::getStoreConfig('rewardpoints/default/money_points', Mage::app()->getStore()->getId());
        }
        
        $money_amount = $this->processMathValue($money*$points_to_get_money);
        return $this->rateMoneyCorrection($money_amount);
        //return $money_amount;
    }
    
    public function convertPointsToMoneyEquivalence($points_to_be_used){
        $points_to_get_money = Mage::getStoreConfig('rewardpoints/default/points_money', Mage::app()->getStore()->getId());
        //$return_value = $this->processMathValueCart($points_to_be_used/$points_to_get_money);
        $return_value = $points_to_be_used/$points_to_get_money;
        return $return_value;
    }
    

    public function convertPointsToMoney($points_to_be_used, $customer_id = null, $quote = null){
        if ($customer_id != null){
            $customerId = $customer_id;
        } else {
            $customerId = Mage::getModel('customer/session')
                                        ->getCustomerId();
        }
       

 
        $reward_model = Mage::getModel('rewardpoints/stats');
        $current = $reward_model->getPointsCurrent($customerId, $quote->getStoreId());
        
        
        //echo "$current < $points_to_be_used";
        //die;
        if ($current < $points_to_be_used) {
            Mage::getSingleton('checkout/session')->addError(Mage::helper('rewardpoints')->__('Not enough points available.'));
            Mage::helper('rewardpoints/event')->setCreditPoints(0);
            $quote 
                ->setRewardpointsQuantity(NULL)
                ->setRewardpointsDescription(NULL)
                ->setBaseRewardpoints(NULL)
                ->setRewardpoints(NULL)
                ->save();
            return 0;
        }
        $step = Mage::getStoreConfig('rewardpoints/default/step_value', $quote->getStoreId());
        $step_apply = Mage::getStoreConfig('rewardpoints/default/step_apply', $quote->getStoreId());
        if ($step > $points_to_be_used && $step_apply && !Mage::app()->getStore()->isAdmin()){
            Mage::getSingleton('checkout/session')->addError(Mage::helper('rewardpoints')->__('The minimum required points is not reached.'));
            Mage::helper('rewardpoints/event')->setCreditPoints(0);
            $quote 
                ->setRewardpointsQuantity(NULL)
                ->setRewardpointsDescription(NULL)
                ->setBaseRewardpoints(NULL)
                ->setRewardpoints(NULL)
                ->save();
            return 0;
        }

        
        if ($step_apply && !Mage::app()->getStore()->isAdmin()){
            if (($points_to_be_used % $step) != 0){
                Mage::getSingleton('checkout/session')->addError(Mage::helper('rewardpoints')->__('Amount of points wrongly used.'));
                Mage::helper('rewardpoints/event')->setCreditPoints(0);
                $quote 
                    ->setRewardpointsQuantity(NULL)
                    ->setRewardpointsDescription(NULL)
                    ->setBaseRewardpoints(NULL)
                    ->setRewardpoints(NULL)
                    ->save();
                return 0;
            }
        }

        $points_to_get_money = Mage::getStoreConfig('rewardpoints/default/points_money', $quote->getStoreId());
        $discount_amount = $this->processMathValueCart($points_to_be_used/$points_to_get_money);

        //return $this->ratePointCorrection($discount_amount);
        return $discount_amount;
    }

    public function getPointsOnOrder($cartLoaded = null, $cartQuote = null, $specific_rate = null, $exclude_rules = false, $storeId = false, $money_points = false){
        $rewardPoints = 0;
        $rewardPointsAtt = 0;

        if (!$storeId){
            $storeId = Mage::app()->getStore()->getId();
        }
        
        $customer_group_id = null;
        if ($cartLoaded == null){
            $cartHelper = Mage::helper('checkout/cart');
            $customer_group_id = $cartHelper->getCart()->getCustomerGroupId();
            //J2T Fix Magento 1.3
            if (!$customer_group_id){
                $customer_group_id = Mage::getModel('checkout/cart')->getQuote()->getCustomerGroupId();
            }
        } elseif ($cartQuote != null){
            $customer_group_id = $cartQuote->getCustomerGroupId();
        }else {
            $customer_group_id = $cartLoaded->getCustomerGroupId();
        }
        
        
        //get points cart rule
        if (!$exclude_rules){
            if ($cartLoaded != null){
                $points_rules = Mage::getModel('rewardpoints/pointrules')->getAllRulePointsGathered($cartLoaded, $customer_group_id);
            } else {
                $points_rules = Mage::getModel('rewardpoints/pointrules')->getAllRulePointsGathered(null, $customer_group_id);
            }
            if ($points_rules === false){
                return 0;
            }
            $rewardPoints += $this->processMathBaseValue($points_rules);//(int)$points_rules;
        }
        
        if ($cartLoaded == null){
            //J2T Fix Magento 1.3
            if (version_compare(Mage::getVersion(), '1.4.0', '<')){
                $items = Mage::getSingleton('checkout/cart')->getItems();
            } else {
                $cartHelper = Mage::helper('checkout/cart');
                $items = $cartHelper->getCart()->getItems();
            }
        } elseif ($cartQuote != null){
            $items = $cartQuote->getAllItems();
        }else {
            $items = $cartLoaded->getAllItems();
        }
        
        $attribute_restriction = Mage::getStoreConfig('rewardpoints/default/process_restriction', $storeId);

        foreach ($items as $_item){
            
            if ($_item->getParentItemId()) {
                if ($cartLoaded == null || $cartQuote != null){
                    $item_qty = $_item->getParentItem()->getQty();
                } else {
                    $item_qty = $_item->getParentItem()->getQtyOrdered();
                }
            } else {
                if ($cartLoaded == null || $cartQuote != null){
                    $item_qty = $_item->getQty();
                } else {
                    $item_qty = $_item->getQtyOrdered();
                }
            }
            
            //$item_default_points = $this->getItemPoints($_item, $storeId, $money_points);
            //BUNDLE FIX PRICE FIX
            if ($_item->getParentItemId()) {
                //if (is_object($_item->getParentItem()) 
		    //if(is_object($_item->getParentItem()->getProduct())) 
		
		if (!is_object($_item->getParentItem()) || !is_object($_item->getParentItem()->getProduct())){
                    continue;
                }	
		if ($_item->getParentItem()->getProduct()->getTypeId() == Mage_Catalog_Model_Product_Type::TYPE_BUNDLE 
                        && $_item->getParentItem()->getProduct()->getPrice() 
                        && $_item->getParentItem()->getProduct()->getPriceType() == Mage_Bundle_Model_Product_Price::PRICE_TYPE_FIXED){
                    continue;
               	}
            } else if (!is_object($_item->getProduct()) || ($_item->getProduct()->getTypeId() == Mage_Catalog_Model_Product_Type::TYPE_BUNDLE && $_item->getProduct()->getPriceType() == Mage_Bundle_Model_Product_Price::PRICE_TYPE_DYNAMIC)){ 
	    //} else if ($_item->getProduct()->getTypeId() == Mage_Catalog_Model_Product_Type::TYPE_BUNDLE && $_item->getProduct()->getPriceType() == Mage_Bundle_Model_Product_Price::PRICE_TYPE_DYNAMIC){
                continue;
            }
            //TODO: Make sure that checkdiscount is correct
            //$item_default_points = $this->getItemPoints($_item, $storeId, $money_points);
            $item_default_points = $this->getItemPoints($_item, $storeId, $money_points, true);
            
            //echo "product {$_item->getId()} points $item_default_points / ";
           
	    //J2T Fix Missing object
            if (!is_object($_item->getProduct()) && $_item->getProductId()){
                $_item->setProduct(Mage::getModel('catalog/product')->load($_item->getProductId()));
            } 
            //BUNDLE FIX PRICE FIX
            if (!is_object($_item->getProduct())){
	   	continue; 
	    } 
	    if ($_item->getHasChildren() && 
                    (
                        ($_item->getProduct()->getTypeId() != Mage_Catalog_Model_Product_Type::TYPE_BUNDLE 
                            || $_item->getProduct()->getTypeId() == Mage_Catalog_Model_Product_Type::TYPE_BUNDLE && $_item->getProduct()->getPrice() == 0)
                    ||
                        ($_item->getProduct()->getTypeId() == Mage_Catalog_Model_Product_Type::TYPE_BUNDLE && $_item->getProduct()->getPriceType() == Mage_Bundle_Model_Product_Price::PRICE_TYPE_FIXED)
                    )
                    
                    
                    ){
            //if ($_item->getHasChildren()){
                //getCatalogRulePointsGathered($to_validate, $item_default_points = null, $storeId = false, $default_qty = 1)
                $child_points = Mage::getModel('rewardpoints/catalogpointrules')->getAllCatalogRulePointsGathered($_item->getProduct(), $item_default_points, $storeId, $item_qty, $customer_group_id);
                if ($child_points === false){
                    continue;
                } else if(!$attribute_restriction) {
                    //FIX COMMENT
                    /*if ($cartLoaded == null || $cartQuote != null){
                        $item_qty = $_item->getQty();
                    } else {
                        $item_qty = $_item->getQtyOrdered();
                    }
                    $bundle_price = $this->getSubtotalInclTax($_item, $storeId);
                    $rewardPoints += $this->processMathBaseValue(Mage::getStoreConfig('rewardpoints/default/money_points', $storeId) * $bundle_price);*/
                    //$rewardPoints += $item_default_points;
                    $rewardPoints += $this->getItemPoints($_item, $storeId, $money_points, true);
                }
                
                continue;
            } else if ( ($_item->getProduct()->getTypeId() == Mage_Catalog_Model_Product_Type::TYPE_BUNDLE) && $_item->getProduct()->getPrice()) {
                $child_points = Mage::getModel('rewardpoints/catalogpointrules')->getAllCatalogRulePointsGathered($_item->getProduct(), $item_default_points, $storeId, $item_qty, $customer_group_id);
            }
            
            /*if ($_item->getHasChildren() && ($_item->getProduct()->getTypeId() == Mage_Catalog_Model_Product_Type::TYPE_CONFIGURABLE || $_item->getProduct()->getTypeId() == Mage_Catalog_Model_Product_Type::TYPE_BUNDLE)) {
                continue;
            }*/
            
            /*if ($_item->getParentItemId()) {
                if ($cartLoaded == null || $cartQuote != null){
                    $item_qty = $_item->getParentItem()->getQty();
                } else {
                    $item_qty = $_item->getParentItem()->getQtyOrdered();
                }
            } else {
                if ($cartLoaded == null || $cartQuote != null){
                    $item_qty = $_item->getQty();
                } else {
                    $item_qty = $_item->getQtyOrdered();
                }
            }*/
            
            $_product = Mage::getModel('catalog/product')->load($_item->getProductId());
            
            $catalog_points = Mage::getModel('rewardpoints/catalogpointrules')->getAllCatalogRulePointsGathered($_product, $item_default_points, $storeId, $item_qty, $customer_group_id);
            
            if ($catalog_points === false){
                continue;
            } else if(!$attribute_restriction && $catalog_points) {
                $rewardPoints += $this->processMathBaseValue($catalog_points * $item_qty);
            }
            $product_points = $_product->getData('reward_points');
            
            if ($product_points > 0){
                if ($_item->getQty() > 0 || $_item->getQtyOrdered() > 0){
                    $rewardPointsAtt += $this->processMathBaseValue($product_points * $item_qty);
                }
            } else if(!$attribute_restriction) {
                //check if product is option product (bundle product)
                if (!$_item->getParentItemId() && $_item->getProduct()->getTypeId() != Mage_Catalog_Model_Product_Type::TYPE_BUNDLE) {
                    //v.2.0.0 exclude_tax
                    //JON
                    /*if (Mage::getStoreConfig('rewardpoints/default/exclude_tax', $storeId)){
                        $tax_amount = 0;
                    } else {
                        $tax_amount = $_item->getBaseTaxAmount();
                    }
                    $price = $_item->getBaseRowTotal() + $tax_amount - $_item->getBaseDiscountAmount();
                    $rewardPoints += $this->processMathBaseValue(Mage::getStoreConfig('rewardpoints/default/money_points', $storeId) * $price);*/
                    //$rewardPoints += $item_default_points;
                    $rewardPoints += $this->getItemPoints($_item, $storeId, $money_points, true);
                } else if ($_item->getParentItemId() && $_item->getParentItem()->getProduct()->getTypeId() == Mage_Catalog_Model_Product_Type::TYPE_BUNDLE){
                    
                    $rewardPoints += $this->getItemPoints($_item, $storeId, $money_points, true);
                }
                
            }
        }
        $rewardPoints = $this->processMathBaseValue($this->processMathValue($rewardPoints, $specific_rate) + $rewardPointsAtt);

        if (Mage::getStoreConfig('rewardpoints/default/max_point_collect_order', $storeId)){
            if ((int)Mage::getStoreConfig('rewardpoints/default/max_point_collect_order', $storeId) < $rewardPoints){
                return ceil(Mage::getStoreConfig('rewardpoints/default/max_point_collect_order', $storeId));
            }
        }
        return ceil($rewardPoints);
    }
    
    protected function getDefaultProductPoints($product, $storeId, $money_points = false, $noCeil = true, $check_discount = false, $_item = null, $tierprice_incl_tax = null, $tierprice_excl_tax = null){
        $points = 0;
        if (!is_object($product)){
            return 0;
        }

	//J2T TIER PRICE UPDATE
        if (Mage::getStoreConfig('rewardpoints/default/exclude_tax', $storeId) && $tierprice_excl_tax !== null){
            return $tierprice_excl_tax;
        } else if ($tierprice_incl_tax !== null) {
            return $tierprice_incl_tax;
        }
        
 
	$_finalPriceInclTax = Mage::helper('tax')->getPrice($product, $product->getFinalPrice(), true);
        $_weeeTaxAmount = Mage::helper('weee')->getAmount($product);
        
        $item_qty = 1;
        if ($_item != null){
            if ($_item->getQty()){
                $item_qty = $_item->getQty();
            } elseif($_item->getQtyOrdered()) {
                $item_qty = $_item->getQtyOrdered();
            }
        }
        
        /* child item price problem */
        /*if ($_item != null && ($current_parent_item = $_item->getParentItem())){
            $price_item = $current_parent_item;
        } else if ($_item != null){
            $price_item = $_item;
        }*/
        
        if (Mage::getStoreConfig('rewardpoints/default/exclude_tax', $storeId)){
            if ($_item != null && (
                    !$_item->getParentItemId() 
                    || (
                            $_item->getParentItemId() 
                            && $_item->getParentItem()->getProduct()->getTypeId() == Mage_Catalog_Model_Product_Type::TYPE_BUNDLE 
                            && $_item->getParentItem()->getProduct()->getPriceType() == Mage_Bundle_Model_Product_Price::PRICE_TYPE_DYNAMIC) 
                    )
               ){
                //$price = $_item->getBasePrice();// / $item_qty;
                if (version_compare(Mage::getVersion(), '1.4.0', '<')){
                    $price = ($_item->getBaseRowTotal() - $_item->getBaseDiscountAmount()) / $item_qty;
                } else {
                    $price = $_item->getBasePrice();// / $item_qty;
                }
            } else {
                $price = Mage::helper('tax')->getPrice($product, $product->getFinalPrice(), false);
            }
        } else {
            if ($_item != null && (
                    !$_item->getParentItemId() 
                    || (
                            $_item->getParentItemId() 
                            && $_item->getParentItem()->getProduct()->getTypeId() == Mage_Catalog_Model_Product_Type::TYPE_BUNDLE 
                            && $_item->getParentItem()->getProduct()->getPriceType() == Mage_Bundle_Model_Product_Price::PRICE_TYPE_DYNAMIC) 
                    )
               ){
                //TODO verify WeeeTax
                //$price = ($_item->getBasePriceInclTax() + $_item->getBaseWeeeTaxAppliedAmount()) / $item_qty;
                //J2T Fix Magento 1.3
                
                if (version_compare(Mage::getVersion(), '1.4.0', '<')){
                    $price = ($_item->getBaseRowTotal() - $_item->getBaseDiscountAmount() + $_item->getBaseTaxAmount()) / $item_qty;
                } else {
                    $price = $_item->getBasePriceInclTax();// / $item_qty;
                }
            } else {
                $price = $_finalPriceInclTax+$_weeeTaxAmount;
            }
        }
        if ($check_discount && $_item != null){
            if ($_item->getBaseDiscountAmount() && $_item->getBaseDiscountAmount() > 0){
                $price -= $_item->getBaseDiscountAmount() / $item_qty;
            } else if(/*$_item->getHasChildren() && $product->getTypeId() == Mage_Catalog_Model_Product_Type::TYPE_BUNDLE && */($children = $_item->getChildren())) {
                //If bundle check discount on children
                $total_child_discount = 0;
                
                foreach ($children as $child_item){
                    if ($child_item->getParentItemId()){
                        if ($child_item->getQty()){
                            $child_item_qty = $child_item->getQty();
                        } elseif($child_item->getQtyOrdered()) {
                            $child_item_qty = $child_item->getQtyOrdered();
                        }
                        if (!$child_item_qty) $child_item_qty = 1;
                        $total_child_discount += $child_item->getBaseDiscountAmount() / $child_item_qty;
                    }
                }
                
                if ($total_child_discount){
                    $price -= ($total_child_discount / $item_qty);
                }
            }
        }
        
        //FIX Refund recalculation
        if ($_item != null){
            if ($_item->getBaseAmountRefunded()){
                $price -= $_item->getBaseAmountRefunded() / $item_qty;
                //base_tax_refunded
                if (!Mage::getStoreConfig('rewardpoints/default/exclude_tax', $storeId)){
                    $price -= $_item->getBaseTaxRefunded() / $item_qty;
                }
            }
        }
        
        if ($price <= 0){
            return 0;
        }
        //END FIX Refund recalculation
        
        
        //echo "$money_points x $price <br />";
        
        if ($money_points !== false){
            $points = $this->processMathBaseValue($money_points * $price);
        } else {
            $points = $this->processMathBaseValue(Mage::getStoreConfig('rewardpoints/default/money_points', $storeId) * $price);
        }
        //if (!$noCeil){
            /*$points = ceil($points);*/
            //J2T CEIL MODIFICATION
        //}
        return $points;
    }
    
    protected function getItemPoints($_item, $storeId, $money_points = false, $check_discount = false){
        //$_product = Mage::getModel('catalog/product')->load($_item->getProductId());
        //$points = $_product->getData('reward_points');
        //if ($points > 0){
            /*$price = $this->getSubtotalInclTax($_item, $storeId);
            
            if ($money_points !== false){
                $points = $this->processMathBaseValue($money_points * $price);
            } else {
                $points = $this->processMathBaseValue(Mage::getStoreConfig('rewardpoints/default/money_points', $storeId) * $price);
            }
            $points = ceil($points);*/
        //}
        //return $points;
        
        $item_qty = 1;
        if ($_item->getParentItemId()) {
            if ($_item->getParentItem()->getQty()){
                $item_qty = $_item->getParentItem()->getQty();
            } elseif($_item->getParentItem()->getQtyOrdered()) {
                $item_qty = $_item->getParentItem()->getQtyOrdered();
            }
        } else {
            if ($_item->getQty()){
                $item_qty = $_item->getQty();
            } elseif($_item->getQtyOrdered()) {
                $item_qty = $_item->getQtyOrdered();
            }
        }
        return $this->getDefaultProductPoints($_item->getProduct(), $storeId, $money_points, true, $check_discount, $_item) * $item_qty;
    }
    
    
    protected function getSubtotalInclTax($item, $storeId)
    {
        $baseTax = ($item->getTaxBeforeDiscount() ? $item->getTaxBeforeDiscount() : ($item->getTaxAmount() ? $item->getTaxAmount() : 0));
        $tax = ($item->getBaseTaxBeforeDiscount() ? $item->getBaseTaxBeforeDiscount() : ($item->getBaseTaxAmount() ? $item->getBaseTaxAmount() : 0));
        $discount_amount = $item->getBaseDiscountAmount();
        
        if (Mage::getStoreConfig('rewardpoints/default/exclude_tax', $storeId)){
            return $item->getBaseRowTotal()-$discount_amount;
        }
        
        //Zend_Debug::dump($item->debug());
        //die;
        return $item->getBaseRowTotal()+$tax-$discount_amount;
    }
    
}
