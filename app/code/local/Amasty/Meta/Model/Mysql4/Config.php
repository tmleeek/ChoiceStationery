<?php
/**
* @author Amasty Team
* @copyright Copyright (c) 2010-2011 Amasty (http://www.amasty.com)
*/ 
class Amasty_Meta_Model_Mysql4_Config extends Mage_Core_Model_Mysql4_Abstract
{
    protected $_configInheritance = true;

	public function _construct()
	{
		$this->_init('ammeta/config', 'config_id');
	}

	public function ifStoreConfigExists(Amasty_Meta_Model_Config $item)
	{
        $collection = Mage::getResourceModel('ammeta/config_collection')
            ->addFieldToFilter('store_id', $item->getStoreId());

        if ($item->getCategoryId()) {
            $collection
                ->addFieldToFilter('category_id', $item->getCategoryId())
                ->addFieldToFilter('is_custom', 0);
        } else {
            $collection
                ->addFieldToFilter('custom_url', $item->getCustomUrl())
                ->addFieldToFilter('is_custom', 1);
        }

        if ($item->getId()) {
            $collection
                ->addFieldToFilter($this->getIdFieldName(), array('neq' => $item->getId()));
        }

        return $collection->getSize() > 0;
	}

	public function getRecursionConfigData($paths, $storeId)
	{
		if (empty($paths)) {
            $paths = array(array(Mage_Catalog_Model_Category::TREE_ROOT_ID));
		}

        $distances = array();

        foreach ($paths as $pathIndex => $path)
        {
            foreach ($path as $categoryIndex => $category)
            {
                if (isset($distances[$category])) {
                    $distances[$category]['distance'] = min(
                        $categoryIndex,
                        $distances[$category]
                    );
                }
                else {
                    $distances[$category] = array(
                        'distance' => $categoryIndex,
                        'path'     => $pathIndex
                    );
                }
            }
        }

        $queryIds = array_keys($distances);

        /** @var Amasty_Meta_Model_Mysql4_Config_Collection $collection */
        $configs = Mage::getResourceModel('ammeta/config_collection')
            ->addFieldToFilter('store_id', array('in' => array(+$storeId, 0)))
            ->addFieldToFilter('category_id', array('in' => $queryIds))
            ->addFieldToFilter('is_custom', 0)
        ;

        $foundIds = $configs->getColumnValues('category_id');

        if (empty($foundIds))
            return array();

        $bestPath = null;
        $minDistance = $distances[$foundIds[0]]['distance'] + 1;

        foreach ($distances as $id => $category)
        {
            if (in_array($id, $foundIds))
            {
                if ($category['distance'] < $minDistance)
                {
                    $minDistance = $category['distance'];
                    $bestPath = $paths[$category['path']];
                }
            }
        }

        $result = array();
        $orders = array_flip($bestPath);
        foreach ($configs as $config)
        {
            if ($config->getCategoryId() == Mage_Catalog_Model_Category::TREE_ROOT_ID) {
                // Lowest priority for default category
                $config->setOrder(sizeof($bestPath));
                $result []= $config;
            }
            else if (in_array($config->getCategoryId(), $bestPath))
            {
                $config->setOrder($orders[$config->getCategoryId()]);
                $result []= $config;
            }
        }

        usort($result, array($this, '_compareConfigs'));

        if (!$this->_configInheritance)
        {
            return array($result[0]);
        }

        return $result;
    }

    protected function _compareConfigs($a, $b)
    {
        if ($a->getOrder() < $b->getOrder())
            return -1;
        else if ($a->getOrder() > $b->getOrder())
            return 1;

        return ($a->getStoreId() > $b->getStoreId()) ? 1 : -1;
    }
}
