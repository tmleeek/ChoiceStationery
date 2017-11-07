<?php
class Mxm_AllInOne_Model_Sync_Subscriber extends Mxm_AllInOne_Model_Sync_Abstract
{
    /**
     * @var array
     */
    protected $fieldMap = array(
        'email'               => 'email',
        'subscribed'          => 'subscribed',
        'title'               => '/Recipient.Title',
        'firstname'           => '/Recipient.First Name',
        'lastname'            => '/Recipient.Last Name',
        'store_id'            => '/Magento/Customer Details.Store Id',
        'subscriber_id'       => '/Magento/Customer Details.Subscriber Id',
        'is_customer'         => '/Magento/Customer Details.Is Customer',
        'customer_id'         => '/Magento/Customer Details.Customer Id',
        'total_spend'         => '/Magento/Customer Details.Total Value',
        'total_orders'        => '/Magento/Customer Details.Total Orders',
        'total_items'         => '/Magento/Customer Details.Total Items',
        'average_order_value' => '/Magento/Customer Details.Avg Order Value',
        'last_order_date'     => '/Magento/Customer Details.Last Order Date',
        'last_order_id'       => '/Magento/Customer Details.Last Order Id',
        'recent_products'     => '/Magento/Customer Details.Products Recent',
        'related_products'    => '/Magento/Customer Details.Products Related',
        'crosssell_products'  => '/Magento/Customer Details.Products Cross Sell',
        'upsell_products'     => '/Magento/Customer Details.Products Upsell',
        'updated_at'          => '/Magento/Customer Details.Updated Date',
        'created_at'          => '/Magento/Customer Details.Created Date',
        'subscribed_at'       => '/Magento/Customer Details.Subscribed Date',
    );

    /**
     * @var string
     */
    protected $importPath = '/Magento Subscribers';

    /**
     * @var array
     */
    protected $subscriberKeys = array(
        'subscriber_id',
        'store_id',
        'change_status_at',
        'customer_id',
        'subscriber_email',
        'subscriber_status',
        'subscriber_confirm_code',
        'mxm_title',
        'mxm_firstname',
        'mxm_lastname',
        'mxm_updated_at',
        'mxm_created_at',
    );

    public function __construct()
    {
        parent::__construct();
        $this->syncType = Mxm_AllInOne_Helper_Sync::SYNC_TYPE_SUBSCRIBER;
    }

    protected function doSync($ids = null)
    {
//        Mage::log(__METHOD__);

        /* @var $collection Mxm_AllInOne_Model_Resource_Subscribercustomer_Collection */
        $collection = Mage::getModel('mxmallinone/subscribercustomer')->getCollection()
            ->addAttributeToSelect('prefix')
            ->addAttributeToSelect('firstname')
            ->addAttributeToSelect('lastname')
            ->addOrderData()
            ->addRecentProductData();

        $collection->getSelect()
            ->reset(Zend_Db_Select::WHERE);

        if (!is_null($this->lastSyncTs)) {
            $collection->getSelect()->where('`subs`.`mxm_updated_at` >= ?', $this->lastSyncTs);
        }

        $storeIds = array();
        foreach ($this->getStores() as $store) {
            $storeIds[] = $store->getId();
        }

        $collection->getSelect()->where('`subs`.`store_id` in (?)', $storeIds);

        $subscribers        = array();
        $subscriber         = Mage::getModel('newsletter/subscriber');
        $orderIds           = array();
        $products           = array();
        $subscriberProducts = array();
        $idx                = 0;
        try {
            foreach ($collection as $customer) {
                $subscriber->setData($customer->toArray($this->subscriberKeys));
                $subscriberArr = $this->getSubscriberArray($subscriber, $customer);

                if ($subscriberArr['is_customer'] && $subscriberArr['recent_products']) {
                    // collect products for applying linked products
                    $subscriberProducts[$idx] = explode(',', $subscriberArr['recent_products']);
                    foreach ($subscriberProducts[$idx] as $productId) {
                        if (!isset($products[$productId])) {
                            $products[$productId] = array();
                        }
                    }
                }

                if ($subscriberArr['last_order_id']) {
                    // collect order ids for fetching of last order data
                    $orderIds[] = $subscriberArr['last_order_id'];
                }

                $subscribers[$idx] = $subscriberArr;
                $idx++;
            }

            if (!empty($products)) {
                $this->applyLinkedProducts($subscribers, $subscriberProducts, $products);
            }
        } catch (Exception $e) {
            Mage::logException($e);
            return;
        }

        if (!empty($subscribers)) {
            $this->importList($subscribers);
            Mage::log("\tSynced " . count($subscribers) . " subscribers for website {$this->getWebsite()->getCode()}");
        }

        if (!empty($orderIds)) {
            Mage::getSingleton('mxmallinone/sync_order')->runWithIds($orderIds, $this->getWebsite());
        }
    }

