<?php

$installer = $this;
$installer->startSetup();
$installer->addAttribute('catalog_category', 'cat_use_external_images', array(
                        'type'                       => 'int',
                        'label'                      => 'Use External Images',
                        'input'                      => 'select',
                        'source'                     => 'eav/entity_attribute_source_boolean',
                        'sort_order'                 => 50,
                        'global'                     => Mage_Catalog_Model_Resource_Eav_Attribute::SCOPE_STORE,
                        'group'                      => 'General Information',
						'visible'           		 => true,
						'required'          		 => true,
						'user_defined'      		 => true,
));
$installer->addAttribute('catalog_category', 'cat_external_image', array(
                        'type'                       => 'varchar',
                        'label'                      => 'External Image',
                        'input'                      => 'text',
                        'sort_order'                 => 51,
                        'global'                     => Mage_Catalog_Model_Resource_Eav_Attribute::SCOPE_STORE,
                        'group'                      => 'General Information',
						'visible'           		 => true,
						'required'          		 => false,
						'user_defined'      		 => true,						
));
$installer->addAttribute('catalog_category', 'cat_external_thumbnail', array(
                        'type'                       => 'varchar',
                        'label'                      => 'External Thumbnail',
                        'input'                      => 'text',
                        'sort_order'                 => 52,
                        'global'                     => Mage_Catalog_Model_Resource_Eav_Attribute::SCOPE_STORE,					
                        'group'                      => 'General Information',
						'visible'           		 => true,
						'required'          		 => false,
						'user_defined'      		 => true,						
));
$installer->endSetup();