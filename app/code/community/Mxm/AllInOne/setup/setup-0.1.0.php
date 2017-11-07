<?php
// Set up the Maxemail customer space for use with this extension
// This script runs in the context of Mxm_AllInOne_Model_Setup
/* @var $setup Mxm_AllInOne_Model_Setup */
$setup = $this;

// Ensure there is a basket type for each store
$setup->addTask(array(
    'name'        => 'BasketTypes',
    'description' => 'Set up basket types',
));

// Create all the necessary folders
$setup->addTask(array(
    'name'        => 'Folders',
    'description' => 'Create folders',
));

//Create the customer profile
$setup->addTask(array(
    'name'        => 'Profiles',
    'description' => 'Create Profiles',
    'depends'     => array('Folders'),
    'check'       => true,
));

//Create the profile fields
$setup->addTask(array(
    'name'        => 'ProfileFields',
    'description' => 'Create Profile Fields',
    'depends'     => array('Folders', 'Profiles'),
    'check'       => true,
    'timeout'     => 300,
));

// Create Email Templates
$setup->addTask(array(
    'name'        => 'EmailTemplates',
    'description' => 'Create email templates',
    'depends'     => array('Folders'),
    'check'       => true
));

//Create the datatables
$setup->addTask(array(
    'name'        => 'Datatables',
    'description' => 'Create Datatables',
    'depends'     => array('Folders'),
    'check'       => true,
));

//Create the datatable fields
$setup->addTask(array(
    'name'        => 'DatatableFields',
    'description' => 'Create Datatable Fields',
    'depends'     => array('Folders', 'Datatables'),
    'check'       => true,
));

//Create the segments
$setup->addTask(array(
    'name'        => 'Segments',
    'description' => 'Create Segments',
    'depends'     => array('Folders', 'ProfileFields'),
));

//Create the subscribers list
$setup->addTask(array(
    'name'        => 'Lists',
    'description' => 'Create Lists',
    'depends'     => array('Folders'),
));

//Create the snippets
$setup->addTask(array(
    'name'        => 'Snippets',
    'description' => 'Create Snippets',
    'depends'     => array('Folders'),
));

//Create the datatable snippets
$setup->addTask(array(
    'name'        => 'DatatableSnippets',
    'description' => 'Create Datatable Snippets',
    'depends'     => array('Folders', 'Datatables'),
));

// Create triggered emails
$setup->addTask(array(
    'name'        => 'TriggerEmails',
    'description' => 'Create Triggered Emails',
    'depends'     => array('Folders', 'EmailTemplates'),
));

// Approve and assign basket emails
$setup->addTask(array(
    'name'        => 'BasketEmails',
    'description' => 'Approve and Assign Basket Emails',
    'depends'     => array('BasketTypes', 'TriggerEmails'),
));

//Set customer configuration values
$setup->addTask(array(
    'name'        => 'Configuration',
    'description' => 'Set Configuration',
    'depends'     => array('ProfileFields', 'DatatableFields'),
));