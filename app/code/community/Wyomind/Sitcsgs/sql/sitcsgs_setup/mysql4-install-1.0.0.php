<?php

$installer = $this;
$installer->startSetup();
//$this->getTable('simplegoogleshopping');
$installer->run("
INSERT INTO `{$this->getTable('simplegoogleshopping')}` (`simplegoogleshopping_id`, `simplegoogleshopping_filename`, `simplegoogleshopping_path`, `simplegoogleshopping_time`, `store_id`, `simplegoogleshopping_url`, `simplegoogleshopping_title`, `simplegoogleshopping_description`, `simplegoogleshopping_xmlitempattern`, `simplegoogleshopping_categories`, `simplegoogleshopping_type_ids`, `simplegoogleshopping_visibility`, `simplegoogleshopping_attributes`) VALUES
(NULL, 'GoogleShopping_StockInTheChannel.xml', '/', NULL, 1, 'http://wwww.website.com', 'Data feed title', 'Stock In The Channel Template', '<!-- Basic Product Information -->
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
<g:adwords_labels>{adwords_labels}</g:adwords_labels>', '*', 'simple', '2,3,4', '[{\"line\":\"0\",\"checked\":true,\"code\":\"price\",\"condition\":\"gt\",\"value\":\"0\"},{\"line\":\"1\",\"checked\":true,\"code\":\"sku\",\"condition\":\"notnull\",\"value\":\"\"},{\"line\":\"2\",\"checked\":true,\"code\":\"name\",\"condition\":\"notnull\",\"value\":\"\"},{\"line\":\"3\",\"checked\":true,\"code\":\"small_image\",\"condition\":\"neq\",\"value\":\"\"},{\"line\":\"4\",\"checked\":false,\"code\":\"ean\",\"condition\":\"notnull\",\"value\":\"\"},{\"line\":\"5\",\"checked\":true,\"code\":\"description\",\"condition\":\"notnull\",\"value\":\"\"},{\"line\":\"6\",\"checked\":true,\"code\":\"description\",\"condition\":\"neq\",\"value\":\"\"},{\"line\":\"7\",\"checked\":false,\"code\":\"cost\",\"condition\":\"eq\",\"value\":\"\"},{\"line\":\"8\",\"checked\":false,\"code\":\"cost\",\"condition\":\"eq\",\"value\":\"\"},{\"line\":\"9\",\"checked\":false,\"code\":\"cost\",\"condition\":\"eq\",\"value\":\"\"},{\"line\":\"10\",\"checked\":false,\"code\":\"cost\",\"condition\":\"eq\",\"value\":\"\"}]');
");
$installer->endSetup();




 
 


