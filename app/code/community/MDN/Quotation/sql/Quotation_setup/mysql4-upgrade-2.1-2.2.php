<?php
/**
 * Magento
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 *
 * @copyright  Copyright (c) 2009 Maison du Logiciel (http://www.maisondulogiciel.com)
 * @author : Olivier ZIMMERMANN
 * @license    http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
$installer=$this;
/* @var $installer Mage_Eav_Model_Entity_Setup */

$installer->startSetup();
 
//rajoute l'attribut
$installer->addAttribute('catalog_product','allow_individual_quote_request', array(
															'type' 		=> 'int',
															'visible' 	=> true,
															'label'		=> 'Allow individual quote request',
															'required'  => false,
															'default'   => '0',
															'global'    => Mage_Catalog_Model_Resource_Eav_Attribute::SCOPE_GLOBAL,
															'apply_to'  => 'simple',
									                        'input'     => 'select',
									                        'class'     => '',
									                        'source'    => 'eav/entity_attribute_source_boolean',
									                        'used_in_product_listing'	=> true
															));

															
$installer->endSetup();