    protected function getSubscriberArray($subscriber, $customer)
    {
        $customerId = $subscriber->getCustomerId();
        $products = $customer->getRecentProducts();
        if ($products) {
            $products = implode(',', array_slice(explode(',', $products), 0, 6));
        }
        return array(
            'subscriber_id'       => $subscriber->getId(),
            'store_id'            => $subscriber->getStoreId(),
            'is_customer'         => $customerId > 0 ? '1' : '0',
            'customer_id'         => $customerId,
            'email'               => $subscriber->getEmail(),
            'subscribed'          => $subscriber->isSubscribed() ? '1' : '0',
            'title'               => $this->getField($customer, 'prefix', $subscriber->getMxmTitle()),
            'firstname'           => $this->getField($customer, 'firstname', $subscriber->getMxmFirstname()),
            'lastname'            => $this->getField($customer, 'lastname', $subscriber->getMxmLastname()),
            'total_spend'         => number_format($customer->getTotalSpend(), 2, '.', ''),
            'total_orders'        => $customer->getTotalOrders(),
            'total_items'         => $customer->getTotalItems(),
            'average_order_value' => number_format($customer->getAverageOrderValue(), 2, '.', ''),
            'last_order_date'     => $customer->getLastOrderDate(),
            'last_order_id'       => $customer->getLastOrderId(),
            'recent_products'     => $products,
            'related_products'    => null,
            'crosssell_products'  => null,
            'upsell_products'     => null,
            'updated_at'          => $subscriber->getMxmUpdatedAt(),
            'subscribed_at'       => $subscriber->getMxmCreatedAt(),
            'created_at'          => $customer->getCreatedAt(),
        );
    }

    protected function applyLinkedProducts(&$subscribers, $subscriberProducts, $products)
    {
        $this->fetchLinkedProducts($products);

        foreach ($subscriberProducts as $idx => $productIds) {
            $related   = array();
            $crosssell = array();
            $upsell    = array();
            foreach ($productIds as $productId) {
                if (isset($products[$productId]['relation'])) {
                    $related = array_merge($related, $products[$productId]['relation']);
                }
                if (isset($products[$productId]['cross_sell'])) {
                    $crosssell = array_merge($crosssell, $products[$productId]['cross_sell']);
                }
                if (isset($products[$productId]['up_sell'])) {
                    $upsell = array_merge($upsell, $products[$productId]['up_sell']);
                }
            }
            $subscribers[$idx]['related_products']   = implode(
                ',',
                array_slice(array_unique($related), 0, 6)
            );
            $subscribers[$idx]['crosssell_products'] = implode(
                ',',
                array_slice(array_unique($crosssell), 0, 6)
            );
            $subscribers[$idx]['upsell_products']    = implode(
                ',',
                array_slice(array_unique($upsell), 0, 6)
            );
        }
    }

    protected function fetchLinkedProducts(&$products)
    {
        $productIds = implode(',', array_keys($products));

        $coreResource  = Mage::getSingleton('core/resource');
        $connection    = $coreResource->getConnection('core_read');
        $linkTable     = $coreResource->getTableName('catalog_product_link');
        $linkTypeTable = $coreResource->getTableName('catalog_product_link_type');
        $select = Mage::getModel('Varien_Db_Select', $connection)
            ->from(array('l' => $linkTable), array(
                'product_id',
                'linked_ids' => new Zend_Db_Expr('GROUP_CONCAT(linked_product_id)')
            ))
            ->join(array('lt' => $linkTypeTable),
                'l.link_type_id = lt.link_type_id',
                array('link_type' => 'code')
            )
            ->where("product_id in ({$productIds})")
            ->group(array('product_id', 'link_type'));
        $links = $connection->query($select)->fetchAll(PDO::FETCH_ASSOC);

        foreach ($links as $link) {
            $productId = $link['product_id'];
            $linkType  = $link['link_type'];
            $products[$productId][$linkType] = explode(',', $link['linked_ids']);
        }
    }

    protected function getField($object, $key, $default = null)
    {
        $value = $object->getData($key);
        return $value ? $value : $default;
    }
}
