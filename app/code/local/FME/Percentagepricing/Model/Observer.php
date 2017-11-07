<?php

class FME_Percentagepricing_Model_Observer
{
    
        public function apply_view($observer)
        {
            $event = $observer->getEvent();
            $products = $observer->getCollection();

             foreach($products as $product )
            {
               
                $model = Mage::getModel('catalog/product'); 
                $_product = $model->load($product->getId());
                $_oldPrice = $_product->getData('cost');
                //get value of disable rule
                $ruleEnable = $_product->getData('fme_rule_enable');
                 $customergroupId = (int) Mage::getSingleton('customer/session')->getCustomerGroupId();
                 $storeId = (int) Mage::app()->getStore()->getStoreId();
                //check rule is allow to apply
                //if($ruleEnable=="No"):
               
                if($ruleEnable==1):
                        
                    if($_product->getTypeId() === 'bundle'){
                       
                              $price =  $this->getDisplayPrice($_product);
                              $product->setData('min_price',$price);
                        }
                        
                    $finalPrice = $_product->getData('cost');
                    //get collection of our rules only when(status=enable, storevalue is true and customer group is true)
                    $collection = Mage::getModel('percentagepricing/percentagepricing')->getCollection();
                    $collection->addFieldToFilter('status', 1);
                    $collection->addFieldToFilter('customer_group_ids', array('finset'=>$customergroupId));
                    $collection->addFieldToFilter('website_ids', array(
                     array('finset' => $storeId),
                     array('finset' => 0)));
                    $collection->setOrder('priorty', 'ASC');
                    if($collection->getData()!=NULL)
                    {
                        $boolen=true;
                        foreach($collection as $_collection)
                        {
                            
                            if((in_array("0",explode(",",$_collection->getWebsiteIds())))|| in_array($storeId,explode(",",$_collection->getWebsiteIds())))
                            {
                                $getdataofrule = unserialize($_collection->getPercentagepricingRule());
                                $checkvalue = $getdataofrule['conditions'];
                                if($checkvalue['value']==0)
                                $true = "false";
                                if($checkvalue['value']==1)
                                $true = "true";
                                $check_aggregator = $getdataofrule['conditions'];
                                $check_rule = $getdataofrule['conditions'];
                                $boolen=false;
                                $break = false;

                                foreach($check_rule['css'] as $_rules)
                                {
                                
                                    if(($check_aggregator['aggregator']=="any")&&($boolen==true))
                                    {
                                        $break=true;
                                    }
                                    if($break==true)
                                    break;
                                    if($_rules['attribute']=='category_ids')
                                    {
                                        $result="0";
                                        if(array_intersect($_product->getCategoryIds(), explode(",",$_rules['value'])))
                                        $result="1";
                                        if(($true=="false")&&($result=="1"))
                                        {
                                            $boolen=false;
                                        }
                                        else if(($true=="true")&&($result=="0"))
                                        {
                                            $boolen=false;
                                        }
                                        else
                                        {
                                            $boolen=true;
                                        }
                                    }
                                    else
                                    {
                                        $result="0";
                                        if($_rules['operator']=="==")
                                        {
                                            if(in_array($_product->getData(trim($_rules['attribute'])), explode(",",$_rules['value']),false))
                                            $result="1";
                                        }
                                        if($_rules['operator']=="!=")
                                        {
                                            if(!in_array($_product->getData(trim($_rules['attribute'])), explode(",",$_rules['value']),false))
                                            $result="1";
                                        }
                                        if($_rules['operator']==">=")
                                        {
                                            if($_product->getData(trim($_rules['attribute']))>= $_rules['value'])
                                            $result="1";
                                        }
                                        if($_rules['operator']=="<=")
                                        {
                                            if($_product->getData(trim($_rules['attribute']))<= $_rules['value'])
                                            $result="1";
                                        }
                                        if($_rules['operator']==">")
                                        {
                                            if($_product->getData(trim($_rules['attribute'])) > $_rules['value'])
                                            $result="1";
                                        }
                                        if($_rules['operator']=="<")
                                        {
                                            if($_product->getData(trim($_rules['attribute'])) < $_rules['value'])
                                            $result="1";
                                        }
                                        if($_rules['operator']=="{}")
                                        {
                                            $product_attrib_value = explode(',', $product->getData(trim($_rules['attribute'])));

                                            $rule_values = $_rules['value'];
                                            $result = array_intersect($rule_values, $product_attrib_value) ;

                                            if(!empty($result))
                                            {
                                                $result="1"; 
                                            }
                                            else
                                            {
                                                 $result="0"; 
                                            }
                                        }
                                        if($_rules['operator']=="!{}")
                                        {
                                            $product_attrib_value = explode(',', $product->getData(trim($_rules['attribute'])));
                                            $rule_values = $_rules['value'];
                                            $p = array_intersect($rule_values, $product_attrib_value) ;
                                            $result = array_filter($p);
                                            if(empty($result))
                                            {
                                                $result="1"; 
                                            }
                                            else
                                            {
                                                $result="0";
                                            }
                                        }
                                        if($_rules['operator']=="()")
                                        {
                                            $product_attrib_value = explode(',', $product->getData(trim($_rules['attribute'])));

                                            $rule_values = $_rules['value'];
                                            $result = array_intersect($rule_values, $product_attrib_value) ;

                                            if(!empty($result))
                                            {
                                                $result="1"; 
                                            }
                                            else
                                            {
                                                $result="0";
                                            }
                                        }
                                        if($_rules['operator']=="!()")
                                        {
                                            $product_attrib_value = explode(',', $product->getData(trim($_rules['attribute'])));
                                            $rule_values = $_rules['value'];
                                            $p = array_intersect($rule_values, $product_attrib_value) ;
                                            $result = array_filter($p);
                                            if(empty($result))
                                            {
                                                $result="1"; 
                                            }
                                            else
                                            {
                                                $result="0";
                                            }
                                        }
                                        if(($true=="false")&&($result=="1"))
                                        {
                                            $boolen=false;
                                        }
                                        else if(($true=="true")&&($result=="0"))
                                        {
                                            $boolen=false;
                                        }
                                        else
                                        {
                                            $boolen=true;
                                        }
                                    }
                                
                                    if(($check_aggregator['aggregator']=="all")&&($boolen==false))
                                    {
                                        //echo"<br>come in aggregate all and bolen false";
                                        $break=true;
                                    }
                                
                                }
                                //echo"////////////////////////////////////////////////";
                                if($boolen==true)
                                {
                                    //for fixed
                                    if($_collection->getApply()=="2")
                                    {
                                        if($_collection->getAction()=="0")
                                        $finalPrice = $finalPrice+$_collection->getAmount();
                                        if($_collection->getAction()=="1")
                                        $finalPrice = $finalPrice-$_collection->getAmount();
                                    }
                                    //for %
                                    if($_collection->getApply()=="1")
                                    {
                                        $percentageamount = $finalPrice*($_collection->getAmount()/100);
                                        if($_collection->getAction()=="0")
                                        $finalPrice = $finalPrice+$percentageamount;
                                        if($_collection->getAction()=="1")
                                        $finalPrice = $finalPrice-$percentageamount;
                                        
                                    }
                                    $new_price = $finalPrice;
                                    break;
                                }
                            }
                             
                        }
                        
                        if($result=="1")
                        {
                            $product->setPrice($new_price);
                            $product->setFinalPrice($new_price);
                           
                        }
                        else
                        {
                            $product->setPrice($_oldPrice);
                            $product->setFinalPrice($_oldPrice);
                        }
                    }
                        
                    endif;
                
            }
            return $this;
        }
        
        
        public function getDisplayPrice($product) {
                if($product->getFinalPrice()) {
                    return $product->getFormatedPrice();
                } else if ($product->getTypeId() == Mage_Catalog_Model_Product_Type::TYPE_BUNDLE) {
                    $optionCol= $product->getTypeInstance(true)
                                        ->getOptionsCollection($product);
                    $selectionCol= $product->getTypeInstance(true)
                                           ->getSelectionsCollection(
                        $product->getTypeInstance(true)->getOptionsIds($product),
                        $product
                    );
                    $optionCol->appendSelections($selectionCol);
                    $price = $product->getPrice();
            
                    foreach ($optionCol as $option) {
                        if($option->required) {
                            $selections = $option->getSelections();
                            $minPrice = min(array_map(function ($s) {
                                            return $s->price;
                                        }, $selections));
                            if($product->getSpecialPrice() > 0) {
                                $minPrice *= $product->getSpecialPrice()/100;
                            }
            
                            $price += round($minPrice,2);
                        }  
                    }
                    return $price;
                } else {
                    return "";
                }
            }


}