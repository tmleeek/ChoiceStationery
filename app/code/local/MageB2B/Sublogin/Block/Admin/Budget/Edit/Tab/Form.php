<?php
/**
 * @category Customer Service Version 3.0
 * @package MageB2B_Sublogin
 * @author AIRBYTES GmbH <info@airbytes.de>
 * @copyright AIRBYTES GmbH
 * @license commercial
 * @date 09.01.2016
 */
class MageB2B_Sublogin_Block_Admin_Budget_Edit_Tab_Form extends Mage_Adminhtml_Block_Widget_Form
{
    protected function _prepareForm()
    {
        $form = new Varien_Data_Form();
        $this->setForm($form);
        $fieldset = $form->addFieldset('budget_form', array('legend'=>Mage::helper('sublogin')->__('Budget')));
        
        $budgetData = Mage::registry('budget_data');
		
		if ($budgetData->getId())
		{
			$fieldset->addField('budget_id', 'hidden', array(
				'label'     => Mage::helper('sublogin')->__('ID'),
				'class'     => '',
				'name'      => 'budget_id',
			));
		}

        $fieldset->addField('sublogin_id', 'text', array(
            'label'     => Mage::helper('sublogin')->__('Sublogin'),
            'class'     => 'required-entry',
            'required'  => true,
            'name'      => 'sublogin_id',
		));
		
		$autocompleteRenderer = Mage::getSingleton('core/layout')->createBlock('sublogin/admin_autocompleter');
		$autocompleteRenderer->setAutocompleteData($autocompleteRenderer->getSublogins());
		$form->getElement('sublogin_id')->setRenderer($autocompleteRenderer);

		$budgetData->getBudgetType();

		$fieldset->addField('budget_type', 'select', array(
			'label'     => Mage::helper('sublogin')->__('Budget Type'),
			'class'     => 'required-entry',
			'required'  => true,
			'name'      => 'budget_type',
			'values'    => Mage::helper('sublogin/budget')->getBudgetTypesArray(),
		));

		
		$fieldset->addField('year', 'select', array(
            'label'     => Mage::helper('sublogin')->__('Year'),
            'class'     => 'ymd_fields',
            'required'  => true,
            'name'      => 'year',
            'values'  	=> Mage::helper('sublogin/budget')->getYearsArray(),
            'container_id'   => 'year_container',
		));

		$fieldset->addField('yearly', 'text', array(
			'label'     => Mage::helper('sublogin')->__('Yearly'),
			'class'     => 'ymd_fields',
			'required'  => true,
			'name'      => 'yearly',
			'container_id'   => 'yearly_container',
		));
		
		if ($budgetData->getBudgetType() == MageB2B_Sublogin_Model_Budget::TYPE_MONTH) {
			$budgetData->setMonth($budgetData->getYear().'-'.$budgetData->getMonth());
		}
		$fieldset->addField('month', 'select', array(
            'label'     => Mage::helper('sublogin')->__('Month'),
            'class'     => 'ymd_fields',
            'required'  => true,
            'name'      => 'month',
            'values'   	=> Mage::helper('sublogin/budget')->getMonthsArray(),
            'container_id'   => 'month_container',
		));

		$fieldset->addField('monthly', 'text', array(
			'label'     => Mage::helper('sublogin')->__('Monthly'),
			'class'     => 'ymd_fields',
			'required'  => true,
			'name'      => 'monthly',
			'container_id'   => 'monthly_container',
		));
		
		if ($budgetData->getBudgetType() == MageB2B_Sublogin_Model_Budget::TYPE_DAY) {
			$budgetData->setDay($budgetData->getYear().'-'.$budgetData->getMonth().'-'.$budgetData->getDay());
		}		
		$fieldset->addField('day', 'date', array(
			'name'   => 'day',
			'label'  => Mage::helper('sublogin')->__('Day'),
			'title'  => Mage::helper('sublogin')->__('Day'),
			'image'  => $this->getSkinUrl('images/grid-cal.gif'),
			'input_format' => Varien_Date::DATE_INTERNAL_FORMAT,
			'format'       => 'yyyy-MM-d',
			'container_id'   => 'day_container',
			'class'   => 'ymd_fields',
			'required'  => true,
		));

		$fieldset->addField('daily', 'text', array(
			'name'   => 'daily',
			'label'  => Mage::helper('sublogin')->__('Daily'),
			'title'  => Mage::helper('sublogin')->__('Daily'),
			'container_id'   => 'daily_container',
			'class'   => 'ymd_fields',
			'required'  => true,
		));
		
		$fieldset->addField('per_order', 'text', array(
            'label'     => Mage::helper('sublogin')->__('Per Order'),
            'class'     => '',
            'required'  => false,
            'name'      => 'per_order',
			'container_id'      => 'per_order_container',
		));
		
		$fieldset->addField('amount', 'text', array(
            'label'     => Mage::helper('sublogin')->__('Amount'),
            'class'     => 'required-entry ymd_fields',
            'required'  => true,
            'name'      => 'amount',
			'container_id'   => 'amount_container',
		));

		$js_script = '<script type="text/javascript">
		$("budget_type").observe("change", function(){
				selectedValue = $(this).value;
				subloginDisplayContainerByBudgetType(selectedValue);
			});
			
			showContainer = function (containerId) {
				containers = ["day_container", "year_container", "month_container", "daily_container", "monthly_container", "yearly_container"];
				containers.each(function(cntnrId){
					if (cntnrId == containerId) {
						$(cntnrId).show();
					} else {
						$(cntnrId).hide();
					}
				});
			}
			
			subloginDisplayContainerByBudgetType = function(selectedValue){
				// remove required validation from all
				$$(".ymd_fields").each(function(elem){
					elem.removeClassName("required-entry")
				});
				if (selectedValue == "yearly") {
					$("amount_container").hide();
					
					showContainer("yearly_container");
					$("yearly").addClassName("required-entry");
				} else if (selectedValue == "year") {
					$("amount_container").show();
					$("amount").addClassName("required-entry");
					
					showContainer("year_container");
					$("year").addClassName("required-entry");
				} else if (selectedValue == "monthly") {
					$("amount_container").hide();
					showContainer("monthly_container");
					$("monthly").addClassName("required-entry");
				} else if (selectedValue == "month") {
					$("amount_container").show();
					$("amount").addClassName("required-entry");
					
					showContainer("month_container");
					$("month").addClassName("required-entry");
				} else if (selectedValue == "daily") {
					$("amount_container").hide();
					showContainer("daily_container");
					$("daily").addClassName("required-entry");
				} else if (selectedValue == "day") {
					$("amount_container").show();
					$("amount").addClassName("required-entry");
					
					showContainer("day_container");
					$("day").addClassName("required-entry");
				} else {
					$("day_container").hide();
					$("year_container").hide();
					$("month_container").hide();
					$("daily_container").hide();
					$("monthly_container").hide();
					$("yearly_container").hide();
				}
			}
			
			subloginDisplayContainerByBudgetType("'.$budgetData->getBudgetType().'");
		</script>';
		
		$fieldset->addField('js_scripts', 'hidden', array(
            'class'     => '',
            'name'      => 'js_scripts',
            'after_element_html'  => $js_script,
		));
        
        $form->setValues($budgetData->getData());
        return parent::_prepareForm();
    }

}
