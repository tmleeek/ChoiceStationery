<?php
/**
 * Price rules upgrade script
 * Add the Custom Customer Attribute containing the Pricerules Group
 *
 * @author Stock in the Channel
 */

$installer = $this;
$installer->startSetup();

$setup = new Mage_Eav_Model_Entity_Setup('sinch_pricerules_setup');
$setup->startSetup();
$customerEntityId = $setup->getEntityTypeId('customer');
$attributeSetId = $setup->getDefaultAttributeSetId($customerEntityId);
$attributeGroupId = $setup->getDefaultAttributeGroupId($customerEntityId, $attributeSetId);

$setup->addAttribute('customer', 'sinch_pricerules_group', array(
	'input'			=>	'text',
	'type'			=>	'int',
	'label'			=>	'Pricerules Group',
	'visible'		=>	1,
	'required'		=>	1,
	'user_defined'	=>	1,
	'default_value'	=>	'0',
));

$setup->addAttributeToGroup(
	$customerEntityId,
	$attributeSetId,
	$attributeGroupId,
	'sinch_pricerules_group',
	'100'
);

$attrib = Mage::getSingleton('eav/config')->getAttribute('customer', 'sinch_pricerules_group');
$attrib->setData('used_in_forms', array('adminhtml_customer'));
$attrib->save();

$setup->endSetup();
$installer->endSetup();