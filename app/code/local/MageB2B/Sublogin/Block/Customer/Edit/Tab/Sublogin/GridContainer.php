<?php
/**
 * @category Customer Service Version 3.0
 * @package MageB2B_Sublogin
 * @author AIRBYTES GmbH <info@airbytes.de>
 * @copyright AIRBYTES GmbH
 * @license commercial
 * @date 09.01.2016
 */
class MageB2B_Sublogin_Block_Customer_Edit_Tab_Sublogin_GridContainer extends Mage_Adminhtml_Block_Widget_Grid_Container
    implements Varien_Data_Form_Element_Renderer_Interface
{
    public function __construct()
    {
        $this->_controller = 'customer_edit_tab_sublogin';
        $this->_blockGroup = 'sublogin';
        $this->_headerText = Mage::helper('sublogin')->__('Sublogins');
        $this->_addButtonLabel = Mage::helper('sublogin')->__('Add new Sublogin');

        parent::__construct();

        $this->_updateButton('add', 'onclick', '
          window.open(\''.$this->getUrl('adminhtml/sublogin_index/new', array('cid' => Mage::registry('current_customer')->getId())).'\', \'_blank\');
          window.focus();');
    }

    // FOLLOWING BELONGS TO Varien_Data_Form_Element_Renderer_Interface

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
     * Set form element instance
     *
     * @param Varien_Data_Form_Element_Abstract $element
     * @return MageB2B_Sublogin_Block_Grid
     */
    public function setElement(Varien_Data_Form_Element_Abstract $element)
    {
        $this->_element = $element;
        return $this;
    }

    /**
     * Retrieve form element instance
     */
    public function getElement()
    {
        return $this->_element;
    }

    public function setDisplay($data)
    {
        $this->setIdField($data['idfield']);
        $this->setDisplayFields($data['fields']);
        $this->setAddbutton($data['addbutton']);
        return $this;
    }
}
