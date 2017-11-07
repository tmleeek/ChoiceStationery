<?php
/**
 * Webtex
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Webtex EULA that is bundled with
 * this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://www.webtex.com/LICENSE-1.0.html
 *
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@webtex.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade the extension
 * to newer versions in the future. If you wish to customize the extension
 * for your needs please refer to http://www.webtex.com/ for more information
 * or send an email to sales@webtex.com
 *
 * @category   Webtex
 * @package    Webtex_CustomerPrices
 * @copyright  Copyright (c) 2010 Webtex (http://www.webtex.com/)
 * @license    http://www.webtex.com/LICENSE-1.0.html
 */

/**
 * Customer Prices extension
 *
 * @category   Webtex
 * @package    Webtex_CustomerPrices
 * @author     Webtex Dev Team <dev@webtex.com>
 */

class Webtex_CustomerPrices_Model_Observer
{
  public function customerSaveAfter($observer)
  {
    $params = Mage::app()->getRequest()->getParams();
                $customerPrices = Mage::getModel('customerprices/prices'); 
    if(isset($params['user_discount'])) {
      $db = Mage::getSingleton('core/resource')->getConnection('core_write');
      $db->query('set foreign_key_checks = 0');
            $customerPrices->loadByCustomer(0, $params['customer_id'],0)
                     ->setCustomerId($params['customer_id'])
                     ->setCustomerEmail(trim($params['account']['email']))
                     ->setProductId(0)
                     ->setStoreId($params['website_id'])
                     ->setPrice(0)
                     ->setQty(0)
                     ->setSpecialPrice(0)
                     ->setDiscount($params['user_discount'])
                     ->save();
      $db->query('set foreign_key_checks = 1');
    }
  }

  
  public function productSaveAfter($observer)
  {
    $params = Mage::app()->getRequest()->getParams();
                $productId = $params['id'];
    foreach($params as $key => $value) {
      if(strpos($key, 'customerprices') === false || $value == 0) {
        continue;
      }
      foreach($value as $_item) {
          $customerPrices = Mage::getModel('customerprices/prices');
           $customerPrices->loadByCustomer($productId, $_item['customerId'],$_item['qty'])
                  ->setCustomerId($_item['customerId'])
                  ->setCustomerEmail(trim($_item['email']))
                  ->setProductId($productId)
                  ->setStoreId($_item['website_id'])
                        ->setPrice($_item['price'])
                        ->setQty($_item['qty'])
                        ->setSpecialPrice($_item['specialprice']);
          if($_item['delete']=='1') {
               $customerPrices->delete();
          } else {
               $customerPrices->save();
          }
      }
    }
  }

