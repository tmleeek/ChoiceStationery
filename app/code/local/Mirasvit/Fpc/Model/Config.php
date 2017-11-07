<?php
/**
 * Mirasvit
 *
 * This source file is subject to the Mirasvit Software License, which is available at http://mirasvit.com/license/.
 * Do not edit or add to this file if you wish to upgrade the to newer versions in the future.
 * If you wish to customize this module for your needs.
 * Please refer to http://www.magentocommerce.com for more information.
 *
 * @category  Mirasvit
 * @package   Full Page Cache
 * @version   1.0.5.3
 * @build     520
 * @copyright Copyright (C) 2016 Mirasvit (http://mirasvit.com/)
 */



class Mirasvit_Fpc_Model_Config extends Varien_Simplexml_Config
{
    const REQUEST_ID_PREFIX = 'FPC_REQUEST_';

    const CACHE_TAGS_LEVEL_FIRST   = 1;
    const CACHE_TAGS_LEVEL_SECOND  = 2;
    const CACHE_TAGS_LEVEL_MINIMAL = 3;
    const CACHE_TAGS_LEVEL_EMPTY   = 4;

    const ALLOW_HDD_FREE_SPACE = 150; // Mb

    const ALLOWED_PEERFORMANCE_SAVE_TIME = 0.1; // local save cache time - 0.0016s
    const ALLOWED_PEERFORMANCE_CLEAN_TIME = 0.1; //clean cache time - 0.019s

    const MAX_SESSION_SIZE = 2; //Mb

    protected $_containers = null;

    public function __construct($data = null)
    {
        parent::__construct($data);

        $cacheConfig = Mage::getConfig()->loadModulesConfiguration('cache.xml');
        // if (Mage::getSingleton('customer/session')->getId()) {
        //     $cacheLoggedConfig = Mage::getConfig()->loadModulesConfiguration('cachelogged.xml');
        //     $cacheConfig->extend($cacheLoggedConfig);
        // }
        $customConfig = Mage::getConfig()->loadModulesConfiguration('custom.xml');
        $cacheConfig->extend($customConfig);
        $this->setXml($cacheConfig->getNode());

        return $this;
    }

    public function getLifetime()
    {
        $lifetime = intval(Mage::getStoreConfig('fpc/general/lifetime'));
        if (!$lifetime) {
            $lifetime = 3600;
        }

        return $lifetime;
    }

    public static function isDebug()
    {
        $options = Mage::app()->getConfig()->getNode('global/cache')->asCanonicalArray();
        if (isset($options['debug']) && $options['debug'] == 1) {
            if (isset($options['ip'])
                && $options['ip'] != '*'
                && !in_array($_SERVER['REMOTE_ADDR'], explode(',', $options['ip']))) {
                return false;
            }

            return true;
        }

        return false;
    }

    public function getMaxCacheSize()
    {
        $size = intval(Mage::getStoreConfig('fpc/general/max_cache_size'));
        if (!$size) {
            $size = 1024;
        }

        $size *= 1024 * 1024;

        return $size;
    }

    public function getMaxCacheNumber()
    {
        $number = intval(Mage::getStoreConfig('fpc/general/max_cache_number'));
        if (!$number) {
            $number = 100000;
        }

        return $number;
    }

    public function getGzcompressLevel()
    {
        return Mage::getStoreConfig('fpc/general/gzcompress_level');
    }

    public function getCacheTagslevelLevel()
    {
        $cacheTagsLevel = Mage::getStoreConfig('fpc/general/cache_tags_level');
        if (!$cacheTagsLevel) {
            $cacheTagsLevel = 1;
        }

        return $cacheTagsLevel;
    }

    public function getCacheEnabled($storeId = null)
    {
        return Mage::getStoreConfig('fpc/general/enabled', $storeId);
    }

    public function getStatus()
    {
        return Mage::getStoreConfig('fpc/crawler/status');
    }

    public function getMaxDepth()
    {
        return Mage::getStoreConfig('fpc/cache_rules/max_depth');
    }

