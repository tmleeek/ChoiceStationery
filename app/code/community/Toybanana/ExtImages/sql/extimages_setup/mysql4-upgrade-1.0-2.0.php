<?php

$installer = $this;
$installer->addAttribute('catalog_product', 'external_gallery', array(
			            'group'             => 'Images',
                        'type'              => 'text',
						'frontend'          => 'catalog/product_attribute_frontend_image',
                        'label'             => 'External Image Gallery',
                        'input'             => 'text',
						'global'            => Mage_Catalog_Model_Resource_Eav_Attribute::SCOPE_STORE,
                        'visible'           => true,
                        'required'          => false,
                        'user_defined'      => true,
                        'used_in_product_listing' => true,
 ));
