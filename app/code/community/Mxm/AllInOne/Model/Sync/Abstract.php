<?php

abstract class Mxm_AllInOne_Model_Sync_Abstract
{
    /**
     * @var Mxm_AllInOne_Helper_Sync
     */
    protected $syncHelper = null;

    /**
     * @var integer
     */
    protected $syncType = null;

    /**
     * @var string
     */
    protected $lastSyncTs = null;

    /**
     * @var Mage_Core_Model_Website
     */
    protected $website = null;

    /**
     * @var int
     */
    protected $websiteCount = null;

    /**
     * @var array
     */
    protected $fieldMap = null;

    /**
     * @var string
     */
    protected $importPath = null;

    public function __construct()
    {
        $this->syncHelper = Mage::helper('mxmallinone/sync');
    }

    public function run()
    {
        $newTs = Varien_Date::now();
        $websites = Mage::app()->getWebsites();
        $this->websiteCount = count($websites);
        foreach ($websites as $website) {
            $this->lastSyncTs = $this->syncHelper
                ->getLastSyncTs($this->syncType, $website);

            if (!$this->syncHelper->canSync($this->syncType, $website)) {
                continue;
            }
            $this->website = $website;
            try {
                $this->doSync();

                $this->syncHelper
                    ->setLastSyncTs($this->syncType, $newTs, $website);
            } catch (Exception $e) {
                $syncName = $this->syncHelper->syncName($this->syncType);
                Mage::log("Failed to sync $syncName for website {$website->getCode()}");
                Mage::logException(
                    new Exception(
                        "Failed to sync $syncName for website {$website->getCode()}",
                        null,
                        $e
                    )
                );
                return;
            }
        }
        $this->syncHelper->setForceSync($this->syncType, false);
    }

    public function runWithIds($ids, $website)
    {
        $newTs = Varien_Date::now();

        $websites = Mage::app()->getWebsites();
        $this->websiteCount = count($websites);
        $this->website = $website;
        try {
            $this->doSync($ids);

            $this->syncHelper
                ->setLastSyncTs($this->syncType, $newTs, $website);
        } catch (Exception $e) {
            $syncName = $this->syncHelper->syncName($this->syncType);
            Mage::log("Failed to sync $syncName for website {$website->getCode()}");
            Mage::logException(
                new Exception(
                    "Failed to sync $syncName for website {$website->getCode()}",
                    null,
                    $e
                )
            );
            return;
        }
    }

    /**
     * Returns true if this magento installation has more than one store
     *
     * @return boolean
     */
    public function isMultiStore()
    {
        return $this->websiteCount > 1 || count($this->getStores()) > 1;
    }

    /**
     * Get the current website which we are performing setup for
     *
     * @return Mage_Core_Model_Website
     */
    public function getWebsite()
    {
        return $this->website;
    }

    /**
     * Get the stores for the current website
     *
     * @return array
     */
    public function getStores()
    {
        return $this->getWebsite()->getStores();
    }

    /**
     * Get the api instance for a service
     *
     * @param string $service
     * @return Mxm_AllInOne_Model_Api|Mxm_AllInOne_Model_Api_Json
     */
    public function getApi($service = null)
    {
        if (is_null($service)) {
            return Mage::helper('mxmallinone')->getApi($this->getWebsite());
        }
        return Mage::helper('mxmallinone')->getApi($this->getWebsite())->getInstance($service);
    }

    protected function createCsv($data)
    {
        $filename = $this->syncHelper->getTempFileName($this->syncType);
        $f = fopen($filename, 'w');

        foreach ($data as $idx => $item) {

            if ($idx === 0) {
                // Add headers
                $keys = array_keys($item);
                if (!is_null($this->fieldMap)) {
                    $keys = array();
                    foreach (array_keys($item) as $key) {
                        $keys[] = isset($this->fieldMap[$key]) ? $this->fieldMap[$key] : $key;
                    }
                }
                fputcsv($f, $keys);
            }

            fputcsv($f, $item);
        }

        fclose($f);

        return $filename;
    }

    protected function uploadCsv($filename)
    {
        /* @var $api Mxm_AllInOne_Model_Api_Json */
        $api = $this->getApi('file_upload');

        $result = $api->initialise();
        $key = $result['key'];
        $api->handle($key, "@$filename");

        return $key;
    }

    protected function importDatatable($data, $prune = false)
    {
        $filename = $this->createCsv($data);
        $key = $this->uploadCsv($filename);

        $this->getApi('datatable_import')->importUploadedFile(
            $this->importPath,
            $key,
            array(), // auto detect mapping
            array(
                'csv' => array(
                    'has_headers' => true,
                    'delimiter'   => ',',
                    'enclosure'   => '"'
                ),
                'afterImport' => array(
                    'prune' => (bool)$prune
                )
            )
        );

        unlink($filename);
    }

    protected function importList($data)
    {
        $filename = $this->createCsv($data);
        $key = $this->uploadCsv($filename);

        $this->getApi('list_import')->importUploadedFile(
            $this->importPath,
            $key,
            array(), // auto detect mapping
            array(
                'csv' => array(
                    'has_headers' => true,
                    'delimiter'   => ',',
                    'enclosure'   => '"'
                )
            ),
            'campaign'
        );

        unlink($filename);
    }

    protected abstract function doSync($ids = null);
}