<?php

class MDN_quotation_Block_Frontend_AnonymousQuoteRequest extends Mage_Core_Block_Template {

    protected $_countryCollection;

    /**
     * Return country combo box
     */
    public function getCountryHtmlSelect($type = 'billing') {
        $countryId = Mage::getStoreConfig('general/country/default');
        $select = $this->getLayout()->createBlock('core/html_select')
                        ->setName($type . '[country_id]')
                        ->setId($type . ':country_id')
                        ->setTitle(Mage::helper('checkout')->__('Country'))
                        ->setClass('validate-select')
                        ->setValue($countryId)
                        ->setOptions($this->getCountryOptions());

        return $select->getHtml();
    }

    /**
     * return countries
     */
    public function getCountryOptions() {
        $options = false;
        $useCache = Mage::app()->useCache('config');
        if ($useCache) {
            $cacheId = 'DIRECTORY_COUNTRY_SELECT_STORE_' . Mage::app()->getStore()->getCode();
            $cacheTags = array('config');
            if ($optionsCache = Mage::app()->loadCache($cacheId)) {
                $options = unserialize($optionsCache);
            }
        }

        if ($options == false) {
            $options = $this->getCountryCollection()->toOptionArray();
            if ($useCache) {
                Mage::app()->saveCache(serialize($options), $cacheId, $cacheTags);
            }
        }
        return $options;
    }

    /**
     * Get country collection
     */
    public function getCountryCollection() {
        if (!$this->_countryCollection) {
            $this->_countryCollection = Mage::getSingleton('directory/country')->getResourceCollection()
                            ->loadByStore();
        }
        return $this->_countryCollection;
    }

    /**
     * Return submit url for anonymous request
     */
    public function getSubmitUrl() {
        return $this->getUrl('Quotation/Quote/submitAnonymousRequest');
    }

}