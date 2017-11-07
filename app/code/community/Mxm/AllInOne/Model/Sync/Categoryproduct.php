<?php
class Mxm_AllInOne_Model_Sync_Categoryproduct extends Mxm_AllInOne_Model_Sync_Abstract
{
    protected $fieldMap = array(
        'link_id'     => 'Link Id',
        'category_id' => 'Category Id',
        'product_id'  => 'Product Id',
    );

    /**
     * @var string
     */
    protected $importPath = '/Magento/Category Products';

    /**
     * @var string
     */
    protected $categoryProductTable = null;

    /**
     * @var string
     */
    protected $productWebsiteTable = null;

    /**
     * @var Varien_Db_Select
     */
    protected $select = null;


    public function __construct()
    {
        parent::__construct();
        $this->syncType = Mxm_AllInOne_Helper_Sync::SYNC_TYPE_CATEGORY_PRODUCT;
        $coreResource  = Mage::getSingleton('core/resource');
        $connection    = $coreResource->getConnection('core_read');
        $this->categoryProductTable = $coreResource->getTableName('catalog/category_product');
        $this->productWebsiteTable  = $coreResource->getTableName('catalog/product_website');
        $this->select = Mage::getModel('Varien_Db_Select', $connection);
    }

    protected function doSync($ids = null)
    {
//        Mage::log(__METHOD__);

        $select = $this->select->reset();
        $select->from(
                array('cat_prod' => $this->categoryProductTable),
                array('category_id', 'product_id')
            )
            ->joinInner(
                array('prod_web' => $this->productWebsiteTable),
                '`cat_prod`.`product_id` = `prod_web`.`product_id`',
                array()
            )
            ->where('`prod_web`.`website_id` = ?', $this->getWebsite()->getId());

        $links = $select->query()->fetchAll(PDO::FETCH_ASSOC);

        foreach ($links as $index => &$link) {
            $link['link_id'] = $index+1;
        }

        if (!empty($links)) {
            $this->importDatatable($links, true);
            Mage::log("\tSynced " . count($links) . " links for website {$this->getWebsite()->getCode()}");
        }
    }
}
