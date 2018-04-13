<?php
/**
* @author Amasty Team
* @copyright Copyright (c) 2010-2011 Amasty (http://www.amasty.com)
*/
class Amasty_Meta_Model_Mysql4_Config_Collection extends Mage_Core_Model_Mysql4_Collection_Abstract
{
    public function _construct()
    {
        $this->_init('ammeta/config');
    }

	/**
	 * @return $this
	 */
	public function addCategoryFilter()
	{
		return $this->_addFilterByCustomField(false);
	}

	/**
	 * @return $this
	 */
	public function addCustomFilter()
	{
		return $this->_addFilterByCustomField(true);
	}

	protected function _addFilterByCustomField($value)
	{
		$this->getSelect()
			->where('is_custom = ?' , $value);

		return $this;
	}

	/**
	 * @param $urls
	 * @param null $storeId
	 *
	 * @return $this
	 */
	public function addUrlFilter($urls, $storeId = null)
	{
		foreach ($urls as &$url) {
            $url = trim($url, '/');
        }

		$this->addCustomFilter();

		$select = $this->getSelect();

		$where = array();
		foreach ($urls as $itemUrl) {
			$itemUrl = $this->getConnection()->quote($itemUrl);
			$where[] = $itemUrl . ' LIKE REPLACE(TRIM("/" FROM custom_url), "*", "%")';
		}

        // Trick to avoid quoteInto call and preserve ? character
        $wherePart = $select->getPart(Varien_Db_Select::WHERE);
        $wherePart[] = 'AND ('.implode(' OR ', $where).')';
		$select->setPart(Varien_Db_Select::WHERE, $wherePart);

		if ($storeId) {
			$select->where('store_id IN (?)', array((int) $storeId, 0));
		}

		return $this;
	}
}
