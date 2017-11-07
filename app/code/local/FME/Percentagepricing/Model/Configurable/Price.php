<?php

class FME_Percentagepricing_Model_Configurable_Price extends Mage_Catalog_Model_Product_Type_Configurable_Price
{
    
    public function getFinalPrice($qty=null, $product)
    {
        //load product to get the all required data
        $model = Mage::getModel('catalog/product'); 
        $_product = $model->load($product->getId());
        
        //get value of cost
        $finalPrice = $_product->getData('cost');
        //get value of disable rule
        $ruleEnable = $_product->getAttributeText('fme_rule_enable');
        
        //check rule is allow to apply
        //if($ruleEnable=="No"):
        if($ruleEnable=="Yes"):
        
                /*exit();*/
            $customergroupId = (int) Mage::getSingleton('customer/session')->getCustomerGroupId();
            $storeId = (int) Mage::app()->getStore()->getStoreId();
            //get collection of our rules only when(status=enable, storevalue is true and customer group is true)
            $collection = Mage::getModel('percentagepricing/percentagepricing')->getCollection();
            $collection->addFieldToFilter('status', 1);
            $collection->addFieldToFilter('customer_group_ids', array('finset'=>$customergroupId));
            $collection->setOrder('priorty', 'ASC');
            if($collection)
            {
                /*echo "<pre>";
                print_r($collection->getData());
                exit();*/
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
                            $_product->setIsSuperMode(true);
                            $_product->setData('price', $finalPrice);
                            $_product->setData('special_price', $finalPrice);
                            $_product->setData('final_price', $finalPrice);
                            break;
                        }
                    }
                }
            }//endif
            $product->setFinalPrice($finalPrice);
            Mage::dispatchEvent('catalog_product_get_final_price', array('product' => $product, 'qty' => $qty));
            $finalPrice = $product->getData('final_price');
            $finalPrice += $this->getTotalConfigurableItemsPrice($product, $finalPrice);
            $finalPrice += $this->_applyOptionsPrice($product, $qty, $basePrice) - $basePrice;
            $finalPrice = max(0, $finalPrice);
            $product->setFinalPrice($finalPrice);
            return $finalPrice;
        
        else:
         if (is_null($qty) && !is_null($product->getCalculatedFinalPrice())) {
            return $product->getCalculatedFinalPrice();
        }

        $basePrice = $this->getBasePrice($product, $qty);
        $finalPrice = $basePrice;
        $product->setFinalPrice($finalPrice);
        Mage::dispatchEvent('catalog_product_get_final_price', array('product' => $product, 'qty' => $qty));
        $finalPrice = $product->getData('final_price');

        $finalPrice += $this->getTotalConfigurableItemsPrice($product, $finalPrice);
        $finalPrice += $this->_applyOptionsPrice($product, $qty, $basePrice) - $basePrice;
        $finalPrice = max(0, $finalPrice);

        $product->setFinalPrice($finalPrice);
        return $finalPrice;

        endif;
         
    }







}
