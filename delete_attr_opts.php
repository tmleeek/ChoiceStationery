<?php
	set_time_limit(0);

	$mageFilename = 'app/Mage.php';
	require_once $mageFilename;
	Mage::setIsDeveloperMode(true);
	ini_set('display_errors', 1);
	umask(0);
	Mage::app('admin');
	Mage::register('isSecureArea', 1);
	
	try{
		$csv = new Varien_File_Csv();
		$file = 'attr_opts.csv';
		$attribute_options = $csv->getData($file);
		$count = 0;
		$row = 0;
		$attr_opt = array();

		/*$resource = Mage::getSingleton('core/resource');
		$readConnection = $resource->getConnection('core_read');
		$writeConnection = $resource->getConnection('core_write');*/
		
		foreach($attribute_options as $attribute_option) {
			if($row == 0){
				$attribute_code = $attribute_option[0];
				$row++;
				continue;
			}
			
			array_push($attr_opt, $attribute_option[0]);
			$row++;
		}
		//echo "<pre>"; print_r($attr_opt); echo "</pre>";
		//echo $attribute_code;
		
		//$attribute_code = 'manufacturer';
		$attribute = Mage::getModel('eav/config')->getAttribute('catalog_product', $attribute_code);
		$options = $attribute->getSource()->getAllOptions();
		$optionsDelete = array();
		foreach($options as $option) {
			if ($option['value'] != "" && in_array($option['label'], $attr_opt)) {
				$optionsDelete['delete'][$option['value']] = true;
				$optionsDelete['value'][$option['value']] = true;
				//echo $option['label']."----".$option['value']."<br>";
				//echo $option['label']."----- in array<br>";
			} /*else {
				echo $option['label']."----- not in array<br>";
			}*/
		}
		
		$installer = new Mage_Eav_Model_Entity_Setup('core_setup');
		$installer->addAttributeOption($optionsDelete);
	} catch(Exception $e) {
		print_r($e);
	}
	/*echo 'total : '.$row; echo '<br/>';*/
	echo 'done';
?>
