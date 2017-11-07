<?php
class NC_LoginAsCustomer_Block_Adminhtml_Edit_Renderer_Website extends Mage_Adminhtml_Block_Widget_Grid_Column_Renderer_Abstract
{
    protected $_values;

    public function render(Varien_Object $row)
    {
        return '<a title="' . Mage::helper('core')->__('Edit Website') . '"
            href="' . $this->getUrl('*/NC_LoginAsCustomer/login', array('website_id' => $row->getWebsiteId(), 'customer_id'=>$this->getRequest()->getParam('customer_id'))) . '">'
            . $this->escapeHtml($row->getData($this->getColumn()->getIndex())) . '</a>';
    }
}
?>