<?php
class Cis_Printer_Block_Printer_Result extends Mage_CatalogSearch_Block_Advanced_Result
{
	public function setListCollection()
	{
		$collection = $this->_getProductCollection();
		$models = $this->getRequest()->getParam('model_id');
		$configOptions = Mage::helper('inktonerfinder')->getStoreConfigvalues();
		
		$productsListArray = array();
		for($page = 1;;$page++)
		{
			$url = $configOptions['productslist_url'].$models.'?uid='.$configOptions['user_id'].'&security_key='.$configOptions['key'].'&password='.$configOptions['password'].'&version='.$configOptions['version'].'&language='.$configOptions['language'].'&source='.$configOptions['base_url'].'&page='.$page;
			$products = file_get_contents($url);
			$productsRecords = json_decode($products,true);
			//print_r($productsRecords);die();
			$noOfPages = $productsRecords['page']['available_pages'];
			
			$productEanNumber = array();
			$productOemsNumber = array();
			$productArtnrsNumber = array();
			foreach($productsRecords['products'] as $productsData)
			{
				$productEanNumber = array_merge($productEanNumber,$productsData['eans']);
				$productOemsNumber = array_merge($productOemsNumber,$productsData['oems']);
				if(isset($productsData['distributors']) && count($productsData['distributors']) > 0)
				{
					foreach($productsData['distributors'] as $dis)
						$productArtnrsNumber = array_merge($productArtnrsNumber,$dis['artnrs']);
				}
			}
			if($noOfPages <= $page)
				break;
		}
		
		//print_r($productArtnrsNumber);die();
		if($configOptions['search_by'] == 1)
		{
			if(count($productEanNumber) > 0 && trim($configOptions['ean_attribute']) != '')
				$collection->addFieldToFilter(trim($configOptions['ean_attribute']), array('in' => $productEanNumber));
			if(count($productOemsNumber) > 0 && trim($configOptions['oem_attribute']) != '')
				$collection->addFieldToFilter(trim($configOptions['oem_attribute']), array('in' => $productOemsNumber));
		}elseif(count($productArtnrsNumber) && $configOptions['search_by'] == 2 && trim($configOptions['artnum_attribute']))
			$collection->addFieldToFilter(trim($configOptions['artnum_attribute']), array('in' => $productArtnrsNumber));
		else
			$collection->addFieldToFilter('status', 123);
			
		$this->getChild('search_result_list')->setCollection($collection);
	}
}
