<?php
class Cis_Inktonerfinder_Helper_Data extends Mage_Core_Helper_Abstract
{	
	public function getStoreConfigvalues()
        {
            
            $inktonerfinder['brands_url'] = 'http://ws.cloud.topdata.de/finder/ink_toner/brands?';
            $inktonerfinder['modelseries_url'] = 'http://ws.cloud.topdata.de/finder/ink_toner/modelseries?';
            $inktonerfinder['modeltype_url'] = 'http://ws.cloud.topdata.de/finder/ink_toner/devicetypes?';
            $inktonerfinder['model_url'] = 'http://ws.cloud.topdata.de/finder/ink_toner/models?';
            $inktonerfinder['productslist_url'] = 'http://ws.cloud.topdata.de/product_accessories/';
            $inktonerfinder['model_text_search'] = 'http://ws.cloud.topdata.de/finder/ink_toner/search?';
            $inktonerfinder['manufacturer_info'] = 'http://ws.cloud.topdata.de/product/';
            $inktonerfinder['base_url'] = Mage::getBaseUrl();
            $inktonerfinder['user_id'] = Mage::getStoreConfig('inktonerfinder_options/inktonerfinder_group/user_id');
            $inktonerfinder['key'] = Mage::getStoreConfig('inktonerfinder_options/inktonerfinder_group/key');
            $inktonerfinder['password'] = Mage::getStoreConfig('inktonerfinder_options/inktonerfinder_group/password');
            //$inktonerfinder['version'] = Mage::getStoreConfig('inktonerfinder_options/inktonerfinder_group/version');
            $inktonerfinder['version'] = '100';
            //$inktonerfinder['language'] = Mage::getStoreConfig('inktonerfinder_options/inktonerfinder_group/language');
            $locale = explode('_',Mage::getStoreConfig('general/locale/code'));
            if(isset($locale[0]) && strlen($locale[0]) == 2)
            	$inktonerfinder['language'] = $locale[0];
            else
            	$inktonerfinder['language'] = 'en';
            $inktonerfinder['search_field'] = Mage::getStoreConfig('inktonerfinder_options/inktonerfinder_group/search_field');
            $inktonerfinder['remove_modelseries'] = Mage::getStoreConfig('inktonerfinder_options/inktonerfinder_group/remove_modelseries');
            $inktonerfinder['search_by'] = Mage::getStoreConfig('inktonerfinder_options/inktonerfinder_group/search_by');
            $inktonerfinder['search_dimension'] = Mage::getStoreConfig('inktonerfinder_options/inktonerfinder_group/search_dimension');
            $inktonerfinder['ean_attribute'] = Mage::getStoreConfig('inktonerfinder_options/inktonerfinder_group/ean_attribute');
            $inktonerfinder['oem_attribute'] = Mage::getStoreConfig('inktonerfinder_options/inktonerfinder_group/oem_attribute');
            $inktonerfinder['artnum_attribute'] = Mage::getStoreConfig('inktonerfinder_options/inktonerfinder_group/artnum_attribute');
            return $inktonerfinder;
        }
        
        public function getManufacturersRecords()
        {
            $configOptions = $this->getStoreConfigvalues();
            $url = $configOptions['brands_url'].'uid='.$configOptions['user_id'].'&security_key='.$configOptions['key'].'&password='.$configOptions['password'].'&version='.$configOptions['version'].'&language='.$configOptions['language'];
            $manufacturers  =  file_get_contents ( $url );
            $manufacturersRecords  =  json_decode ( $manufacturers, true ) ;
            return $manufacturersRecords;
        }       
        
}
	 
