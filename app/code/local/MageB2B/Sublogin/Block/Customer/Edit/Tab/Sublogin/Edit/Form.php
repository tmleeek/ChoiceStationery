<?php
/**
 * @category Customer Service Version 3.0
 * @package MageB2B_Sublogin
 * @author AIRBYTES GmbH <info@airbytes.de>
 * @copyright AIRBYTES GmbH
 * @license commercial
 * @date 09.01.2016
 */
class MageB2B_Sublogin_Block_Customer_Edit_Tab_Sublogin_Edit_Form extends Mage_Adminhtml_Block_Widget_Form
{
    protected function _prepareForm()
    {

        $customer = Mage::registry('current_customer');
        $model = Mage::registry('subloginModel');

        $form = new Varien_Data_Form(
            array(
                'id' => 'edit_form',
                'action' => $this->getUrl('*/*/save', array('id' => $this->getRequest()->getParam('id'))),
                'method' => 'post',
            )
        );

        $fieldset   = $form->addFieldset('base_fieldset', array(
            'legend'    => Mage::helper('sublogin')->__('Sublogin'),
            'class'     => 'fieldset-wide'
        ));

        if ($model->getId())
        {
            $fieldset->addField('id', 'hidden', array(
                'name'      => 'id',
                'value'     => $model->getId(),
            ));
			
			$fieldset->addField('entity_id', 'text', array(
				'name'      => 'entity_id',
				'label'     => Mage::helper('sublogin')->__('Customer'),
				'title'     => Mage::helper('sublogin')->__('Customer'),
				'required'  => true,
				'value'		=>	$model->getEntityId(),
			));
			
		}else{
			$fieldset->addField('entity_id', 'text', array(
				'name'      => 'entity_id',
				'label'     => Mage::helper('sublogin')->__('Customer'),
				'title'     => Mage::helper('sublogin')->__('Customer'),
				'required'  => true,
				'value'     => (int)$model->getEntityId(),
			));
		}
		
		$autocompleteRenderer = Mage::getSingleton('core/layout')->createBlock('sublogin/admin_autocompleter');
		//$autocompleteRenderer->setAutocompleteData($autocompleteRenderer->getCustomers());
		$form->getElement('entity_id')->setRenderer($autocompleteRenderer);

        $gridFieldsFromHelper = Mage::helper('sublogin')->getGridFields($customer);

        foreach ($gridFieldsFromHelper as $field)
        {
            if ($field['type'] == 'html')
            {
                if ($field['name'] == 'expire_date')
                {
                    $fieldData = array(
                        'name'      => $field['name'],
                        'label'     => $field['label'],
                        'value'     => $model->getId(),
                        'required'  => isset($field['required']) ? 1 : 0,
                        // 'format'    => Mage::app()->getLocale()->getDateFormat(Mage_Core_Model_Locale::FORMAT_TYPE_MEDIUM),
                        'format'    => "y-M-dd",
                        'image'     => $this->getSkinUrl('images/grid-cal.gif'),
                        'value'     => $model->getExpireDate() == 0 ? null : $model->getExpireDate(),
                    );
                    $fieldset->addField($field['name'], 'date', $fieldData);
                }
            }
            else if ($field['name'] == 'password')
            {
                $fieldData = $field;
                $fieldData['value'] = '';
                if ($model->getId())
                {
					$fieldData['required'] = false;
                    $fieldData['readonly'] = false;
                    $fieldData['disabled'] = false;
                    $fieldData['class'] = 'validate-password';
                    $fieldData['onlyNewRequired'] = false;
                    $fieldData['onlyNewValue'] = false;
                    $fieldData['after_element_html'] = Mage::helper('sublogin')->__('Only type in password if you need to change. Otherwise the password will not be set.');
					
					if($model->getPassword() != ""){
						// $fieldData['value'] = $model->getPassword();
						$fieldData['value'] = "******";
					}
                }
				
				// because admin can not set password on their own, so we unset the readonly and disabled attributes
				unset($fieldData['readonly']);
				unset($fieldData['disabled']);
				
                $fieldset->addField($field['name'], 'text', $fieldData);
            }
            //else if ($field['name'] == 'address_ids')
            else if ($field['type'] == 'multiselect')
            {
                $fieldData = $field;
                $optionArray = array();
                foreach ($fieldData['options'] as $key => $addressId)
                {
                    $innerArray = array(
                          'value' => $key,
                          'label' => $addressId,
                    );
                    $optionArray[] = $innerArray;
                }
                $fieldData['values'] = $optionArray;
                //$fieldData['value'] = explode(',', $model->getData('address_ids'));
                $fieldData['value'] = explode(',', $model->getData($field['name']));
                $fieldset->addField($field['name'], 'multiselect', $fieldData);
            }

            else
            {
                $fieldData = array(
                    'name'      => $field['name'],
                    'label'     => $field['label'],
                    'value'     => $model->getId(),
                    'required'  => $field['required'],
                    'value' => $model->getData($field['name']),
                );
                foreach (array('options'=>'options', 'cssclass'=>'class') as $k=>$v)
                {
                    if (isset($field[$k]))
                    {
                        $fieldData[$v] = $field[$k];
                    }
                }
                if (isset($field['readonly']) && $field['readonly'])
                        $fieldData['readonly'] = true;
                if ($field['type'] == 'checkbox')
                {
                    $fieldData['value'] = 1;
                    $fieldData['checked'] = (boolean)$model->getData($field['name']);
                }
                $fieldset->addField($field['name'], $field['type'],$fieldData);
            }
        }

		$tempEleForScript = $fieldset->addField('temp_ele_for_script', 'hidden', array(
			'name'      => 'temp_ele_for_script',
		));
		$script = '';
		if (Mage::getModel('sublogin/source_formfields')->isFieldAllowed('expire_date'))
		{
			$script .= '
			document.observe("dom:loaded", function() {
				var currentDate = new Date('.(time()*1000).');
				
				// The number of milliseconds in one day
				var ONE_DAY = 1000 * 60 * 60 * 24
				// dateformat which gets used
				var dateFormat = "%Y-%m-%d";

				function days_between(date1, date2) {
					// Convert both dates to milliseconds
					var date1_ms = date1.getTime();
					var date2_ms = date2.getTime();
					// Calculate the difference in milliseconds
					var difference_ms = date2_ms - date1_ms;
					// Convert back to days and return
					return Math.ceil(difference_ms/ONE_DAY);
				}

				function generatePassword() {
					var length = 6,
						charset = "abcdefghijklnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789",
						retVal = "";
					for (var i = 0, n = charset.length; i < length; ++i) {
						retVal += charset.charAt(Math.floor(Math.random() * n));
					}
					return retVal;
				}

				function updateDaysFromDates(currentDate, expDate) {
					if (days_between(currentDate, expDate) < 1) // only update positive days
						return;
					$("days_to_expire").value = days_between(currentDate, expDate);
					$("expire_date").value = expDate.print(dateFormat);
				}
				
				function updateDateFromDays(currentDate, days) {
					if (days == "" || !parseInt(days))
						$("expire_date").value = "";
					else {
						var newDate = new Date(currentDate.getTime() + days * ONE_DAY);
						$("expire_date").value = newDate.print(dateFormat);
					}
				}

				$("expire_date").observe("change", function(event) {
					newDateFormated = Date.parseDate($(this).value, dateFormat);
					updateDaysFromDates(currentDate, newDateFormated);
				});

				// register days_to_expire handler - when editing this field the expire_date should be updated
				$("days_to_expire").observe("change", function(event) {
					var days = $("days_to_expire").value;
					updateDateFromDays(currentDate, days);
				});

				if ($("id") !== null)
				{
					var exp_date = Date.parseDate($("expire_date").value, dateFormat);
					updateDaysFromDates(currentDate, exp_date);
				}
				else
				{
					$("password").value = generatePassword();
					$("days_to_expire").value = 90;
					$("active").checked = true;
					$("send_backendmails").checked = true;
					$("create_sublogins").checked = false;
					updateDateFromDays(currentDate, 90);
				}

				/** when an inactive field gets activated - set the days back to 90 */
				if ($("active"))
				{
					$("active").observe("change", function(event) {
						var el = $(this);
						if (el.checked) {
							var id = el.readAttribute("rel");
							$("days_to_expire").value = 90;
							var days = $("days_to_expire").value;
							updateDateFromDays(currentDate, days);
						}
					});
				}
			});';
		}
		
		if ($script != '')
		{
			$script = '<script type="text/javascript">'.$script.'</script>';
			$tempEleForScript->setAfterElementHtml($script);
		}

		$form->setUseContainer(true);
        $this->setForm($form);
        return parent::_prepareForm();
    }
}
