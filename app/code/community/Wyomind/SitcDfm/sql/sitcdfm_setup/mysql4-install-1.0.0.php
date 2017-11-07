<?php

$installer = $this;
$installer->startSetup();
$installer->run("
insert into `{$this->getTable('datafeedmanager_configurations')}`(`feed_id`,`feed_name`,`feed_type`,`feed_path`,`feed_status`,`feed_updated_at`,`store_id`,`feed_include_header`,`feed_header`,`feed_product`,`feed_footer`,`feed_separator`,`feed_protector`,`feed_escape`,`feed_encoding`,`feed_required_fields`,`feed_enclose_data`,`feed_clean_data`,`datafeedmanager_category_filter`,`datafeedmanager_categories`,`datafeedmanager_type_ids`,`datafeedmanager_visibility`,`datafeedmanager_attributes`,`cron_expr`,`feed_extraheader`,`ftp_enabled`,`ftp_host`,`ftp_login`,`ftp_password`,`ftp_active`,`ftp_dir`) values (23,'GoogleShopping_SITC',1,'/feeds/',1,'2013-07-02 16:13:00',1,0,'<?xml version=\"1.0\" encoding=\"utf-8\" ?>
<rss version=\"2.0\" xmlns:g=\"http://base.google.com/ns/1.0\">
<channel>
<title>Data feed title</title>
<link>http://wwww.website.com</link>
<description>Stock In The Channel Template</description>
','<item>
<!-- Basic Product Information -->
<g:id>{sku}</g:id>
<title>{name,[substr],[70],[...]}</title>
{SC:DESCRIPTION}
{G:GOOGLE_PRODUCT_CATEGORY}
{G:PRODUCT_TYPE,[10]}
{SC:URL}
{SC:IMAGES}
<g:condition>new</g:condition>

<!-- Availability & Price -->
<g:availability>{is_in_stock?[in stock]:[out of stock]}</g:availability>
<g:price>{normal_price,[USD],[0]} </g:price>
{G:SALE_PRICE,[USD],[0]}

<!-- Unique Product Identifiers-->
<g:brand>{manufacturer}</g:brand>
{SC:EAN}
<g:mpn>{sku}</g:mpn>
<g:identifier_exists>TRUE</g:identifier_exists>

<!-- Apparel Products -->
<g:gender>{gender}</g:gender>
<g:age_group>{age_group}</g:age_group>
<g:color>{color}</g:color>
<g:size>{size}</g:size>

<!-- Product Variants -->
{G:ITEM_GROUP_ID}
<g:material>{material}</g:material>
<g:pattern>{pattern}</g:pattern>

<!-- Shipping -->
<g:shipping_weight>{weight,[float],[2]}kg</g:shipping_weight>

<!-- AdWords attributes -->
<g:adwords_grouping>{adwords_grouping}</g:adwords_grouping>
<g:adwords_labels>{adwords_labels}</g:adwords_labels>
</item>','</channel>
</rss>',';','','','UTF-8',null,1,1,0,'','simple','2,3,4','[{\"line\": \"0\", \"checked\": true, \"code\": \"price\", \"condition\": \"gt\", \"value\": \"0\"}, {\"line\": \"1\", \"checked\": true, \"code\": \"sku\", \"condition\": \"notnull\", \"value\": \"\"}, {\"line\": \"2\", \"checked\": true, \"code\": \"name\", \"condition\": \"notnull\", \"value\": \"\"}, {\"line\": \"3\", \"checked\": true, \"code\": \"small_image\", \"condition\": \"neq\", \"value\": \"\"}, {\"line\": \"4\", \"checked\": true, \"code\": \"description\", \"condition\": \"neq\", \"value\": \"\"}, {\"line\": \"5\", \"checked\": true, \"code\": \"description\", \"condition\": \"notnull\", \"value\": \"\"}, {\"line\": \"6\", \"checked\": false, \"code\": \"custom_design_from\", \"condition\": \"eq\", \"value\": \"\"}, {\"line\": \"7\", \"checked\": false, \"code\": \"custom_design_from\", \"condition\": \"eq\", \"value\": \"\"}, {\"line\": \"8\", \"checked\": false, \"code\": \"custom_design_from\", \"condition\": \"eq\", \"value\": \"\"}, {\"line\": \"9\", \"checked\": false, \"code\": \"custom_design_from\", \"condition\": \"eq\", \"value\": \"\"}, {\"line\": \"10\", \"checked\": false, \"code\": \"custom_design_from\", \"condition\": \"eq\", \"value\": \"\"}]','{\"days\": [\"Monday\", \"Tuesday\", \"Wednesday\", \"Thursday\", \"Friday\", \"Saturday\", \"Sunday\"], \"hours\": [\"03:00\"]}','',0,null,null,null,0,null);

");
$installer->endSetup();




 
 