    public function layeredPrice($observer)
    {
        $prices = Mage::getModel('customerprices/prices');
        $_session=Mage::getSingleton('customer/session');
        $collection = $observer->getCollection();

        foreach($collection as $product){
          if($_session->getCustomerId() && $_session->isLoggedIn()) {
            $p=$product->getPrice();

            if($p < $prices->getProductPrice($product) && $p < $product->getGroupPrice()){
              /*if($_SERVER["REMOTE_ADDR"]=="43.243.39.90"){
                echo $p." ".$prices->getProductPrice($product)." ".$product->getGroupPrice()."<br>";
               }*/
              $product->setPrice($p);
              continue;
            }

            $customerprice=$prices->loadCustomerPriceByCustomer($_session->getCustomerId(),$product->getId());
            $groupPrice=$product->getGroupPrice();
            $specialPrice = $prices->getProductSpecialPrice($product);

            $specialPrice=0;

            $currentDate= strtotime(Mage::getModel('core/date')->date('Y-m-d'));
            $specialPriceTo = strtotime(date('Y-m-d',strtotime($product->getSpecialToDate())));
            $interval = $specialPriceTo-$currentDate;
            $interval=floor($interval / (60 * 60 * 24));
            if($interval>=0){
              $specialPrice=$product->getMaxPrice();
            }

            if(!empty($customerprice)){
              if($customerprice['qty']<=1){
                $product->setPrice($customerprice['price']);
              }
            }
            else{
              if($product->getGroupPrice() < $prices->getProductPrice($product)){
                $product->setPrice($product->getGroupPrice());
                $product->setSpecialPrice($product->getGroupPrice());
              }
            }

            /*if($product->getGroupPrice() < $prices->getProductPrice($product)){
              //$product->setPrice($product->getGroupPrice());
              //$product->setSpecialPrice($product->getGroupPrice());
            }*/

            //echo $product->getPrice()." ".$product->getGroupPrice()."<br>";

            if(($groupPrice < $specialPrice) && ($groupPrice < $prices->getProductPrice($product))){
              $chngedToDate=strtotime($specialPriceTo.' -1 days');
              $chngedToDate=date("Y-m-d H:i:s",$currentDate);
              $product->setData('special_to_date', $chngedToDate);
              $product->setPrice($groupPrice);
              $product->setSpecialPrice($groupPrice);
            }
            else{
              if($groupPrice < $prices->getProductPrice($product)){
                $product->setPrice($groupPrice);
              }
            }

            if(($specialPrice < $groupPrice) && $specialPrice!=0 && $specialPrice < $prices->getProductPrice($product)){
              //$product->setPrice($specialPrice);
              $product->setSpecialPrice($specialPrice);
            }
          }
          else{
            if($product->getGroupPrice() < $prices->getProductPrice($product)){
              $basePrice=$product->getGroupPrice();
            }
          }

          $rulePrice  = Mage::getModel('catalogrule/rule')->calcProductPriceRule($product, $product->getMinimalPrice());

          if($rulePrice != "" && $rulePrice != 0){
            if($rulePrice < $product->getPrice()){
              $product->setPrice($rulePrice);
              if($rulePrice < $specialPrice){
                $product->setSpecialPrice($rulePrice);
              }
            }
          }
        }

        $query = Mage::app()->getRequest()->getQuery();
        if(empty($query ) || !isset($query['price'])) {
            return $this;
        }

        list($index, $range) = explode('-', $query['price']);
        $begin = $index;
        $to = $range;

        if(empty($begin)){
           $begin = 0;
        }
        
        if(empty($to)){
            $to = PHP_INT_MAX;
        }

        if(($customer = Mage::getModel('customer/session')->getCustomer()) && $customer->getId()) {
            $col = "IF(cp_prices.special_price > 0, cp_prices.special_price,IF(cp_prices.price > 0,cp_prices.price,IF(price_index.final_price IS NULL, price_index.min_price,price_index.final_price)))";
            //$col = "IF(cp_prices.special_price > 0, cp_prices.special_price,IF(cp_prices.price > 0,cp_prices.price,IF(price_index.final_price > 0, price_index.final_price,price_index.min_price)))";
            //$col = "IF(cp_prices.special_price > 0, cp_prices.special_price,IF(cp_prices.price > 0,cp_prices.price,price_index.final_price))";

            $tablePrefix = (string) Mage::getConfig()->getTablePrefix();
            $from = $collection->getSelect()->getPart('from');
            if(!in_array('cp_prices', array_keys($from))) {
                $_where = $collection->getSelect()->getPart('where');
                $collection->getSelect()
                       ->joinLeft(array('cp_prices' => $tablePrefix.'customerprices_prices'),
                            'cp_prices.product_id=e.entity_id AND cp_prices.customer_id='.$customer->getId().' and cp_prices.qty = 1',
                            array())
                        ->reset(Zend_Db_Select::WHERE)
                        ->where($col.' >= '.$begin)
                        ->where($col.' < '.$to);

                foreach($_where as $expression){
                    if(!strpos($expression, 'min_price')){
                       if(substr($expression,0,3) == "AND") {
                           $collection->getSelect()->where(substr($expression,4));
                       } else {
                           $collection->getSelect()->where($expression);
                       }
                    }
                }

            }
        }
    }
    
    public function sortByPrice($observer)
    {
        $collection = $observer->getCollection();
        $orderPart = $collection->getSelect()->getPart(Zend_Db_Select::ORDER);
        $isPriceOrder = false;
        foreach($orderPart as $v) {
            if(is_array($v) && $v[0] == 'price_index.min_price') {
                $isPriceOrder = true;
                $orderDir = $v[1];
            }
        }

        $customer = Mage::getModel('customer/session')->getCustomer();

        if(!$isPriceOrder || !$customer->getId()){
            return $this;
        }

        $from = $collection->getSelect()->getPart('from');
        if(!in_array('cp_prices', array_keys($from))) {
            $col = "IF(cp_prices.special_price > 0, cp_prices.special_price,IF(cp_prices.price > 0,cp_prices.price,IF(price_index.final_price IS NULL, price_index.min_price,price_index.final_price)))";
            //$col = "IF(cp_prices.special_price > 0, cp_prices.special_price,IF(cp_prices.price > 0,cp_prices.price,IF(price_index.final_price > 0, price_index.final_price,price_index.min_price)))";
            //$col = "IF(cp_prices.special_price > 0, cp_prices.special_price,IF(cp_prices.price > 0,cp_prices.price,price_index.final_price))";

            $tablePrefix = (string) Mage::getConfig()->getTablePrefix();
            $collection->getSelect()
                   ->joinLeft(array('cp_prices' => $tablePrefix.'customerprices_prices'),
                        'cp_prices.product_id=e.entity_id AND cp_prices.customer_id='.$customer->getId(),
                        array());

            $collection->getSelect()
                ->reset(Zend_Db_Select::ORDER)
                ->order('CAST(min_price as signed)'.$orderDir);
        }
    }

