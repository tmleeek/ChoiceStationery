<?php
 //ALTER TABLE `quotation` ADD `additional_pdf` VARCHAR( 255 ) NOT NULL ,
//ADD `additional_information` TEXT NOT NULL 
$installer = $this;
 
/*$installer->startSetup();
 
//Ajoute les emails template
$installer->run("
 
insert into {$this->getTable('core_email_template')}  
(template_code, template_text, template_type, template_subject)
values
(
	'Relance_devis',
	'Bonjour {{var customer_name}}<br />Nous sommes toujours en attente de votre reponse concernant le devis {{var quote_name}}.
	
	<br>Vous pouvez consulter votre devis &agrave; partir de votre espace client en cliquant <a href=\"{{var url}}\">sur ce lien</a>
	
	<br>Cordialement',
	2,
	'A propos de votre devis'
);

insert into {$this->getTable('core_email_template')}  
(template_code, template_text, template_type, template_subject)
values
(
	'Quotation_reminder',
	'Dear {{var customer_name}}<br />
	We are still waiting for your feedbacks about your quotation : {{var quote_name}}.
	
	<br>You can consult your quotation from your customer account :  <a href=\"{{var url}}\">My Quotations</a>
	
	<br>Best regards',
	2,
	'Quotation'
);

");
 
$installer->endSetup();*/