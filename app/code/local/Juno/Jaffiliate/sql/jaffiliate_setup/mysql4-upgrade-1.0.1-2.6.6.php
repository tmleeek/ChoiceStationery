<?php
 /**
  *
  *
  **/
  
$setup = new Mage_Eav_Model_Entity_Setup('core_setup');

$setup->addAttribute('catalog_category', 'event_id', array(
    'group'         => 'General Information',
    'input'         => 'select',
    'type'          => 'varchar',
    'label'         => 'Event ID',
    'source'		=> 'jaffiliate/source_eventid',
    'backend'       => '',
    'visible'       => 0,
    'required'      => 0,
    'user_defined' 	=> 0,
    'global'        => Mage_Catalog_Model_Resource_Eav_Attribute::SCOPE_GLOBAL));