  public function collectionLoadAfter($observer)
  {
    $_session = Mage::getSingleton('adminhtml/session_quote');
    $prices = Mage::getModel('customerprices/prices');
    if($_session->getCustomerId()) {
        foreach($observer->getCollection() as $_item) {
            $oldPrice = $_item->getData('price');
            $price = $prices->getProductCustomerPrice($_item,$_session->getCustomerId());
            $specialPrice = $prices->getProductCustomerSpecialPrice($_item,$_session->getCustomerId());
                  $rulePrice  = Mage::getModel('catalogrule/rule')->calcProductPriceRule($_item, $price);
                  $specialPriceFrom = $_item->getSpecialFromDate();
                  $specialPriceTo   = $_item->getSpecialToDate();
                  $rulePrice  = Mage::getModel('catalogrule/rule')->calcProductPriceRule($_item, $price);
            if($_item->getTypeId() != 'bundle') {
                $finalPrice = $_item->getPriceModel()->calculatePrice($price,$specialPrice,$specialPriceFrom,$specialPriceTo,$rulePrice,null,null,$_item->getId());
                $_item->setCalculatedFinalPrice($finalPrice);
                $_item->setFinalPrice($finalPrice);
                if($_item->getTypeId() == 'grouped'){
                   $_item->setGroupedPrice($_item);
                }
                if($_item->getTypeId() == 'configurable') {
                   $_item->setConfPrice($_item,$oldPrice);
                }
            } else {
               $minP = $_item->getMinimalPrice($_item);
               $maxP = $_item->getMaximalPrice($_item);
               $_item->setMinPrice($minP);
               $_item->setMinimalPrice($minP);
               $_item->setMaxPrice($maxP);
               $_item->setMaximalPrice($maxP);
            }
        }
     } else {
        foreach($observer->getCollection() as $_item) {
            $oldPrice = $_item->getData('price');
            $price = $prices->getProductPrice($_item);
            $specialPrice = $prices->getProductSpecialPrice($_item);
                  $rulePrice  = Mage::getModel('catalogrule/rule')->calcProductPriceRule($_item, $price);
                  $specialPriceFrom = $_item->getSpecialFromDate();
                  $specialPriceTo   = $_item->getSpecialToDate();
            if($_item->getTypeId() != 'bundle') {
                $finalPrice = $_item->getPriceModel()->calculatePrice($price,$specialPrice,$specialPriceFrom,$specialPriceTo,$rulePrice,null,null,$_item->getId());
                $_item->setCalculatedFinalPrice($finalPrice);
                $_item->setFinalPrice($finalPrice);
                $_item->setData('price',$price);
                $_item->setData('minimal_price',$price);
                if($_item->getTypeId() == 'grouped'){
                   $_item->setGroupedPrice($_item);
                }
                if($_item->getTypeId() == 'configurable') {
                   $_item->setConfPrice($_item,$oldPrice);
                }
            } else {
               $minP = $_item->getMinimalPrice($_item);
               $maxP = $_item->getMaximalPrice($_item);
               $_item->setMinPrice($minP);
               $_item->setMinimalPrice($minP);
               $_item->setMaxPrice($maxP);
               $_item->setMaximalPrice($maxP);
            }
        }
     }
          return $this;
  }
  
