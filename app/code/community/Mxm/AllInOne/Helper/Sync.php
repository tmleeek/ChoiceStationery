<?php

class Mxm_AllInOne_Helper_Sync extends Mage_Core_Helper_Abstract
{
    const CFG_ENABLED      = 'mxm_allinone_sync/general/enabled';
    const CFG_INTERVAL     = 'mxm_allinone_sync/%s/interval';

    const SYNC_TYPE_PRODUCT          = 1;
    const SYNC_TYPE_SUBSCRIBER       = 2;
    const SYNC_TYPE_PROMOTION        = 3;
    const SYNC_TYPE_ORDER            = 4;
    const SYNC_TYPE_ORDER_ITEM       = 5;
    const SYNC_TYPE_STORE            = 6;
    const SYNC_TYPE_CATEGORY         = 7;
    const SYNC_TYPE_CATEGORY_PRODUCT = 8;
    const SYNC_TYPE_PRODUCT_SALES    = 9;

    const FLAG_SYNC    = 'mxm_allinone_sync';

    const SYNC_LAST_TS = 'last_sync_ts';
    const SYNC_FORCE   = 'force_sync';

    protected $syncTypeMap = array(
        self::SYNC_TYPE_PRODUCT          => 'product',
        self::SYNC_TYPE_SUBSCRIBER       => 'subscriber',
        self::SYNC_TYPE_PROMOTION        => 'promotion',
        self::SYNC_TYPE_ORDER            => 'order',
        self::SYNC_TYPE_ORDER_ITEM       => 'orderitem',
        self::SYNC_TYPE_STORE            => 'store',
        self::SYNC_TYPE_CATEGORY         => 'category',
        self::SYNC_TYPE_CATEGORY_PRODUCT => 'categoryproduct',
        self::SYNC_TYPE_PRODUCT_SALES    => 'productsales',
    );

    protected $scheduledTypes = array(
        self::SYNC_TYPE_PRODUCT,
        self::SYNC_TYPE_SUBSCRIBER,
        self::SYNC_TYPE_PROMOTION,
        self::SYNC_TYPE_STORE,
        self::SYNC_TYPE_CATEGORY,
        self::SYNC_TYPE_CATEGORY_PRODUCT,
        self::SYNC_TYPE_PRODUCT_SALES,
    );

    protected $uploadDir = array('mxm', 'uploads');

    protected $uploadDirStr = null;

    /**
     *
     * @var Mage_Core_Model_Flag
     */
    protected $syncFlag = null;

    /**
     *
     * @var array
     */
    protected $syncData = null;

    /**
     * Return true if data syncing is enabled in this extension
     *
     * @param mixed $websiteId
     * @return boolean
     */
    public function isEnabled($websiteId = null)
    {
        if (is_null($websiteId)) {
            return Mage::getStoreConfigFlag(self::CFG_ENABLED);
        }
        return !!Mage::app()->getWebsite($websiteId)->getConfig(self::CFG_ENABLED);
    }

    public function syncName($type)
    {
        return $this->syncTypeMap[$type];
    }

    public function syncType($name)
    {
        $map = array_flip($this->syncTypeMap);
        return isset($map[$name]) ? $map[$name] : null;
    }

    /**
     * Runs all of the sync types which should be run on a schedule
     * @see Mxm_AllInOne_Helper_Sync::$scheduledTypes
     */
    public function runScheduled()
    {
        foreach ($this->scheduledTypes as $type) {
            try {
                Mage::getSingleton("mxmallinone/sync_{$this->syncName($type)}")->run();
            } catch (Exception $e) {
                Mage::logException("Sync failed for {$this->syncName($type)}", null, $e);
                // continue to next type
            }
        }
    }

    /**
     * Check if the sync type can sync now
     *
     * @param string $type
     * @param mixed $websiteId
     * @return boolean
     */
    public function canSync($type, $websiteId = null)
    {
        if (!Mage::helper('mxmallinone')->canUseApi($websiteId) || !$this->isEnabled($websiteId)) {
            return false;
        }

        // Force the sync using the 'Sync now' button
        if ($this->getForceSync($type)) {
            return true;
        }

        $lastSync = $this->getLastSyncTs($type, $websiteId);
        $nowTs    = Varien_Date::toTimestamp(true);
        $lastTs   = Varien_Date::toTimestamp($lastSync);
        $interval = $this->getInterval($type);
        // Run every $interval seconds
        return ($nowTs >= ($lastTs + $interval));
    }

