<?php
/**
 * Zero1_BuyXGetTheCheapestFree_Model_Observer
 *
 * @category    Zero1
 * @package     Zero1_BuyXGetTheCheapestFree
 * @copyright   Copyright (c) 2012 Zero1 Ltd. (http://www.zero1.co.uk)
 */
class Zero1_BuyXGetTheCheapestFree_Model_Observer extends Mage_SalesRule_Model_Validator
{
    /**
     * Bespoke rule definition
     */
	const BUY_X_GET_CHEAPEST_FREE = 'buy_x_get_cheapest_free';
	
	/**
	 * Storage for the current ruleId (Used in _filter)
	 *
	 * @var int
	 */
	var $_ruleId;
	
	/**
	 * Handles the "adminhtml_block_salesrule_actions_prepareform" event
	 *
	 * @param Varien_Event_Observer $observer
	 * @return null
	 */
	public function adminhtml_block_salesrule_actions_prepareform(Varien_Event_Observer $observer)
	{
		$field = $observer->getForm()->getElement('simple_action');
		
		$options = $field->getValues();		
		$options[] = array(
				'value' => self::BUY_X_GET_CHEAPEST_FREE,
				'label' => 'Zero-1 - Buy X get the cheapest free'
		);
		
		$field->setValues($options);
	}
	
	/**
	 * Sort the input items by price
	 *
	 * @param Mage_Sales_Model_Quote_Item $a
	 * @param Mage_Sales_Model_Quote_Item $b
	 * @return int
	 */
	private function _sort(Mage_Sales_Model_Quote_Item $a, Mage_Sales_Model_Quote_Item $b)
	{		
		if($a->getPrice() == $b->getPrice())
			return 0;
		
		return ($a->getPrice() < $b->getPrice()) ? -1 : 1;
	}
	
	/**
	 * Filter the items to only ones related to the rule
	 *
	 * @param Mage_Sales_Model_Quote_Item $a
	 * @return bool
	 */
	private function _filter(Mage_Sales_Model_Quote_Item $a)
	{		
		if(in_array($this->_ruleId, explode(',', $a->getAppliedRuleIds())))
			return true;
		
		return false;
	}
	
	/**
	 * Handles the "salesrule_validator_process" event
	 *
	 * @param Varien_Event_Observer $observer
	 * @return null
	 */
	public function salesrule_validator_process(Varien_Event_Observer $observer)
	{		
		// Get the event arguments
		$rule		= $observer->getRule();
		$item		= $observer->getItem();
		$address	= $observer->getAddress();
		$quote		= $observer->getQuote();
        $qty		= $observer->getQty();
        $result		= $observer->getResult();
        
        // Get all the items in the current quote
		$quote_items = $quote->getAllItems();
        
		// Store the rule ID for usage in callbacks
		$this->_ruleId = $rule->getId();
		
		switch($rule->getSimpleAction())
		{
			case self::BUY_X_GET_CHEAPEST_FREE : 
				
				if($rule->getDiscountStep() <= 0)
					return;	// Invalid rule
				
				// Remove items that are not used by this rule
				$rule_items = array_filter($quote_items, array($this, '_filter'));
				
				// Sort by price, lowest to highest
				usort($rule_items, array($this, '_sort'));
				
				// Get the number of products to discount
				$products_to_discount = 0;
				foreach($rule_items as $rule_item)
				{
					// Do not include the simples of a configurable
					if($rule_item->getParentItem())
						continue;
				
					$products_to_discount += $rule_item->getQty();
				}
				
				$products_to_discount = floor($products_to_discount / $rule->getDiscountStep());
				
				if($rule->getDiscountQty() > 0)
					$products_to_discount = min($products_to_discount, $rule->getDiscountQty());
				
				// Identify the products that should be discounted
				$items_discounted = array();
				$discounts_applied = 0;
				
				foreach($rule_items as $rule_item)
				{
					// Do not include the simples of a configurable
					if($rule_item->getParentItem())
						continue;
					
					if($discounts_applied >= $products_to_discount)
						break; // All the discounts have been applied, stop processing more
					
					// Get the possible amount of discounts that can be applied to this item
					// Taking in to account the quanity assigned to the cart
					$amount = min($rule_item->getQty(), ($products_to_discount - $discounts_applied));
					
					if($amount > 0)
					{
						$items_discounted[$rule_item->getId()] = $amount;
						$discounts_applied += $amount;
					}
				}
				
				if(array_key_exists($item->getId(), $items_discounted))
				{
					//echo '<b>'.$item->getName().' = '.$item->getPrice().' * '.$items_discounted[$item->getId()].'</b><br/>';
					
					// Apply the discount to the current item
                    $itemPrice = $quote->getStore()->convertPrice($item->getPrice());
                    $itemBasePrice = $quote->getStore()->convertPrice($item->getBasePrice());

	            	$price = $itemPrice + ($itemPrice * ($item->getTaxPercent() / 100));
					$basePrice = $itemBasePrice + ($itemBasePrice * ($item->getTaxPercent() / 100));
	
					$discountAmount = min(100, max(0, $rule->getDiscountAmount()));
					
					$price = $price * ($discountAmount / 100);
					$basePrice = $basePrice * ($discountAmount / 100);
					
	            	$result->setDiscountAmount($price * $items_discounted[$item->getId()]);
	              	$result->setBaseDiscountAmount($basePrice * $items_discounted[$item->getId()]);
				}
				break;
		}
	}
}