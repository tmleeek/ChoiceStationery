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
	'Nouveau devis disponible',
	'Bonjour {{var name}}<br />Nous avons le plaisir de vous annoncer que votre <b>devis no {{var increment_id}} {{var caption}}</b> est disponible sur votre espace client<br>Pour le consulter, il vous suffit de vous rendre dans votre <a href=\"{{store url=\"\"}}\">espace client.</a>',
	2,
	'Nouveau devis disponible'
);
 
insert into {$this->getTable('core_email_template')} 
(template_code, template_text, template_type, template_subject)
values
(
	'New quotation available',
	'Dear {{var name}}<br />You new quotation <b>#{{var increment_id}} {{var caption}}</b> is available in your customer account<br>To view it, go to <a href=\"{{store url=\"\"}}\">your customer accout</a>',
	2,
	'New quotation available'
);

    ");
 
$installer->endSetup();*/