<?php
/**
 * @category Customer Service Version 3.0
 * @package MageB2B_Sublogin
 * @author AIRBYTES GmbH <info@airbytes.de>
 * @copyright AIRBYTES GmbH
 * @license commercial
 * @date 09.01.2016
 */
class MageB2B_Sublogin_Block_Customer_Edit_Tab_Sublogin extends Mage_Adminhtml_Block_Widget_Form
{

    protected $_addressCollection = null;

    protected function _prepareForm()
    {
        $form = new Varien_Data_Form();
        $this->setForm($form);
        $customer = Mage::registry('current_customer');

        $fieldsetconfiguration = $form->addFieldset('subloginconfiguration', array('legend'=>Mage::helper('sublogin')->__('Sublogin Configuration')));
        $form->setHtmlIdPrefix('_subloginconfiguration');
        $form->setFieldNameSuffix('subloginconfiguration');

        $fieldsetconfiguration->addField('can_create_sublogins', 'select', array(
            'label'     => Mage::helper('sublogin')->__('Can create sublogins'),
            'after_element_html' => '<br />' . Mage::helper('sublogin')->__('If allowed, customer can create sublogins in frontend area'),
            'name'      => 'can_create_sublogins',
            'values'    => Mage::getModel('adminhtml/system_config_source_yesno')->toOptionArray(),
        ));

        $fieldsetconfiguration->addField('max_number_sublogins', 'text', array(
            'label'     => Mage::helper('sublogin')->__('Max. number of sublogins'),
            'after_element_html' => Mage::helper('sublogin')->__('Configure the max. amount of sublogins the customer can create. 0 is unlimited.'),
            'name'      => 'max_number_sublogins',
        ));
		
        $fieldsetconfiguration->addField('sublogin_optional_email', 'text', array(
            'label'     => Mage::helper('sublogin')->__('Optional Email'),
            'after_element_html' => '<br />' . Mage::helper('sublogin')->__('If specified, then email alerts of this customer will be sent to the specified email address.'),
            'name'      => 'sublogin_optional_email',
        ));

        $fieldset = $form->addFieldset('sublogin', array('legend' => Mage::helper('sublogin')->__('Sublogins')));
        $form->setHtmlIdPrefix('_sublogin');
        $form->setFieldNameSuffix('sublogin');

        $fieldset->addField('sublogins', 'text', array(
                'name'=>'sublogins',
        ));

        if (Mage::getStoreConfig('sublogin/general/edit_in_grid', $customer->getStoreId()))
        {
        // days_to_expire has two functions: one is the days_to_expire display
        // the other is the calendar
        $_htmlName    = $form->getElement('sublogins')->getName();
        $_htmlId      = $form->getElement('sublogins')->getHtmlId();
$calendarHtml = <<<EOH
<div style="width:100px">
                <img style="margin-top:1px;float:right"
                    id="{$_htmlId}_row_{{index}}_expire_date_trig"
                    src="{$this->getSkinUrl('images/grid-cal.gif')}" />
                <input rel="{{index}}" class="input-text" type="text" value="{{expire_date}}"
                    name="{$_htmlName}[{{index}}][expire_date]" id="{$_htmlId}_row_{{index}}_expire_date"
                    readonly="readonly"
                    style="width:70px"
                    />
</div>
EOH;
        $fields = Mage::Helper('sublogin')->getGridFields($customer, $calendarHtml);
            $collection = Mage::getModel('sublogin/sublogin')->getCollection()
                ->addFieldToFilter('entity_id', $customer->getId())
                ->addOrder('id', 'ASC');

            $customer->setSublogins($collection->getItems());

            $form->getElement('sublogins')->setRenderer(
                Mage::getSingleton('core/layout')->createBlock('sublogin/tableinput')
                    ->addAfterJs('mageb2b/sublogin/form.js')
                    ->setDisplay(
                        array(
                            'idfield'   =>  'id',
                            'addbutton' =>  $this->__('Add'),
                            'fields'    =>  $fields,
                    ))
            );
        }
        else
        {
            $form->getElement('sublogins')->setRenderer(
                Mage::getSingleton('core/layout')->createBlock('sublogin/customer_edit_tab_sublogin_gridContainer')
            );
        }

        if (!$customer->getId()) {
            $customer->setData('can_create_sublogins', 1);
        }
        $form->setValues($customer->getData());
        $this->setForm($form);
        return $this;
    }
}

