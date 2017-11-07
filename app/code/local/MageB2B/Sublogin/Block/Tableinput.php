<?php
/**
 * @category Customer Service Version 3.0
 * @package MageB2B_Sublogin
 * @author AIRBYTES GmbH <info@airbytes.de>
 * @copyright AIRBYTES GmbH
 * @license commercial
 * @date 09.01.2016
 */
class MageB2B_Sublogin_Block_Tableinput extends Mage_Adminhtml_Block_Widget_Form
    implements Varien_Data_Form_Element_Renderer_Interface
{
    protected $javascripts = array('before'=>array(), 'after'=>array());

    public function setDisplay($data)
    {
        $this->setIdField($data['idfield']);
        $this->setDisplayFields($data['fields']);
        $this->setAddbutton($data['addbutton']);
        return $this;
    }

    public function __construct()
    {
        parent::__construct();
        if (Mage::getStoreConfig('sublogin/general/edit_in_grid')) {
            $this->setTemplate('sublogin/tableinput.phtml');
        }
    }

    public function render(Varien_Data_Form_Element_Abstract $element)
    {
        $this->setElement($element);
        return $this->toHtml();
    }

    public function getValues()
    {
        return $this->getElement()->getValue();
    }

    public function addBeforeJs($script)
    {
        $this->javascripts['before'][] = $script;
        return $this;
    }

    public function addAfterJs($script)
    {
        $this->javascripts['after'][] = $script;
        return $this;
    }

    public function getBeforeJs()
    {
        return $this->javascripts['before'];
    }

    public function getAfterJs()
    {
        return $this->javascripts['after'];
    }
}
