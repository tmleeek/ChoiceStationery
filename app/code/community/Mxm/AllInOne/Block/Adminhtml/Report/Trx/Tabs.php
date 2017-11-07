<?php
class Mxm_AllInOne_Block_Adminhtml_Report_Trx_Tabs extends Mage_Adminhtml_Block_Widget_Tabs
{
    public function __construct()
    {
        parent::__construct();
        $this->setId('report_tabs');
        $this->setDestElementId('report_tab_content');
        $this->setTemplate('widget/tabshoriz.phtml');
    }

    protected function _prepareLayout()
    {
        // load this active tab statically
        $this->addTab('summary', array(
            'label'     => $this->__('Summary'),
            'content'   => Mage::helper('mxmallinone/report')
                ->getIframeHtml(Mxm_AllInOne_Helper_Report::TYPE_TRX, 'summary'),
            'active'    => true
        ));

        // load other tabs with ajax
        $this->addTab('activity', array(
            'label'     => $this->__('Activity'),
            'url'       => $this->getUrl(
                'mxmallinone/report/iframe',
                array(
                    '_current'    => true,
                    'report-type' => Mxm_AllInOne_Helper_Report::TYPE_TRX,
                    'report'      => 'activity'
                )
            ),
            'class'     => 'ajax'
        ));

        $this->addTab('click-throughs', array(
            'label'     => $this->__('Click Throughs'),
            'url'       => $this->getUrl(
                'mxmallinone/report/iframe',
                array(
                    '_current'    => true,
                    'report-type' => Mxm_AllInOne_Helper_Report::TYPE_TRX,
                    'report'      => 'click-throughs'
                )
            ),
            'class'     => 'ajax'
        ));

        return parent::_prepareLayout();
    }
}
