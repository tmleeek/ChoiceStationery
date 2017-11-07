<?php
class Mxm_AllInOne_Model_Sync_Store extends Mxm_AllInOne_Model_Sync_Abstract
{
    protected $fieldMap = array(
        'store_id'      => 'Store Id',
        'name'          => 'Name',
        'address'       => 'Address',
        'contact_name'  => 'Contact Name',
        'contact_email' => 'Contact Email',
        'logo_path'     => 'Logo Path',
        'telephone'     => 'Telephone',
        'base_url'      => 'Base URL',
        'media_url'     => 'Media URL',
    );

    /**
     * @var string
     */
    protected $importPath = '/Magento/Stores';

    public function __construct()
    {
        parent::__construct();
        $this->syncType = Mxm_AllInOne_Helper_Sync::SYNC_TYPE_STORE;
    }

    protected function doSync($ids = null)
    {
//        Mage::log(__METHOD__);

        $stores = array();

        foreach ($this->getStores() as $store) {
            $stores[] = $this->getStoreArray($store);
        }

        if (!empty($stores)) {
            $this->importDatatable($stores, true);
            Mage::log("\tSynced " . count($stores) . " stores for website {$this->getWebsite()->getCode()}");
        }
    }

    protected function getStoreArray(Mage_Core_Model_Store $store)
    {
        $logoPath = $store->getConfig('design/email/logo');
        $name = $store->getFrontendName();
        if (!$name) {
            $name = $store->getName();
        }
        return array(
            'store_id'      => $store->getId(),
            'name'          => $name,
            'address'       => $store->getConfig('general/store_information/address'),
            'contact_name'  => $store->getConfig('trans_email/ident_general/name'),
            'contact_email' => $store->getConfig('trans_email/ident_general/email'),
            'logo_path'     => 'email/logo' . '/' . $logoPath,
            'telephone'     => $store->getConfig(Mage_Core_Model_Store::XML_PATH_STORE_STORE_PHONE),
            'base_url'      => $store->getBaseUrl(),
            'media_url'     => $store->getBaseUrl('media'),
        );
    }
}
