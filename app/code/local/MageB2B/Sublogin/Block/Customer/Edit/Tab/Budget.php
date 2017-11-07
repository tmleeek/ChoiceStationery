<?php
/**
 * @category Customer Service Version 3.0
 * @package MageB2B_Sublogin
 * @author AIRBYTES GmbH <info@airbytes.de>
 * @copyright AIRBYTES GmbH
 * @license commercial
 * @date 09.01.2016
 */
class MageB2B_Sublogin_Block_Customer_Edit_Tab_Budget extends Mage_Adminhtml_Block_Widget_Form
{
    protected function _prepareForm()
    {
        $form = new Varien_Data_Form();
        $this->setForm($form);
        $customer = Mage::registry('current_customer');
        
        $fieldset = $form->addFieldset('budget', array('legend' => Mage::helper('sublogin')->__('Budgets')));
        $form->setHtmlIdPrefix('_budget');
        $form->setFieldNameSuffix('budget');
        
        $fieldset->addField('budgets', 'text', array(
                'name'=>'budgets',
        ));
        
        $_htmlName    = $form->getElement('budgets')->getName();
        $_htmlId      = $form->getElement('budgets')->getHtmlId();
$calendarHtml = <<<EOH
<div style="width:100px">
                <img style="margin-top:1px;float:right"
                    id="{$_htmlId}_row_{{index}}_expire_date_trig"
                    src="{$this->getSkinUrl('images/grid-cal.gif')}" />
                <input rel="{{index}}" class="input-text" type="text" value="{{day}}"
                    name="{$_htmlName}[{{index}}][day]" id="{$_htmlId}_row_{{index}}_day"
                    readonly="readonly"
                    style="width:70px"
                    />
</div>
EOH;

        $fields = array();
        
        $budgetTypes = Mage::helper('sublogin/budget')->getBudgetTypesArray();
        $budgetTypesKV = array();
        foreach ($budgetTypes as $budgetType)
        {
            $budgetTypesKV[$budgetType['value']] = $budgetType['label'];
        }
        $fields[] = array(
            'name'    => 'budget_type',
            'label'   => Mage::helper('sublogin')->__('Budget Type'),
            'required'=> true,
            'type'    => 'select',
            'style'   => 'width:100px',
            'cssclass'=> 'required-entry',
            'options' => $budgetTypesKV,
        );
        
        $years = Mage::helper('sublogin/budget')->getYearsArray();
        $yearsKV = array();
        foreach ($years as $year)
        {
            $yearsKV[$year['value']] = $year['label'];
        }
        $fields[] = array(
            'name'    => 'year',
            'label'   => Mage::helper('sublogin')->__('Year'),
            'required'=> true,
            'type'    => 'select',
            'style'   => 'width:100px',
            'cssclass'=> 'ymd_fields',
            'options' => $yearsKV,
        );
        
        $months = Mage::helper('sublogin/budget')->getMonthsArray();
        $monthsKV = array();

        foreach ($months as $month)
        {
            $monthsKV[$month['value']] = $month['label'];
        }
        $fields[] = array(
            'name'    => 'month',
            'label'   => Mage::helper('sublogin')->__('Month'),
            'required'=> true,
            'type'    => 'select',
            'style'   => 'width:100px',
            'cssclass'=> 'ymd_fields',
            'options' => $monthsKV,
        );

        $fields[] = array(
            'name'      => 'day',
            'label'     => Mage::helper('sublogin')->__('Day'),
            'type'      => 'date',
            'cssclass'  => '',
            'required'  => false,
            'style'     => 'width:100px',
        );
        
        $fields[] = array(
            'name'      => 'yearly',
            'label'     => Mage::helper('sublogin')->__('Yearly'),
            'required'  => false,
            'type'      => 'text',
            'style'     => '',
            'cssclass'  => '',
        );
		
        $fields[] = array(
            'name'      => 'monthly',
            'label'     => Mage::helper('sublogin')->__('Monthly'),
            'required'  => false,
            'type'      => 'text',
            'style'     => '',
            'cssclass'  => '',
        );
		
		$fields[] = array(
            'name'      => 'daily',
            'label'     => Mage::helper('sublogin')->__('Daily'),
            'required'  => false,
            'type'      => 'text',
            'style'     => '',
            'cssclass'  => '',
        );
		
		$fields[] = array(
            'name'      => 'per_order',
            'label'     => Mage::helper('sublogin')->__('Per Order'),
            'required'  => false,
            'type'      => 'text',
            'style'     => '',
            'cssclass'  => '',
        );
        
        $fields[] = array(
            'name'      => 'amount',
            'label'     => Mage::helper('sublogin')->__('Amount'),
            'required'  => true,
            'type'      => 'text',
            'style'     => '',
            'cssclass'  => '',
        );
        
        // set default values for fields
        foreach ($fields as $key => $field)
        {
            if (!isset($field['onlyNewRequired'])) {
                $field['onlyNewRequired'] = false;
            }
            if (!isset($field['onlyNewValue'])) {
                $field['onlyNewValue'] = false;
            }
            if (!isset($field['readonly'])) {
                $field['readonly'] = false;
            }
            $fields[$key] = $field;
        }
        
        $budgets = $customer->getResource()
              ->getAttribute('budgets')
              ->getFrontend()
              ->getValue($customer);
        
        if (!$budgets) {
            $customer->setBudgets(array());
        } else {
            $budgetsArr = json_decode($budgets, true);
            $budgets = array();
            foreach ($budgetsArr as $budgetSingle)
            {
                $budgetVarienObj = new Varien_Object();
                // manipulate budget data
                $budgetData = Mage::helper('sublogin/budget')->manipulateDataReverseBasedonBudgetType($budgetSingle);
                $budgetVarienObj->setData($budgetData);                
                $budgets[] = $budgetVarienObj;
            }
            $customer->setBudgets($budgets);
        }        
        
        $form->getElement('budgets')->setRenderer(
            Mage::getSingleton('core/layout')->createBlock('sublogin/budgetTableinput')
                ->addAfterJs('mageb2b/sublogin/form.js')
                ->setDisplay(
                    array(
                        'idfield'   =>  'budget_id',
                        'addbutton' =>  $this->__('Add'),
                        'fields'    =>  $fields,
                ))
        );
        
        $form->setValues($customer->getData());
        $this->setForm($form);
        return $this;
    }
}