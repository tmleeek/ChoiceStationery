<?php
/**
 *
 *
 */
 
class Juno_Jaffiliate_Model_Abstract
{	

	public function getFormattedOrderData($_pr_settings)
	{	
		$_settings = Mage::getStoreConfig('jaffiliate/general');
		$total_sale = 0;
		if(isset($_GET['orderid'])){
			$order = Mage::getModel('sales/order')->load($_GET['orderid']);
		} else {
			$order = Mage::getModel('sales/order')->load(Mage::getSingleton('checkout/session')->getLastOrderId());
		}
		if(strstr($_settings['event_id'], ',')){
			$event_id = explode(',', $_settings['event_id']);
			$event_id = trim($event_id[0]);
		} else {
			$event_id = trim($_settings['event_id']);
		}
		$items = array();
		foreach($order->getItemsCollection() as $item){
			//echo '<pre>'; print_r($item->getData()); echo '</pre>'; //exit();
			if($item->getProductType() == 'simple'){
				$item_event_id = $event_id;
				$product_id = ($item->getParentItemId() != '') ? $parent[$item->getParentItemId()]['product_id'] : $item->getProductId();
				$_product = Mage::getModel('catalog/product')->load($product_id);
				
				if($new_event_id = $_product->getEventId()){
					// -- get the event id from product model.
					if($new_event_id != $event_id){
						$item_event_id = $new_event_id;
					}
				} elseif($new_event_id = $this->hasCategoryEventId($_product->getCategoryIds())){
					// -- get the event id from product model.
					if($new_event_id != $event_id){
						$item_event_id = $new_event_id;
					}
				} else {
					$item_event_id = $event_id;
				}
				$price		= ($item->getParentItemId() != '') ? $parent[$item->getParentItemId()]['base_price'] : $this->decimalFormat($item->getBasePrice());
				$discount	= ($item->getParentItemId() != '') ? $parent[$item->getParentItemId()]['base_discount_amount'] : $this->decimalFormat($item->getBaseDiscountAmount());
				$tax 		= ($item->getParentItemId() != '') ? $parent[$item->getParentItemId()]['base_tax_amount'] : $this->decimalFormat($item->getBaseTaxAmount());
				$line_inc 	= ($item->getParentItemId() != '') ? $parent[$item->getParentItemId()]['base_row_total_incl_tax'] : $this->decimalFormat($item->getBaseRowTotalInclTax());
				$line_ex	= ($item->getParentItemId() != '') ? $parent[$item->getParentItemId()]['base_row_total'] : $this->decimalFormat($item->getBaseRowTotal());
				
				if($_settings['tax'] == 1){
					$calculated_price = $price-($discount/$item->getQtyOrdered())+$tax;
				} else {
					$calculated_price = ($price-($discount/$item->getQtyOrdered()));
				}
				
				$total_sale += $calculated_price*$item->getQtyOrdered();
				
				$items[] = array('type' 			=> $item->getProductType(),
								 'item_id' 			=> $item->getitemId(),
								 'product_id' 		=> $item->getProductId(),
								 'parent_item_id' 	=> $item->getParentItemId(),
								 'product_options' 	=> $item->getProductOptions(),
								 'weight' 			=> $item->getWeight(),
								 'qty'			 	=> $item->getQtyOrdered(),
								 'sku' 				=> $item->getSku(),
								 'name' 			=> $item->getName(),
								 'price_each'		=> $calculated_price,
								 'line_discount'	=> $discount,
								 'line_total'		=> $line_inc,
								 'line_total_ex'	=> $line_ex,
								 'event_id'			=> $item_event_id);
			}
			if($item->getProductType() == 'configurable'){
				$parent[$item->getId()] = $item->getData();
			}
		}
		$data = array(
					'id' 					=> $order->getId(),
					'coupon_code'			=> $order->getCouponCode(),
					'increment_id' 			=> $order->getIncrementId(),
					'state' 				=> $order->getState(),
					'status' 				=> $order->getStatus(),
					'shipping_description' 	=> $order->getShippingDescription(),
					'customer_id' 			=> $order->getCustomerId(),
					'base_discount_amount' 	=> $order->getBaseDiscountAmount(),
					'base_grand_total' 		=> $order->getBaseGrandTotal(),
					'base_shipping_amount' 	=> $order->getBaseShippingAmount(),
					'base_shipping_tax_amount' => $order->getBaseShippingTaxAmount(),
					'base_subtotal' 		=> $order->getBaseSubtotal(),
					'base_tax_amount' 		=> $order->getBaseTaxAmount(),
					'base_to_order_rate'	=> $order->getBaseToOrderRate(),
					'total_qty_ordered'		=> $order->getTotalQtyOrdered(),
					'weight' 				=> $order->getWeight(),
					'customer_email' 		=> $order->getCustomerEmail(),
					'customer_firstname' 	=> $order->getCustomerFirstname(),
					'customer_lastname' 	=> $order->getCustomerLastname(),
					'global_currency_code' 	=> $order->getGlobalCurrencyCode(),
					'remote_ip' 			=> $order->getRemoteIp(),
					'shipping_method' 		=> $order->getShippingMethod(),
					'item_total_sale_count' => $total_sale,
					'items' 				=> $items);
		return $data;
	}
		
