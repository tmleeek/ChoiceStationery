<?php

class Mxm_AllInOne_Helper_Roi extends Mage_Core_Helper_Abstract
{
    /**
     * Configuration variable paths
     */
    const CFG_ENABLED        = 'mxm_allinone_roi/roi/enabled';

    protected $completeScript = null;

    public function isEnabled()
    {
        return !!Mage::getStoreConfig(self::CFG_ENABLED);
    }

    public function getServerUrl()
    {
        return '//' . Mage::getStoreConfig(Mxm_AllInOne_Helper_Data::CFG_API_SERVER_URL);
    }

    public function addScripts(&$scripts)
    {
        if (!$this->isEnabled()) {
            return;
        }
        $serverUrl = $this->getServerUrl();
        $scripts[] = <<<SCRIPT
<script type="text/javascript" src="{$serverUrl}/scripts/tracker.js"></script>
SCRIPT;
        if (!is_null($this->completeScript)) {
            $scripts[] = $this->completeScript;
        }
    }

    public function setComplete($revenue)
    {
        $revenue = $revenue * 100;
        $this->completeScript = <<<SCRIPT
<script type="text/javascript">
    Mxm.Tracker.setAutoTrack(false);
    Mxm.Tracker.setRevenue($revenue).complete();
</script>
SCRIPT;
    }
}