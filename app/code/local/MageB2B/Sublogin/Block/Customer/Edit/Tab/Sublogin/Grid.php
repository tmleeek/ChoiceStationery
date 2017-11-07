<?php
/**
 * @category Customer Service Version 3.0
 * @package MageB2B_Sublogin
 * @author AIRBYTES GmbH <info@airbytes.de>
 * @copyright AIRBYTES GmbH
 * @license commercial
 * @date 09.01.2016
 */
class MageB2B_Sublogin_Block_Customer_Edit_Tab_Sublogin_Grid extends Mage_Adminhtml_Block_Widget_Grid
    implements Varien_Data_Form_Element_Renderer_Interface
{
    /**
     * Render HTML
     *
     * @param Varien_Data_Form_Element_Abstract $element
     * @return string
     */
    public function render(Varien_Data_Form_Element_Abstract $element)
    {  
        $this->setElement($element);
        return '</table>'.$this->toHtml(); // close the opening table from the fieldset - normally all Element render render inside a table
    }  

    /**
     * Set grid params
     */
    public function __construct()
    {
        parent::__construct();
        $this->setId('sublogin_grid');
        $this->setUseAjax(true);
    }

    /**
     * Rerieve grid URL
     * @return string
     */
    public function getGridUrl()
    {
        return $this->_getData('grid_url') ?
            $this->_getData('grid_url') :
            $this->getUrl('adminhtml/sublogin_index/grid', array(
                '_current' => true,
                'id' => Mage::registry('current_customer')->getId()
            ));
    }

    /**
     * Prepare collection
     * @return Mage_Adminhtml_Block_Widget_Grid
     */
    protected function _prepareCollection()
    {
        $customer = Mage::registry('current_customer');
        $collection = Mage::getModel('sublogin/sublogin')->getCollection()
            ->addFieldToFilter('entity_id', $customer->getId())
            ->addOrder('id', 'ASC');
        $this->setCollection($collection);
        return parent::_prepareCollection();
    }

    /**
     * Add columns to grid
     *
     * @return Mage_Adminhtml_Block_Widget_Grid
     */
    protected function _prepareColumns()
    {
        $customer = Mage::registry('current_customer');
        $fields = Mage::Helper('sublogin')->getGridFields($customer);
        foreach ($fields as $field)
        {
            // don't diplay those fields
            if (in_array($field['name'], array('address_ids', 'password', 'days_to_expire'))) {
                continue;
            }

            $colData = array(
                'header'    => $field['label'],
                'sortable'  => true,
                'width'     => 60,
                'index'     => $field['name']
            );
            if ($field['type'] == 'select')
            {
                $colData['type'] = 'options';
                $colData['options'] = $field['options'];
            }
            if ($field['type'] == 'checkbox')
            {
                $colData['type'] = 'options';
                $colData['options'] = array(
                    0 => Mage::helper('sublogin')->__('No'),
                    1 => Mage::helper('sublogin')->__('Yes'),
                );
            }
            $this->addColumn($field['name'], $colData);

        }

        $this->addColumn('action', array(
            'header'    => Mage::helper('sublogin')->__('Action'),
            'sortable'  => true,
            'width'     => 60,
            'filter'    => false,
            'sortable'  => false,
            'renderer'  => 'sublogin/customer_edit_tab_sublogin_editRenderer',
        ));
    }
}