    public function getCacheableActions()
    {
       $key = 'fpc/cache_rules/cacheable_actions';

       return $this->_prepareValues($key);
    }

    public function getIgnoredPages()
    {
        $key = 'fpc/cache_rules/ignored_pages';

        return $this->_prepareValues($key);
    }

    public function getUserAgentSegmentation()
    {
        $key = 'fpc/cache_rules/user_agent_segmentation';

        return $this->_prepareValues($key);
    }

    public function getIgnoredUrlParams()
    {
        $key = 'fpc/cache_rules/ignored_url_params';

        return $this->_prepareValues($key);
    }

    protected function _prepareValues($key) {
        $result = array();

        if ($values = Mage::getStoreConfig($key)) {
            $values = unserialize($values);
            foreach ($values as $value) {
                if (count($value) == 1) {
                    $result[] = array_pop($value);
                } else {
                    $result[] = $value;
                }
            }
        }

        return $result;
    }

    public function getMobileDetect()
    {
         return Mage::getStoreConfig('fpc/cache_rules/mobile_detect');
    }

    public function getContainers()
    {
        if ($this->_containers === null) {
            $this->_containers = array();
            foreach ($this->getNode('containers')->children() as $container) {
                Mage::log($container, null, 'mir1.log', true);
                $containerName = (string) $container->name;
                $containerData = array(
                        'container' => (string) $container->container,
                        'block' => (string) $container->block,
                        'cache_lifetime' => (int) $container->cache_lifetime,
                        'name' => (string) $container->name,
                        'depends' => (string) $container->depends,
                        'in_register' =>  isset($container->in_register) ? (string) $container->in_register : false,
                        'in_session' =>  isset($container->in_session) ? ((trim($container->in_session) !== 'true') ? intval($container->in_session) : true) : false,
                        'in_app' => isset($container->in_app) ? intval($container->in_app) : intval($container->in_app) + 1,
                    );
                if (!empty($containerName)) {
                    $this->_containers[(string) $container->block][$containerName] = $containerData;
                } else {
                    $this->_containers[(string) $container->block] = $containerData;
                }
            }
        }

        return $this->_containers;
    }

    public function isDebugHintsEnabled($storeId = null)
    {
        if (!self::isDebugAllowed()) {
            return false;
        }

        return Mage::getStoreConfig('fpc/debug/hints', $storeId);
    }

    public function isDebugInfoEnabled($storeId = null)
    {
        if (!self::isDebugAllowed()) {
            return false;
        }

        return Mage::getStoreConfig('fpc/debug/info', $storeId);
    }

    public function isDebugLogEnabled($storeId = null)
    {
        if (!self::isDebugAllowed()) {
            return false;
        }

        return Mage::getStoreConfig('fpc/debug/log', $storeId);
    }

    public function isDebugAllowed($storeId = null)
    {
        if (Mage::app()->getRequest()->isXmlHttpRequest()) {
            return false;
        }

        if (strpos(Mage::helper('core/url')->getCurrentUrl(), 'api/soap') !== false) {
            return false;
        }

        $userAgent = Mage::helper('core/http')->getHttpUserAgent();
        if (preg_match('/testmirasvit/', $userAgent)) {
            return true;
        }

        $ips = Mage::getStoreConfig('fpc/debug/allowed_ip', $storeId);
        if ($ips == '') {
            return true;
        }

        if (!empty($_SERVER['HTTP_CLIENT_IP'])) {
            $clientIp = $_SERVER['HTTP_CLIENT_IP'];
        } else if (!empty($_SERVER['HTTP_X_FORWARDED_FOR'])) {
            $clientIp = $_SERVER['HTTP_X_FORWARDED_FOR'];
        } else {
            $clientIp = $_SERVER['REMOTE_ADDR'];
        }

        if (!$clientIp) {
            return false;
        }

        $ips = explode(',', $ips);
        $ips = array_map('trim',$ips);

        return in_array($clientIp, $ips);
    }
}