	public function hasCategoryEventId($category_ids)
	{
		if(count($category_ids) == 0)
			return false;
		
		$read = $this->read();
		try
		{
			$event_id = $read->fetchOne($q=$read->select()->from(array('v'=>$this->getTableName('catalog_category_entity_varchar')), 'value as event_id')
											->joinleft(array('e'=>$this->getTableName('catalog_category_entity')), 'v.entity_id = e.entity_id', '')
											->where('v.attribute_id = ?', $this->getAttributeId('event_id', $this->getEntityType('catalog/category')))
											->where('e.entity_id IN (?)', $category_ids)
											->order('e.level DESC'));
		} catch(Exception $e) {
			return false;
		}		
		return $event_id;
	}
		
	public function toFile($type, $name, $data, $start, $location = '')
	{
		if($location == ''){
			$location = Mage::getBaseDir('media');
		}
		$filename = $name.'.'.$type;
		if(!$start){
			$handle = fopen($location.DS.$filename, 'a');
		} else {
			$handle = fopen($location.DS.$filename, 'w');
		}
		fwrite($handle, $data);
		fclose($handle);			
		return $location.DS.$filename;
	}
		
	public function convertToXml($key, $value)
	{
		if(!$value){
		//	return '<'.$key.'/>';
		}
		if(is_array($value)){
			if(!is_numeric($key)){ $item  = '<'.$key.'>'."\n"; }
			foreach($value as $key_two=>$value_two){
				$item .= $this->convertToXml($key_two, $value_two)."\n";
			}
			if(!is_numeric($key) && $key != 'category_name'){ $item .= '</'.$key.'>'; }
			return $item;
		}
		if(is_numeric($value)){
			return '<'.$key.'>'.$value.'</'.$key.'>';
		} else {
			return '<'.$key.'><![CDATA['.$value.']]></'.$key.'>';			
		}
		return $item;
	}
	
	public function getMax()
	{
		$read = $this->read();
		$count = $read->fetchOne($read->select()->from(array('e'=>$this->getTableName('catalog_product_entity')), 'COUNT(*) as count')
												->joinleft(array('v'=>$this->getTableName('catalog_product_entity_int')), 'e.entity_id = v.entity_id', '')
												->joinleft(array('s'=>$this->getTableName('catalog_product_entity_int')), 'e.entity_id = s.entity_id', '')
												->where('v.attribute_id = ?',$this->getAttributeId('visibility', $this->getEntityType('catalog/product')))
												->where('s.attribute_id = ?',$this->getAttributeId('status', $this->getEntityType('catalog/product')))
												->where('v.value IN (?)',array(2,3,4)) // -- catalog + search
												->where('s.value = ?',1)
												//->limit($limit, $offset)
												); // -- enabled			
		return $count;
	}
	