    /**
     * Get the last sync timestamp for the sync type
     *
     * @param string $type
     * @param mixed $websiteId
     * @return string
     */
    public function getLastSyncTs($type, $websiteId = null)
    {
        $websiteId = (is_null($websiteId)) ? '0' :
            Mage::app()->getWebsite($websiteId)->getId();
        return $this->getSyncData($type, self::SYNC_LAST_TS . "/$websiteId");
    }

    /**
     * Set the last sync timestamp for the sync type
     *
     * @param string $type
     * @param string $updateTs
     * @param mixed $websiteId
     * @return \Mxm_AllInOne_Helper_Sync
     */
    public function setLastSyncTs($type, $updateTs, $websiteId = null)
    {
        $websiteId = (is_null($websiteId)) ? '0' :
            Mage::app()->getWebsite($websiteId)->getId();
        $this->setSyncData($type, self::SYNC_LAST_TS . "/$websiteId", $updateTs);
        return $this;
    }

    /**
     * Return true if the sync type should be forced to run on the next schedule
     *
     * @param string $type
     * @return boolean
     */
    public function getForceSync($type)
    {
        return $this->getSyncData($type, self::SYNC_FORCE);
    }

    /**
     * Set whether or not the sync type should be forced to run on the next schedule
     *
     * @param string $type
     * @param boolean $value
     * @return \Mxm_AllInOne_Helper_Sync
     */
    public function setForceSync($type, $value)
    {
        $this->setSyncData($type, self::SYNC_FORCE, !!$value);
        return $this;
    }

    /**
     * Set all scheduled sync types to run on the next schedule
     *
     * @return $this
     */
    public function forceSyncAll()
    {
        foreach ($this->scheduledTypes as $type) {
            $this->setForceSync($type, true);
        }
        return $this;
    }

    /**
     * Get the interval time in seconds for the sync type
     *
     * @param string $type
     * @return int
     */
    public function getInterval($type)
    {
        $path = sprintf(self::CFG_INTERVAL, $this->syncTypeMap[$type]);
        return Mage::getStoreConfig($path);
    }

    /**
     * Get a temporary file name for the upload csv file
     *
     * @param string $type
     * @return string
     */
    public function getTempFileName($type)
    {
        $dir    = $this->getUploadDir();
        $prefix = $this->syncTypeMap[$type];
        return $dir . DS . uniqid($prefix) . '-' . date('Y-m-d') . '.csv';
    }

    /**
     * Get the directory for upload files, create if it does not exist
     *
     * @return string
     */
    protected function getUploadDir()
    {
        if (is_null($this->uploadDirStr)) {
            $this->uploadDirStr =  Mage::getBaseDir('var');
            foreach ($this->uploadDir as $dir) {
                $this->uploadDirStr .= DS . $dir;
                if (!is_dir($this->uploadDirStr)) {
                    if (!mkdir($this->uploadDirStr)) {
                        throw new Exception("Unable to create uploads directory: {$this->uploadDirStr}");
                    }
                    if (!chmod($this->uploadDirStr, 0777)) {
                        throw new Exception("Unable to set permissions on uploads directory: {$this->uploadDirStr}");
                    }
                }
            }
        }
        return $this->uploadDirStr;
    }

    /**
     * Get sync data from the flag
     *
     * @param int $type
     * @param string $key
     * @return mixed
     */
    public function getSyncData($type, $key = null)
    {
        if (is_null($this->syncData)) {
            $this->syncData = $this->getSyncFlag()->getFlagData();
        }
        if (is_null($key)) {
            return isset($this->syncData[$type]) ? $this->syncData[$type] : array();
        }
        return (isset($this->syncData[$type][$key]) ? $this->syncData[$type][$key] : null);
    }

    /**
     * Set sync data on the flag
     *
     * @param int $type
     * @param string $key
     * @param mixed $value
     */
    public function setSyncData($type, $key, $value)
    {
        $flag = $this->getSyncFlag();
        if (is_null($this->syncData)) {
            $this->syncData = $flag->getFlagData();
        }
        if (!isset($this->syncData[$type])) {
            $this->syncData[$type] = array();
        }
        $this->syncData[$type][$key] = is_null($value) ? null : $value;
        $flag->setFlagData($this->syncData)
            ->save();
    }

    /**
     * Get the flag which holds the sync data
     *
     * @return Mage_Core_Model_Flag
     */
    public function getSyncFlag()
    {
        if (is_null($this->syncFlag)) {
             $this->syncFlag =  Mage::getModel('core/flag', array(
                    'flag_code' => self::FLAG_SYNC,
                 ))
                 ->loadSelf();
        }
        return $this->syncFlag;
    }
}