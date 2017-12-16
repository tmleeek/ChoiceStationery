<?php
	set_time_limit(0);

	$mageFilename = 'app/Mage.php';
	require_once $mageFilename;
	Mage::setIsDeveloperMode(true);
	ini_set('display_errors', 1);
	umask(0);
	Mage::app('admin');
	Mage::register('isSecureArea', 1);
	
	try {
		echo "Start <br>";
		echo $id = 3; echo "<br>";
		$googleshopping = Mage::getModel('simplegoogleshopping/simplegoogleshopping'); 
		$googleshopping->setId($id);

		if ($googleshopping->load($id)) {

			$googleshopping->generateXml();
			if (Mage::helper("core")->isModuleEnabled("Wyomind_Googlemerchantpromotions")) {
				if ($googleshopping->getSimplegoogleshoppingPromotions()) {
					$promoFileName = Mage::getStoreConfig('googlemerchantpromotions/settings/prefix') . str_replace(".xml", "", $googleshopping->getSimplegoogleshoppingFilename()) . Mage::getStoreConfig('googlemerchantpromotions/settings/suffix') . ".xml";
					Mage::helper("googlemerchantpromotions")->generateDatafeed($promoFileName, $googleshopping);
				}
			}

			$report = Mage::helper("simplegoogleshopping")->generationStats($googleshopping);

			if ($googleshopping->_demo) {
				Mage::getConfig()->saveConfig('simplegoogleshopping/license/activation_code', '', 'default', '0');
				Mage::getConfig()->cleanCache();
				Mage::getSingleton('core/session')->addError(Mage::helper('simplegoogleshopping')->__("Invalid license."));
			} else {

				Mage::getSingleton('core/session')->addSuccess(Mage::helper('simplegoogleshopping')->__('The data feed "%s" has been generated.', $googleshopping->getSimplegoogleshoppingFilename()) . "<br>" . $report);
			}
		} else {
			Mage::throwException(Mage::helper('simplegoogleshopping')->__('Unable to find a data feed to generate.'));
		}
		/*if ($this->getRequest()->getParam('generate')) {
			print_r($this->getRequest()->getParam('generate'));
			echo '-- done'; exit;
		} else {	
		   echo 'catch3'; exit;
		}*/
		echo "Done 1";
	} catch (Mage_Core_Exception $e) {
		//$this->_getSession()->addError($e->getMessage());
		print_r($e->getMessage());
		echo '-- catch1'; exit;
	} catch (Exception $e) {
		//$this->_getSession()->addError($e->getMessage());
		print_r($e->getMessage());
		echo '-- catch2'; exit;
	}
	
	echo "Done";
?>
