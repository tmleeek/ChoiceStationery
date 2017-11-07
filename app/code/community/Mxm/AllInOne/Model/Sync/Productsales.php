<?php
class Mxm_AllInOne_Model_Sync_Productsales extends Mxm_AllInOne_Model_Sync_Abstract
{
    /**
     * @var array
     */
    protected $fieldMap = array(
        'product_store' => 'Product Store',
        'sales_7_days'  => 'Sales 7 Days',
        'sales_30_days' => 'Sales 30 Days',
    );

    /**
     * @var string
     */
    protected $importPath = '/Magento/Products';

    public function __construct()
    {
        parent::__construct();
        $this->syncType    = Mxm_AllInOne_Helper_Sync::SYNC_TYPE_PRODUCT_SALES;
    }

    protected function doSync($ids = null)
    {
//        Mage::log(__METHOD__);
        $stores = $this->getStores();

        $products  = array();
        foreach ($stores as $store) {

            /* @var $collection Mage_Catalog_Model_Resource_Product_Collection */
            $collection = Mage::getModel('catalog/product')->getCollection();
            $collection->addStoreFilter($store)
                ->addAttributeToSelect('store_id')
                ->addAttributeToFilter('visibility', array('neq' => 1));

            $this->addProductSales($collection->getSelect());

            foreach ($collection as $product) {
                $products[] = $this->getProductArray($product, $store->getId());
            }
        }

        if (!empty($products)) {
            $this->importDatatable($products);
            Mage::log("\tSynced " . count($products) . " product sales for website {$this->getWebsite()->getCode()}");
        }
    }

    protected function getProductArray($product, $storeId)
    {
        return array(
            'product_store'  => "{$product->getId()}/$storeId",
            'sales_7_days'   => (int)$product->getSalesSeven(),
            'sales_30_days'  => (int)$product->getSalesThirty()
        );
    }

    protected function addProductSales($select)
    {
        /* @var $select Varien_Db_Select */
        $sales7 = <<<SQL
SELECT product_id, sum(qty_ordered) AS sales_seven
FROM sales_flat_order_item
WHERE created_at >= (CURDATE() - INTERVAL 7 DAY)
GROUP BY product_id
SQL;
        $select->joinLeft(
            array('sales7' => new Zend_Db_Expr("($sales7)")),
            '`e`.`entity_id` = `sales7`.`product_id`'
        );

        $sales30 = <<<SQL
SELECT product_id, sum(qty_ordered) AS sales_thirty
FROM sales_flat_order_item
WHERE created_at >= (CURDATE() - INTERVAL 30 DAY)
GROUP BY product_id
SQL;
        $select->joinLeft(
            array('sales30' => new Zend_Db_Expr("($sales30)")),
            '`e`.`entity_id` = `sales30`.`product_id`'
        );

    }
}
