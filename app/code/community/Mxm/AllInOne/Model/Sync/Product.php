<?php
class Mxm_AllInOne_Model_Sync_Product extends Mxm_AllInOne_Model_Sync_Abstract
{
    /**
     * @var Mage_Catalog_Helper_Image
     */
    protected $imageHelper = null;

    /**
     * @var array
     */
    protected $fieldMap = array(
        'product_store'  => 'Product Store',
        'product_id'     => 'Product Id',
        'store_id'       => 'Store Id',
        'name'           => 'Name',
        'sku'            => 'Sku',
        'description'    => 'Description',
        'price'          => 'Price',
        'price_fmt'      => 'Price Formatted',
        'in_stock'       => 'In Stock',
        'image_url'      => 'Image URL',
        'image_url_path' => 'Image URL Path',
        'categories'     => 'Categories',
        'url'            => 'URL',
        'url_path'       => 'URL Path'
    );

    /**
     * @var string
     */
    protected $importPath = '/Magento/Products';

    public function __construct()
    {
        parent::__construct();
        $this->imageHelper = Mage::helper('catalog/image');
        $this->syncType    = Mxm_AllInOne_Helper_Sync::SYNC_TYPE_PRODUCT;
    }

    protected function doSync($ids = null)
    {
//        Mage::log(__METHOD__);
        $stores = $this->getStores();

        $products  = array();
        $appEmulation = Mage::getSingleton('core/app_emulation');
        foreach ($stores as $store) {
            // app emulation to emulate being in the current store
            // this is used to ensure we get an image URL for the current store
            // and to ensure we use the correct locale settings for currency
            $initialEnvironmentInfo = $appEmulation->startEnvironmentEmulation($store->getId());

            /* @var $collection Mage_Catalog_Model_Resource_Product_Collection */
            $collection = Mage::getModel('catalog/product')->getCollection();
            $collection->addStoreFilter($store)
                ->addAttributeToSelect('name')
                ->addAttributeToSelect('description')
                ->addAttributeToSelect('price')
                ->addAttributeToSelect('url_path')
                ->addAttributeToSelect('store_id')
                ->addAttributeToSelect('small_image')
                ->addAttributeToFilter('visibility', array('neq' => 1));

            if (!is_null($this->lastSyncTs)) {
                $collection->getSelect()->where('`e`.`updated_at` >= ?', $this->lastSyncTs);
            }

            foreach ($collection as $product) {
                $products[] = $this->getProductArray($product, $store);
            }

            $appEmulation->stopEnvironmentEmulation($initialEnvironmentInfo);
        }

        if (!empty($products)) {
            $this->importDatatable($products);
            Mage::log("\tSynced " . count($products) . " products for website {$this->getWebsite()->getCode()}");
        }
    }

    protected function getProductArray($product, $store)
    {
        $storeId = $store->getId();
        $imageUrl = (string)$this->imageHelper->init($product, 'small_image')->resize(125);
        $imageUrlPath = str_replace(Mage::getBaseUrl('media'), '', $imageUrl);
        $price = $product->getPrice();
        return array(
            'product_store'  => "{$product->getId()}/$storeId",
            'product_id'     => $product->getId(),
            'store_id'       => $storeId,
            'name'           => $product->getName(),
            'sku'            => $product->getSku(),
            'description'    => $product->getDescription(),
            'price'          => number_format($price, 2, '.', ''),
            'price_fmt'      => Mage::helper('core')->currency($price, true, false),
            'in_stock'       => $product->isInStock(),
            'image_url'      => $imageUrl,
            'image_url_path' => $imageUrlPath,
            'categories'     => implode(',', $product->getAvailableInCategories()),
            'url'            => $store->getBaseUrl() . $product->getUrlPath(),
            'url_path'       => $product->getUrlPath()
        );
    }
}
