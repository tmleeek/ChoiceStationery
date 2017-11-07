<?php

class Mxm_AllInOne_Block_Adminhtml_Report_Trx extends Mage_Adminhtml_Block_Widget_Container
{

    /**
     * Initialise template
     */
    protected function _construct()
    {
        parent::_construct();
        $this->_headerText = $this->__('Transactional Reporting');
        $this->setTemplate('mxmallinone/report/trx.phtml');
    }

    public function hasTransactional()
    {
        return Mage::helper('mxmallinone/transactional')->isEnabled(Mage::helper('mxmallinone/report')->getWebsiteId());
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
