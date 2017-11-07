<?php

class Mxm_AllInOne_Helper_Report extends Mage_Core_Helper_Abstract
{
    const REPORT_CONTEXT = 'magento';
    const TYPE_SCA       = 'sca';
    const TYPE_TRX       = 'trx';

    protected $reportTypes = array(
        self::TYPE_SCA => 'basket-report',
        self::TYPE_TRX => 'transactional-report'
    );

    protected $curStoreId   = null;
    protected $curWebsiteId = null;

    public function getIframeHtml($reportType, $reportName)
    {
        $frameUrl = $this->getFrameUrl($reportType, $reportName);
        return <<<HTML
<iframe id="reportIframe" src="$frameUrl" style="width:100%;height:500px;border:none;" width="100%" height="500px">
</iframe>
HTML;
    }

    public function getFrameUrl($reportType, $reportName)
    {
        $url              = Mage::helper('mxmallinone')->getServerUrl();
        $reportController = $this->reportTypes[$reportType];
        $queryData        = $this->getQueryData($reportType, $reportName);

        $url = "{$url}/{$reportController}/{$reportName}?" . http_build_query($queryData);
        return $url;
    }

    public function getStoreId()
    {
        if (is_null($this->curStoreId)) {
            $storeId = $this->_getRequest()->getParam('store', null);
            if (is_null($storeId)) {
                $store = Mage::app()->getDefaultStoreView();
                if (!is_null($store)) {
                    $storeId = $store->getId();
                }
            }
            $this->curStoreId = $storeId;
        }
        return $this->curStoreId;
    }

    public function getWebsiteId()
    {
        if (is_null($this->curWebsiteId)) {
            $websiteId = $this->_getRequest()->getParam('website', null);
            if (is_null($websiteId)) {
                $storeId = $this->getStoreId();
                if (!is_null($storeId)) {
                    $websiteId = Mage::app()->getStore($storeId)->getWebsiteId();
                } else {
                    foreach (Mage::app()->getWebsites() as $website) {
                        if ($website->getIsDefault()) {
                            $websiteId = $website->getId();
                        }
                    }
                }
            }
            $this->curWebsiteId = $websiteId;
        }
        return $this->curWebsiteId;
    }

    public function getDateStr($type)
    {
        $date = $this->_getRequest()->getParam("date_$type");
        if ($date) {
            return base64_decode($date);
        }
        switch ($type) {
            case 'from':
                return date('Y/m/d', strtotime('-14 day'));
            case 'to':
                return date('Y/m/d');
        }
    }

    public function getQueryData($reportType, $reportName)
    {
        $website = Mage::app()->getWebsite($this->getWebsiteId());
        $customerId = $website->getConfig(Mxm_AllInOne_Helper_Data::CFG_CUSTOMER_ID);
        $queryData = array(
            'cid'  => $customerId,
            'ctxt' => self::REPORT_CONTEXT
        );
        $queryData['start'] = $this->getDateStr('from');
        $queryData['end'] = $this->getDateStr('to');

        if ($reportType === self::TYPE_SCA) {
            $this->addQueryDataSca($queryData, $reportName);
        } elseif ($reportType === self::TYPE_TRX) {
            $this->addQueryDataTrx($queryData, $reportName);
        }

        return $queryData;
    }

    protected function addQueryDataSca(&$queryData, $reportName)
    {
        $store = Mage::app()->getStore($this->getStoreId());
        $basketTypeId   = $store->getConfig(Mxm_AllInOne_Helper_Sca::CFG_BASKET_TYPE_ID);
        $basketTypeSalt = $store->getConfig(Mxm_AllInOne_Helper_Sca::CFG_BASKET_TYPE_SALT);
        $customerId     = $queryData['cid'];
        $time = time();
        $token          = md5("$customerId-$time-$basketTypeSalt");
        $queryData = $queryData + array(
            'btid'    => $basketTypeId,
            'token'   => $token,
            'time'    => $time,
        );
    }

    protected function addQueryDataTrx(&$queryData, $reportName)
    {
        $website = Mage::app()->getWebsite($this->getWebsiteId());
        $username = $website->getConfig(Mxm_AllInOne_Helper_Data::CFG_API_USERNAME);
        $password = $website->getConfig(Mxm_AllInOne_Helper_Data::CFG_API_PASSWORD);
        $token = Mage::helper('mxmallinone/tokenauth')->createNew($username, $password);
        $queryData = $queryData + array(
            'tags'  => 'magento',
            'token' => base64_encode($token)
        );
        if ($reportName === 'click-throughs') {
            $queryData['offset'] = 0;
            $queryData['limit'] = 50;
        }
    }
}
