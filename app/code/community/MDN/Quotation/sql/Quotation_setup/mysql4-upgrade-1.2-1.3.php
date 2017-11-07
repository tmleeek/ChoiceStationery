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
 
/*$installer->startSetup();
 
//Ajoute les emails template
$installer->run("
 
insert into {$this->getTable('core_email_template')}  
(template_code, template_text, template_type, template_subject)
values
(
	'Nouvelle demande de devis',
	'Bonjour<br />Une demande de devis a ete creee par le client {{var customer}}
	<br>La demande est disponible <a href=\"{{var url}}\">ici</a>',
	2,
	'Nouvelle demande de devis'
);
 
insert into {$this->getTable('core_email_template')}  
(template_code, template_text, template_type, template_subject)
values
(
	'New quotation request',
	'Hello<br />{{var customer}} made a new quotation request
	<br>Quotation request is available <a href=\"{{var url}}\">here</a>',
	2,	'New quotation request'
);

    ");
 
$installer->endSetup();*/