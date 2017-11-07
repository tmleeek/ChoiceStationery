<?php
class Mxm_AllInOne_Model_Sync_Category extends Mxm_AllInOne_Model_Sync_Abstract
{
    protected $fieldMap = array(
        'category_id' => 'Category Id',
        'name'        => 'Name',
        'parent_id'   => 'Parent Id',
    );

    /**
     * @var string
     */
    protected $importPath = '/Magento/Categories';

    public function __construct()
    {
        parent::__construct();
        $this->syncType = Mxm_AllInOne_Helper_Sync::SYNC_TYPE_CATEGORY;
    }

    protected function doSync($ids = null)
    {
//        Mage::log(__METHOD__);

        $categories = array();
        /* @var $collection Mage_Catalog_Model_Resource_Category_Collection */
        $collection = Mage::getModel('catalog/category')->getCollection()
            ->addAttributeToSelect('name');

        foreach ($collection as $category) {
            $categories[] = $this->getCategoryArray($category);
        }

        if (!empty($categories)) {
            $this->importDatatable($categories, true);
            Mage::log("\tSynced " . count($categories) . " categories for website {$this->getWebsite()->getCode()}");
        }
    }

    protected function getCategoryArray(Mage_Catalog_Model_Category $category)
    {
        return array(
            'category_id' => $category->getId(),
            'name'        => $category->getName(),
            'parent_id'   => $category->getParentId(),
        );
    }
}
