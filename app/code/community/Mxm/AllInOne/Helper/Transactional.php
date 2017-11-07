<?php
class Mxm_AllInOne_Helper_Transactional extends Mage_Core_Helper_Abstract
{
    /**
     * Configuration variable paths
     */
    const CFG_API_USERNAME    = 'mxm_allinone_api/api/username';
    const CFG_API_PASSWORD    = 'mxm_allinone_api/api/password';

    const CFG_ENABLED         = 'mxm_allinone_transactional/transactional/enabled';
    const CFG_AUTH_TYPE       = 'mxm_allinone_transactional/transactional/auth_type';
    const CFG_SSL_TYPE        = 'mxm_allinone_transactional/transactional/ssl_type';
    const CFG_WYSIWYG_ENABLED = 'mxm_allinone_transactional/wysiwyg/enabled';

    public function isEnabled($websiteId = null)
    {
        $website = Mage::app()->getWebsite($websiteId);
        return !!$website->getConfig(self::CFG_ENABLED);
    }

    public function wysiwygEnabled()
    {
        return !!Mage::getStoreConfigFlag(self::CFG_WYSIWYG_ENABLED);
    }

    public function getServerUrl()
    {
        return Mage::getStoreConfig(Mxm_AllInOne_Helper_Data::CFG_API_SERVER_URL);
    }

    public function getPort($sslType)
    {
        $isDevel = Mage::helper('mxmallinone')->isDevel();
        switch (strtolower($sslType)) {
            case 'ssl':
                return $isDevel ? 5465 : 465;
            case 'none':
                return $isDevel ? 5525 : 25;
            default:
                return $isDevel ? 5587 : 587;
        }
    }

    public function getTransport()
    {
        $host     = $this->getServerUrl();
        $username = Mage::getStoreConfig(self::CFG_API_USERNAME);
        $password = Mage::getStoreConfig(self::CFG_API_PASSWORD);
        $auth     = Mage::getStoreConfig(self::CFG_AUTH_TYPE); // LOGIN, PLAIN, CRAM-MD5
        $ssl      = Mage::getStoreConfig(self::CFG_SSL_TYPE);  // SSL, TLS, NONE

        $config = array(
            'username' => $username,
            'password' => $password,
            'auth'     => $auth,
            'port'     => $this->getPort($ssl)
        );

        if ($ssl !== 'none') {
            $config['ssl'] = ($ssl === 'ssl' ? 'ssl' : 'tls');
        }

        return new Zend_Mail_Transport_Smtp($host, $config);
    }
}