	public function getProductsData($limit, $offset)
	{
		$read = $this->read();
		
		$settings = Mage::getStoreConfig('jaffiliate/feed');
		$items = $read->fetchAll($read->select()->from(array('e'=>$this->getTableName('catalog_product_entity')))
												->joinleft(array('v'=>$this->getTableName('catalog_product_entity_int')), 'e.entity_id = v.entity_id')
												->joinleft(array('s'=>$this->getTableName('catalog_product_entity_int')), 'e.entity_id = s.entity_id')
												->where('v.attribute_id = ?',$this->getAttributeId('visibility', $this->getEntityType('catalog/product')))
												->where('s.attribute_id = ?',$this->getAttributeId('status', $this->getEntityType('catalog/product')))
												->where('v.value IN (?)',array(2,3,4)) // -- catalog + search
												->where('s.value = ?',1)
												->limit($limit, $offset)
												); // -- enabled			
		
		$attributes = $read->fetchAll($read->select()->from($this->getTableName('eav_attribute'),array('attribute_code','attribute_id'))->where('entity_type_id = ?', $this->getEntityType('catalog/product')));
		
		$i = 0;
		foreach($items as $item){
			$categoty_data = array();
			$categories = $read->fetchAll($q=$read->select()->from(array('cp'=>$this->getTableName('catalog_category_product')))
														 ->joinleft(array('v'=>$this->getTableName('catalog_category_entity_varchar')), 'cp.category_id = v.entity_id', 'value as name')
														 ->where('v.attribute_id = ?', $this->getAttributeId('name', $this->getEntityType('catalog/category')))
														 ->where('cp.product_id = ?', $item['entity_id']));
			foreach($categories as $category){
				$categoty_data[] = array('category'=>array('category_id'=>$category['category_id'],'category_name'=>$category['name']));
			}
			$ignore_array = array('category_ids','special_from_date','special_to_date','cost','weight','tier_price','news_from_date','news_to_date','gallery','minimal_price','is_recurring','recurring_profile','visibility','custom_design','custom_design_from','custom_design_to','custom_layout_update','page_layout','options_container','required_options','has_options','image_label','small_image_label','thumbnail_label','created_at','country_of_manufacture','msrp_enabled','msrp_display_actual_price_type','msrp','enable_googlecheckout','tax_class_id','gift_message_available','price_type','sku_type','weight_type','price_view','shipment_type','links_purchased_separately','samples_title','links_title','links_exist');
			foreach($attributes as $attribute){
				if(in_array($attribute['attribute_code'], $ignore_array)){
					continue;
				}
				$products[$i][$attribute['attribute_code']] = $this->getAttributeValue($attribute['attribute_code'], $item['entity_id']);
			}
			$products[$i]['entity_id'] = $item['entity_id'];
			$products[$i]['category'] = $categoty_data;
			$products[$i]['stock_level'] = number_format($this->getStockLevel($item['entity_id']),0);
			
			$special_price = $this->getAttributeValue('special_price', $item['entity_id']);
			if($special_price){
				$products[$i]['final_price'] = $special_price;
			} else {
				$products[$i]['final_price'] = $this->getAttributeValue('price', $item['entity_id']);
			}
			if($products[$i]['image'] == 'no_selection'){
				unset($products[$i]);
			}
			$i++;
		}
		
		//echo '<pre>'; print_r($products); exit();
		
		return $products;
	}
	
	public function getStockLevel($entity_id)
	{
		$read = $this->read();
		return $read->fetchOne($read->select()->from($this->getTableName('cataloginventory_stock_item'), 'qty')
												->where('product_id = ?', $entity_id)->limit(1));	
	}
	
	public function getIsInStock($entity_id) 
	{
		$read = $this->read();
		$is_in_stock = $read->fetchOne($read->select()->from($this->getTableName('cataloginventory_stock_item'), 'is_in_stock')
												->where('product_id = ?', $entity_id)->limit(1));
		if($is_in_stock == 1){
			return 'In Stock';
		} else {
			return 'Out of Stock';
		}
	}
	
	/**
	 * Get the relative table for a attribute.
	 */
	public function getAttributeValue($attribute_code, $entity_id, $eav_type = 'catalog/product')
	{	
		$read = $this->read();
		$attribute_id = $this->getAttributeId($attribute_code, $this->getEntityType($eav_type));
		$table = $this->getTableForAttribute($attribute_id, $eav_type);
		if(substr($table, -6) == 'static'){
			$value = $read->fetchOne($read->select()->from(str_replace('_static', '', $table), $attribute_code)
													->where('entity_id = ?', $entity_id)
													->limit(1));
		} else {
			$value = $read->fetchOne($read->select()->from($table, 'value')
													->where('attribute_id = ?', $attribute_id)
													->where('entity_id = ?', $entity_id)
													->where('store_id IN (?)', array(0, Mage::app()->getStore()->getStoreId()))
													->order('store_id DESC')
													->limit(1));			
		}
		if(in_array($this->_getFrontendInput($attribute_id), array('select','multiselect'))){
			$value = $this->getMultiOptionValue($value);
		}
		return $value;
	}
	
