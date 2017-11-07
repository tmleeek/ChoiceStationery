<?php

class Mxm_AllInOne_Block_Adminhtml_Report_Sca extends Mage_Adminhtml_Block_Widget_Container
{

    /**
     * Initialise template
     */
    protected function _construct()
    {
        parent::_construct();
        $this->_headerText = $this->__('SCA Reporting');
        $this->setTemplate('mxmallinone/report/sca.phtml');
    }

    public function hasBasketType()
    {
        $store = Mage::app()->getStore(Mage::helper('mxmallinone/report')->getStoreId());
        return !is_null($store->getConfig(Mxm_AllInOne_Helper_Sca::CFG_BASKET_TYPE_ID));
    }

    public function getDatePickerUrl()
    {
        return $this->getUrl('*/*/*', array('_current' => true, 'date_from' => null, 'date_to' => null));
    }

    public function getDateStr($type)
    {
        return Mage::helper('mxmallinone/report')->getDateStr($type);
    }
}