  public function productLoadAfter($observer)
  {
    $_session=Mage::getSingleton('customer/session');
    if($_session->isLoggedIn()){
      $prices = Mage::getModel('customerprices/prices');
      $product = $observer->getEvent()->getProduct();
      $specialPrice=0;
      /*$_session=Mage::getSingleton('customer/session');
      if(!$_session->isLoggedIn()){
        $_session->setCustomerGroupId(0);
      }*/

      if($_SERVER["REMOTE_ADDR"]=="43.243.39.90"){
        //echo $product->getPrice()." ".$prices->getProductPrice($product)." ".$product->getGroupPrice()."<br>";
        /*$p=(float)$product->getPrice();
        $cp=(float)$prices->getProductPrice($product);
        var_dump($p);
        var_dump($cp);
        echo "<br>";
        var_dump($p < $cp);
        echo "<br>";*/  
      }

      $p=$product->getPrice();
      $rulePrice  = Mage::getModel('catalogrule/rule')->calcProductPriceRule($product, $product->getMinimalPrice());

      if($p < $prices->getProductPrice($product) && $p < $product->getGroupPrice() && ($p < $rulePrice && $rulePrice!='' && $rulePrice!=0)){
        /*if($_SERVER["REMOTE_ADDR"]=="43.243.39.90"){
          echo $p." ".$prices->getProductPrice($product)." ".$product->getGroupPrice()."<br>";
         }*/
        $product->setPrice($p);
        return $this;
      }
      else{
        $product->setPrice($p);
      }

      $currentDate= strtotime(Mage::getModel('core/date')->date('Y-m-d'));
      $specialPriceTo = strtotime(date('Y-m-d',strtotime($product->getSpecialToDate())));
      $interval = $specialPriceTo-$currentDate;
      $interval=floor($interval / (60 * 60 * 24));
      if($interval>=0){
        $specialPrice=$product->getSpecialPrice();
      }

      /*$_taxHelper  = Mage::helper('tax');

      var_dump($_taxHelper->displayBothPrices());
      exit;*/

      /*echo $product->getSpecialPrice();
      echo "<pre>";
      print_r(get_class_methods($product));
      echo "</pre>";
      exit;*/

      /*echo $product->getFinalPrice();
      exit;*/

            /*echo "<pre>";
            print_r(get_class_methods($product));
            echo "</pre>";
            exit;*/

            //$_session = Mage::getSingleton('adminhtml/session_quote');
            $_session=Mage::getSingleton('customer/session');
            
            if($_session->getCustomerId() && $_session->isLoggedIn()) {

              $customerprice=$prices->loadCustomerPriceByCustomer($_session->getCustomerId(),$product->getId());

              if(!empty($customerprice) && $customerprice['qty']>1){
                $tierPrices[] = array(
                  'website_id'  => Mage::app()->getWebsite()->getId(),
                  'cust_group'  => $_session->getCustomerGroupId(),
                  'price_qty'   => $customerprice['qty'],
                  'price'       => $customerprice['price']
                 );

                $product->setTierPrice($tierPrices);
              }

              $basePrice=$prices->getProductCustomerPrice($product,$_session->getCustomerId());

              $groupPrice=$product->getGroupPrice();

              if($groupPrice!=''){
                if(!empty($customerprice)){
                  if($groupPrice < $basePrice && $groupPrice < $customerprice['price']){
                    $basePrice=$groupPrice;
                  }
                }
                else{
                  if($groupPrice < $basePrice){
                    $basePrice=$groupPrice;
                  }
                }
              }

              /*if($specialPrice>0 && $specialPrice<$basePrice){
                $basePrice=$specialPrice;
              }*/

              $customerSpcialPrice=$prices->getProductCustomerSpecialPrice($product,$_session->getCustomerId());

              if($customerSpcialPrice > $specialPrice){
                $customerSpcialPrice=$specialPrice;
              }

              /*echo $basePrice;
              exit;*/ 

              //$basePrice=$prices->getProductCustomerPrice($product,$_session->getCustomerId());

              $product->setPrice($basePrice);
              $product->setSpecialPrice($customerSpcialPrice);

              if (Mage::app()->getFrontController()->getAction()->getFullActionName() == 'catalog_category_view'){
                $product->setSpecialPrice($basePrice);
              }

              /*$product->setPrice($prices->getProductCustomerPrice($product,$_session->getCustomerId()));
              $product->setSpecialPrice($prices->getProductCustomerSpecialPrice($product,$_session->getCustomerId()));*/
      } else {
              $price = $prices->getProductPrice($product);
              $specialPrice = $prices->getProductSpecialPrice($product);
              $rulePrice  = Mage::getModel('catalogrule/rule')->calcProductPriceRule($product, $price);
              $specialPriceFrom = $product->getSpecialFromDate();
              $specialPriceTo   = $product->getSpecialToDate();
        if($product->getTypeId() != 'bundle') {
            $finalPrice = $product->getPriceModel()->calculatePrice($price,$specialPrice,$specialPriceFrom,$specialPriceTo,$rulePrice,null,null,$product->getId());
            $product->setCalculatedFinalPrice($finalPrice);
            $product->setFinalPrice($finalPrice);
            $product->setData('price',$price);
        }
      }

      /*echo "<pre>";
      print_r($product->getData());
      echo "</pre>";
      exit;*/

      return $this;
    }
  }

