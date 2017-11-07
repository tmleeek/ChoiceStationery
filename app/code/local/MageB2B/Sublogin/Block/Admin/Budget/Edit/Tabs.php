<?php
/**
 * @category Customer Service Version 3.0
 * @package MageB2B_Sublogin
 * @author AIRBYTES GmbH <info@airbytes.de>
 * @copyright AIRBYTES GmbH
 * @license commercial
 * @date 09.01.2016
 */
class MageB2B_Sublogin_Block_Admin_Budget_Edit_Tabs extends Mage_Adminhtml_Block_Widget_Tabs
{

    public function __construct()
    {
        parent::__construct();
        $this->setId('sublogin_budget_tabs');
        $this->setDestElementId('edit_form');
        $this->setTitle(Mage::helper('sublogin')->__('Add budget'));
    }

    protected function _beforeToHtml()
    {
        $model = Mage::registry('budget_data');
        $this->addTab('form_section', array(
          'label'     => Mage::helper('sublogin')->__('Budget'),
          'title'     => Mage::helper('sublogin')->__('Budget'),
          'content'   => $this->getLayout()->createBlock('sublogin/admin_budget_edit_tab_form')->toHtml(),
        ));
        return parent::_beforeToHtml();
    }
}
