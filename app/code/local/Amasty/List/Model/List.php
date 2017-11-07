<?php
/**
* @copyright Amasty.
*/ 
class Amasty_List_Model_List extends Mage_Core_Model_Abstract
{
    protected function _construct()
    {
        $this->_init('amlist/list');
    }

    public function getItems()
    {
        $items = $this->getData('items');
        if (is_null($items)) {
            $collection = Mage::getResourceModel('amlist/item_collection') 
                ->addFieldToFilter('list_id', $this->getId())
                ->setOrder('item_id')
                ->load();
        
            $products = $this->_getProductsArray($collection); 
       
            $items = array();
            foreach ($collection as $item) {
                if (isset($products[$item->getProductId()])){
                    $item->setProduct($products[$item->getProductId()]);
                    $items[] = $item;
                }
            } 
            $this->setData('items', $items);
        }
        return $items;  
    }
    
    protected function _getProductsArray($items)
    {
        $productIds = array();
        foreach ($items as $item) {
            $productIds[] = $item->getProductId();
        }
        $productIds = array_unique($productIds);
         
        $collection = Mage::getModel('catalog/product')->getResourceCollection()
             ->addIdFilter($productIds)
             ->addAttributeToSelect(Mage::getSingleton('catalog/config')->getProductAttributes());
        Mage::getSingleton('catalog/product_status')->addVisibleFilterToCollection($collection);
        //Mage::getSingleton('catalog/product_visibility')->addVisibleInCatalogFilterToCollection($collection);

        $collection->load();
        
        $products = array(); 
        foreach ($collection as $prod) {
            $products[$prod->getId()] = $prod; 
        }
        
        return $products;
    }

    public function getLastListId($customerId)
    {
         return $this->_getResource()->getLastListId($customerId); 
    }
    
    public function addItem($productId, $customOptions)
    {
        $item = Mage::getModel('amlist/item')
            ->setProductId($productId)
            ->setListId($this->getId())
            ->setQty(1);
              
        if ($customOptions) {
             foreach ($customOptions as $product) {
                 $options = $product->getCustomOptions();
                 foreach ($options as $option) {
                    if ($option->getProductId() == $productId && $option->getCode() == 'info_buyRequest'){
                        $v = unserialize($option->getValue());
                        
                        $qty = isset($v['qty']) ? max(0.01, $v['qty']) : 1;
                        $item->setQty($qty);
                        
                        // to be able to compare request in future
                        $unusedVars = array('list', 'qty', 'list_next', 'related_product');
                        foreach($unusedVars as $k){
                            if (isset($v[$k])){
                                unset($v[$k]);
                            }
                        }
                        $item->setBuyRequest(serialize($v));
                    }
                 }
            }
        } 
         
        // check if we already have the same item in the list.
        // if yes - set it's id to the current item
        $id = $item->findDuplicate();
        if ($id) {
            $item->setId($id);    
        }           
        else { 
            $item->save();
        }
        return $item;
    }
    
    public function saveDefault()
    {
        $this->_getResource()->clearDefault($this->getCustomerId());
        $this->setIsDefault(1);
        $this->save();
         
        return $this;
    }

	/**
	 * Create list from csv file
	 *
	 * @param array $data
	 * @param int $customerId
	 * @return array
	 */
	public function createListFromCsv(array $data, $customerId)
	{
		$report = array(
			'data'    => array(),
			'notices' => array(),
			'errors'  => array()
		);

		$model             = Mage::getModel('catalog/product');
		$helper            = Mage::helper('amlist/data');
		$processedProducts = array();
		$rowsAdded         = 0;
		$rowsSkipped       = 0;
		$i                 = 0;
		$list              = null;

		foreach ($data as $itemData) {
			$i++;

			//check for blank line
			if (empty($itemData[0]) && empty($itemData[1])) {
				continue;
			}

			$itemData[1] = empty($itemData[1]) || !(int) $itemData[1] ? 1 : (int) $itemData[1];
			$itemData[0] = trim($itemData[0]);

			//check for SKU existing
			if (empty($itemData[0])) {
				$this->_addMessageToReport($report, $helper->__('has no qty'), $i);
				$rowsSkipped++;
				continue;
			}

			//Check if product with specified SKU exists
			if (! ($product = $model->loadByAttribute('sku', $itemData[0]))) {
				$this->_addMessageToReport($report, $helper->__('no product with SKU "%s" was found', $itemData[0]), $i);
				$rowsSkipped++;
				continue;
			}

			//Check if product is already processed
			if (in_array($product->getId(), $processedProducts)) {
				$this->_addMessageToReport($report, $helper->__('product with SKU "%s" is already processed', $itemData[0]), $i);
				$rowsSkipped++;
				continue;
			}

			$processedProducts[] = $product->getId();

			//add product to list
			$request = new Varien_Object();
			$request->setProduct($product->getId());
			$request->setQty($itemData[1]);

			// check if params are valid
			$customOptions = $product->getTypeInstance()->prepareForCart($request, $product);

			// string == error during prepare cycle
			if (is_string($customOptions)) {
				$rowsSkipped++;
				$this->_addMessageToReport($report, $helper->__('bad request'), $i);
				continue;
			}

			//Create list
			if (is_null($list)) {
				$date = date('Y-m-d H:i:s');
				$list = Mage::getModel('amlist/list');
				$list->setTitle($date);
				$list->setCustomerId($customerId);
				$list->setCreatedAt($date);
				$list->save();
			}

			$list->addItem($product->getId(), $customOptions);
			$rowsAdded++;
		}

		if (! is_null($list)) {
			$this->_addMessageToReport($report,
				$helper->__('New product list with name "%s" was created', $list->getTitle()), 0, 'data'
			);
			$this->_addMessageToReport($report, $helper->__('Rows added: %d', $rowsAdded), 0, 'data');
			$this->_addMessageToReport($report, $helper->__('Rows skipped: %d', $rowsSkipped), 0, 'data');
		} else {
			$this->_addMessageToReport($report,
				$helper->__('Nothing was imported'), 0, 'notices'
			);
		}

		return $report;
	}

	protected function _addMessageToReport(array &$report, $message, $line = 0, $type = 'errors')
	{
		if ($type == 'errors') {
			$report[$type][] = sprintf(Mage::helper('amlist/data')->__('line') . ' %d %s', $line, strip_tags($message));
		} else {
			$report[$type][] = strip_tags($message);
		}
	}
}