  public function processFrontFinalPrice($observer)
  {
      $_session=Mage::getSingleton('customer/session');

      /*if(!$_session->isLoggedIn()){
        $_session->setCustomerGroupId(0);
      }*/

      if($_session->isLoggedIn()){
        $product = $observer->getEvent()->getProduct();
        $prices = Mage::getModel('customerprices/prices');

        $specialPrice=0;
        $groupFlag=0;

        $p=$product->getPrice();
        $rulePrice  = Mage::getModel('catalogrule/rule')->calcProductPriceRule($product, $product->getMinimalPrice());

        if($p < $prices->getProductPrice($product) && $p < $product->getGroupPrice() && ($p < $rulePrice && $rulePrice!='' && $rulePrice!=0)){
          $product->setPrice($p);
          return $this;
        }
        else{
          $product->setPrice($p);
        }

        $currentDate= strtotime(Mage::getModel('core/date')->date('Y-m-d'));
        $specialPriceToA = strtotime(date('Y-m-d',strtotime($product->getSpecialToDate())));
        $interval = $specialPriceToA-$currentDate;
        $interval=floor($interval / (60 * 60 * 24));
        //$interval=1;
        if($interval>=0){
          $specialPrice=$product->getSpecialPrice();
        }

        /*echo $specialPrice;
        exit;*/

        if($product->getTypeId() != 'bundle') {
          /*if (Mage::app()->getFrontController()->getAction()->getFullActionName() == 'checkout_cart_index' || Mage::app()->getFrontController()->getAction()->getFullActionName()=='onestepcheckout_index_index') 
          {*/

          if(Mage::app()->getFrontController()->getAction()->getFullActionName()!="catalog_product_view"){
            $qty=1;
            $cartItem = Mage::getSingleton('checkout/cart')->getQuote()->getItemByProduct($product);
            if ($cartItem) {
                $qty=(int) $cartItem->getQty();
            }
            $basePrice  = $prices->getProductPrice($product,$qty);
          } 
          else{
            $basePrice  = $prices->getProductPrice($product);
          }

          $customerprice=array();
          if($_session->getCustomerId() && $_session->isLoggedIn()) {
            $customerprice=$prices->loadCustomerPriceByCustomer($_session->getCustomerId(),$product->getId());
          }
          
          /*if(empty($customerprice)){
            if($product->getGroupPrice() < $prices->getProductPrice($product)){
              $basePrice=$product->getGroupPrice();
            }
          }*/

          $groupPrice=$product->getGroupPrice();

          //echo $customerprice['price']." ".$basePrice." ".$groupPrice;

          if($groupPrice!=''){
            if(!empty($customerprice)){
              if($groupPrice < $basePrice && $groupPrice < $customerprice['price']){
                $basePrice=$groupPrice;
                $groupFlag=1;
              }
            }
            else{
              if($groupPrice < $basePrice){
                $basePrice=$groupPrice;
                $groupFlag=1;
              }
            }
          }

          /*echo $specialPrice;
          exit;*/

          $flagLessBase=0;
          if($basePrice < $specialPrice || $specialPrice==0 || $specialPrice==''){
            //$specialPrice=0;
            $flagLessBase=1;
          }
          else{
            $groupFlag=0;
          }

          /*echo $basePrice." ".$specialPrice;
          exit;*/
                    if($specialPrice==0){
                      $specialPrice = $prices->getProductSpecialPrice($product);
                    }

                    if($specialPrice > $basePrice){
                      $specialPrice=$basePrice;
                    }

                    /*echo $specialPrice." ".$basePrice." test";
                    exit;*/

                    $specialPriceFrom = $product->getSpecialFromDate();
                    $specialPriceTo   = $product->getSpecialToDate();
                    $specialPriceFrom = $product->getSpecialFromDate();
                    $specialPriceTo = $product->getSpecialToDate();
                    $rulePrice  = Mage::getModel('catalogrule/rule')->calcProductPriceRule($product, $product->getMinimalPrice());
                    $finalPrice = $product->getPriceModel()->calculatePrice(
                                  $basePrice,
                                  $specialPrice,
                                  $specialPriceFrom,
                                  $specialPriceTo,
                                  $rulePrice,
                                  null,
                                  null,
                                  $product->getId());
                   $product->setCalculatedFinalPrice($finalPrice);

                   /*echo " ".$basePrice." ".$specialPrice." ".$finalPrice." ".$groupFlag;
                   exit;*/

                   if(Mage::app()->getFrontController()->getAction()->getFullActionName()=="catalog_product_view"){
                      if($rulePrice!='' && $rulePrice!=0){
                        if($rulePrice < $basePrice && ($rulePrice < $specialPrice || $specialPrice=='')){
                          $product->setData('final_price', $rulePrice);
                          $product->setData('price', $rulePrice);
                          $product->setSpecialPrice($rulePrice);
                          return $this;
                        }
                      }
                    }

                   if(($basePrice < $specialPrice) || $groupFlag==1){
                      $chngedToDate=strtotime($specialPriceTo.' -1 days');
                      $chngedToDate=date("Y-m-d H:i:s",$currentDate);
                      $product->setData('special_to_date', $chngedToDate);
                      $product->setData('final_price', $finalPrice);
                      $product->setData('price', $finalPrice);
                      $product->setSpecialPrice($specialPrice);
                   }
                   else{
                      $product->setData('final_price', $finalPrice);
                      //$product->setData('price', $finalPrice);
                      $product->setSpecialPrice($specialPrice);
                   }
              }
              else{
                if(Mage::app()->getFrontController()->getAction()->getFullActionName()=="checkout_cart_index"){
                  $product = $observer->getEvent()->getProduct();
                  $prices = Mage::getModel('customerprices/prices');
                  $price = $prices->getProductPrice($product);
                    $specialPrice = $prices->getProductSpecialPrice($product);
                    $rulePrice  = Mage::getModel('catalogrule/rule')->calcProductPriceRule($product, $price);
                    $specialPriceFrom = $product->getSpecialFromDate();
                    $specialPriceTo   = $product->getSpecialToDate();
                  if($product->getTypeId() != 'bundle') {
                      echo $finalPrice = $product->getPriceModel()->calculatePrice($price,$specialPrice,$specialPriceFrom,$specialPriceTo,$rulePrice,null,null,$product->getId());
                      $product->setCalculatedFinalPrice($finalPrice);
                      $product->setSpecialPrice($finalPrice);
                      $product->setFinalPrice($finalPrice);
                      $product->setData('price',$price);
                  }
                }
              }
            return $this;
          }
        }

