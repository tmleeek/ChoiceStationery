<?php
/**
* @copyright Amasty.
*/ 
class Amasty_List_Helper_Data extends Mage_Core_Helper_Abstract
{
	const CSV_FOLDER_PATH = '/amasty/list/';

    public function getAddUrl($product)
    {
        $url = '';
        if (Mage::getStoreConfig('amlist/general/active'))
            $url =  $this->_getUrl('amlist/list/addItem', array('product'=>$product->getId()));
             
        return $url;
    }

	/**
	 * Parse file with products
	 *
	 * @param $csvFileName
	 * @return array
	 */
	public function parseProductCsv($csvFileName)
	{
		$csv = new Varien_File_Csv();

		return $csv->getData(Mage::getBaseDir('media') . self::CSV_FOLDER_PATH . $csvFileName);
	}

}