	/**
	 * Get the relative table for a attribute.
	 */
	public function getTableForAttribute($attribute_id, $eav_type)
	{
		$read = $this->read();
		$result = $read->fetchRow($read->select()->from(array('e'=>$this->getTableName('eav_attribute')), array('backend_type'))
												 ->joinleft(array('em'=>$this->getTableName('eav_entity_type')), 'e.entity_type_id = em.entity_type_id', 'entity_model')
											 	 ->where('e.attribute_id = ?', $attribute_id)
										 		 ->limit(1));
		$entity_model = explode('/',$result['entity_model']);
		$entity_model = array_merge($entity_model, array('entity', $result['backend_type']));
		$entity_model = array_flip(array_flip($entity_model));
		$table = implode('_',$entity_model);
		
		if($table == '_entity'){
			return false;
		}
		return $this->getTableName($table);
	}
	
	/**
	 * Get the Frontend Input
	 */
	protected function _getFrontendInput($attribute_id)
	{
		$read = $this->read();
		return $read->fetchOne($read->select()->from($this->getTableName('eav_attribute'), 'frontend_input')->where('attribute_id = ?', $attribute_id)->limit(1));
	}
	
	/**
	 * Get value of the multiselect.
	 */
	protected function getMultiOptionValue($value)
	{
		$read = $this->read();
		return $read->fetchOne($read->select()->from($this->getTableName('eav_attribute_option_value'), 'value')->where('option_id = ?', $value)->limit(1));
	}
	
	/**
	 * Check if the product is disabled.
	 */
	public function getIsDisabled($entity_id)
	{
		$read = $this->read();
		$value = $read->fetchOne($q=$read->select()->from($this->getTableForAttribute($this->getAttributeId('status'), $this->getEntityType('catalog/product')), 'value')->where('attribute_id = ?', $this->getAttributeId('status', $this->getEntityType('catalog/product')))->where('entity_id = ?', $entity_id)->limit(1));
		
		if($value == 2){
			return true;
		}
		return false;
	}
	
	/**
	 * Get the Attribute ID
	 */
	public function getAttributeId($attribute_code, $entity_type_id = 4)
	{
		$read = $this->read();
		return $read->fetchOne($read->select()->from($this->getTableName('eav_attribute'), 'attribute_id')->where('attribute_code = ?', $attribute_code)->where('entity_type_id = ?', $entity_type_id)->limit(1));
	}
	
	/**
	 * Get the Entity Type ID
	 */
	public function getEntityType($type)
	{
		$read = $this->read();
		return $read->fetchOne($read->select()->from($this->getTableName('eav_entity_type'), 'entity_type_id')->where('entity_model = ?', $type)->limit(1));
	}
	
	/**
	 * Get the table name with relative prefix if needed.
	 */
	public function getTableName($table)
	{
		return Mage::getSingleton('core/resource')->getTableName($table);
	}

	public function decimalFormat($value)
	{
		return number_format($value, 2, '.', '');
	}
	
	public function getAttributeOptions()
	{
		$read = $this->read();
    	$attributes = $read->fetchAll($read->select()->from(Mage::helper('jaffiliate')->getTableName('eav_attribute'))
    												 ->where('entity_type_id = ?', $this->getEntityType('catalog/product'))
    												 ->where('frontend_label != ?', '')
    												 ->where('frontend_input NOT IN (?)', array('gallery', ''))
    												 ->order('frontend_label'));
    	return $attributes;
	}
	
	public function getEventIds()
	{
		$event_setting = Mage::getStoreConfig('jaffiliate/general/event_id');
		if(!$event_setting){
			return array(array('label'=>'No Event Ids','value'=>0));
		}
		if(strstr($event_setting, ',')){
			$event_ids = explode(',', $event_setting);
		} else {
			$event_ids[0] = $event_setting;
		}
		$attributes = array(array('label'=>'Default or Parent ID','value'=>0));
		foreach($event_ids as $event_id){
			$attributes[] = array('label'=>$event_id,'value'=>$event_id);
		}
    	return $attributes;
	}
	
	public function getKeyResult()
	{
	     return false;
	}
	
	/**
	 * Database Read Adapter
	 */
	public function read()
	{
		return Mage::getSingleton('core/resource')->getConnection('core_read');
	}
}