  public function processBackFinalPrice($observer)
  {
           $_session = Mage::getSingleton('adminhtml/session_quote');
     $customer = $_session->getCustomerId();
     $prod    = $observer->getEvent()->getProduct();
     $qty     = $observer->getEvent()->getQty();
     if($customer && $prod->getTypeId() != 'bundle') {
      $product = Mage::getModel('catalog/product')->load($prod->getId());
      $basePrice = $product->getData('price');
      $prices = Mage::getModel('customerprices/prices');
      $price     = $prices->getProductCustomerPrice($product,$customer,$qty);
      $sPrice    = $prices->getProductCustomerSpecialPrice($product,$customer,$qty);
            $specialPriceFrom = $product->getSpecialFromDate();
            $specialPriceTo   = $product->getSpecialToDate();
            $rulePrice  = Mage::getModel('catalogrule/rule')->calcProductPriceRule($product, $basePrice);
            $finalPrice = $product->getPriceModel()->calculatePrice(
                            $basePrice,
                            $sPrice,
                            $specialPriceFrom,
                            $specialPriceTo,
                            $rulePrice,
                            null,
                            null,
                            $product->getId());
             $product->setCalculatedFinalPrice($finalPrice);
             $product->setData('final_price', $finalPrice);
             $product->setData('price',$price);
            }
          return $this;
        }

    public function coreBlockAbstractToHtmlBefore($observer) 
    {
        $block = $observer->getBlock();
        $catalogProductEditTabsClass = Mage::getConfig()->getBlockClassName('adminhtml/catalog_product_edit_tabs');
        if ($catalogProductEditTabsClass == get_class($block) && $block->getProduct()->getTypeId() && $block->getProduct()->getId()) {
            $block->addTab('customerprices', array(
                'label'     => Mage::helper('catalog')->__('Prices per Customer'),
                'content'   => $block->getLayout()
                    ->createBlock('customerprices/Adminhtml_Catalog_Product_Tab_CustomerPrices')
                    ->setTitle(Mage::helper('catalog')->__('Prices per Customer'))
                    ->toHtml(),
            ));
        }
        
        return $this;
    }
}