<?php
class Rock_ProductCPImport_Model_Cron{	
	public function productImport(){
		//do something
		$storeId = Mage::app()->getStore(); // ID of the store you want to fetch the value of
		$configValue = Mage::getStoreConfig('rock_product_import_configuration/rock_product_import_general/cron_time', $storeId);
		$time=explode(',',$configValue);
		$time[2]='00';
		$cronTime=implode(':',$time);
		$currentTime=Mage::getModel('core/date')->date('H:i').":00";

		$helper = Mage::helper('productcpimport');

		$isExtension = Mage::getStoreConfig('rock_product_import_configuration/rock_product_import_general/enabled', Mage::app()->getStore());

		if($isExtension){
			if(!((strtotime($cronTime)==strtotime($currentTime)||strtotime($cronTime)==strtotime($currentTime)+1)))
			{
			 	Mage::log("product not imported".' '.$cronTime.' '.$currentTime,null,'rocknotimportcron.log');
			}
			else
			{
				Mage::log("product is imported".' '.$cronTime.' '.$currentTime,null,'rocknotimportcron.log');
				$result = $helper->importProductUsingModel();
				$process = Mage::getSingleton('index/indexer')->getProcessByCode('catalog_product_flat');
				$process->reindexEverything();
				/*Mage::app()->getCacheInstance()->flush();
				Mage::app()->cleanCache();*/
			}
		}
	} 
}