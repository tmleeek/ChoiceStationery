<?php
class Rock_ProductNotAvailable_Block_Mostbuyproducts extends Mage_Core_Block_Template{

	public function __construct()
    {
        parent::__construct();
        $collection = $this->getMostBuyedProducts();
		/* $productCollection = Mage::getModel('catalog/product')
					->getCollection()
					->addAttributeToSelect('*')
					->addFieldToFilter('entity_id',1); */
		$this->setCollection($collection);
    }

	public function getMostBuyedProducts(){
		$productCollection = Mage::getModel('catalog/product')
			->getCollection()
			->addAttributeToSelect('*')
			->addFieldToFilter('entity_id',0);

			if(Mage::getSingleton('customer/session')->isLoggedIn()) {
			$customerData = Mage::getSingleton('customer/session')->getCustomer();
			$productIdArray=array();

			$Ordercollection=Mage::getModel('sales/order')->getCollection()
				->addFieldToFilter('customer_id',$customerData->getId());

			foreach($Ordercollection as $order){
				foreach ($order->getAllItems() as $item) { //or $order->getAllVisibleItems()
					$poroduct=$item->getProduct();

					if($poroduct->getTypeId()=="simple"){
						$productIdArray[$poroduct->getId()]=$productIdArray[$poroduct->getId()]+1;
					}
				}
			}

			arsort($productIdArray);
			$productIds=array_keys($productIdArray);
			
			if(!empty($productIds)){

				$productCollection = Mage::getModel('catalog/product')
					->getCollection()
					->addAttributeToSelect('*')
					->addAttributeToFilter('entity_id', array('in' => array($productIds)));

				$productCollection->getSelect()->order('FIELD(e.entity_id,'.implode(',',$productIds).')');
				
				return $productCollection;
			}
			else{
				return $productCollection;
			}
		}

		return $productCollection;
	}

	protected function _prepareLayout()
    {
        parent::_prepareLayout();
 
        $pager = $this->getLayout()->createBlock('page/html_pager', 'custom.pager');
        $pager->setAvailableLimit(array(5=>5,10=>10,20=>20,'all'=>'all'));
        $pager->setCollection($this->getCollection());
        $this->setChild('pager', $pager);
        $this->getCollection()->load();
        return $this;
    }
 
    public function getPagerHtml()
    {
        return $this->getChildHtml('pager');
    }
}