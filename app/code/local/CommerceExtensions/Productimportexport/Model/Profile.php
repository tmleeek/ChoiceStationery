<?php

class CommerceExtensions_Productimportexport_Model_Profile extends Mage_Dataflow_Model_Profile
{
    protected function _construct()
    {
        $this->_init('dataflow/profile');
    }
    
    public function _parseGuiData()
    {
        $nl = "\r\n";
        $import = $this->getDirection()==='import';
        $p = $this->getGuiData();

        if ($this->getDataTransfer()==='interactive') {
//            $p['file']['type'] = 'file';
//            $p['file']['filename'] = $p['interactive']['filename'];
//            $p['file']['path'] = 'var/export';

            $interactiveXml = '<action type="dataflow/convert_adapter_http" method="'
                . ($import ? 'load' : 'save') . '">' . $nl;
            #$interactiveXml .= '    <var name="filename"><![CDATA['.$p['interactive']['filename'].']]></var>'.$nl;
            $interactiveXml .= '</action>';

            $fileXml = '';
        } else {
            $interactiveXml = '';

            $fileXml = '<action type="dataflow/convert_adapter_io" method="'
                . ($import ? 'load' : 'save') . '">' . $nl;
            $fileXml .= '    <var name="type">' . $p['file']['type'] . '</var>' . $nl;
            $fileXml .= '    <var name="path">' . $p['file']['path'] . '</var>' . $nl;
            $fileXml .= '    <var name="filename"><![CDATA[' . $p['file']['filename'] . ']]></var>' . $nl;
   			#$fileXml .= '    <var name="link">/export/download.php?download_file=' . $p['file']['filename'] . '</var>' . $nl;
			
            if ($p['file']['type']==='ftp') {
                $hostArr = explode(':', $p['file']['host']);
                $fileXml .= '    <var name="host"><![CDATA[' . $hostArr[0] . ']]></var>' . $nl;
                if (isset($hostArr[1])) {
                    $fileXml .= '    <var name="port"><![CDATA[' . $hostArr[1] . ']]></var>' . $nl;
                }
                if (!empty($p['file']['passive'])) {
                    $fileXml .= '    <var name="passive">true</var>' . $nl;
                }
                if ((!empty($p['file']['file_mode']))
                        && ($p['file']['file_mode'] == FTP_ASCII || $p['file']['file_mode'] == FTP_BINARY)
                ) {
                    $fileXml .= '    <var name="file_mode">' . $p['file']['file_mode'] . '</var>' . $nl;
                }
                if (!empty($p['file']['user'])) {
                    $fileXml .= '    <var name="user"><![CDATA[' . $p['file']['user'] . ']]></var>' . $nl;
                }
                if (!empty($p['file']['password'])) {
                    $fileXml .= '    <var name="password"><![CDATA[' . $p['file']['password'] . ']]></var>' . $nl;
                }
            }
            if ($import) {
                $fileXml .= '    <var name="format"><![CDATA[' . $p['parse']['type'] . ']]></var>' . $nl;
            }
            $fileXml .= '</action>' . $nl . $nl;
        }

        switch ($p['parse']['type']) {
            case 'excel_xml':
                $parseFileXml = '<action type="dataflow/convert_parser_xml_excel" method="'
                    . ($import ? 'parse' : 'unparse') . '">' . $nl;
                $parseFileXml .= '    <var name="single_sheet"><![CDATA['
                    . ($p['parse']['single_sheet'] !== '' ? $p['parse']['single_sheet'] : '')
                    . ']]></var>' . $nl;
                break;

            case 'csv':
                $parseFileXml = '<action type="dataflow/convert_parser_csv" method="'
                    . ($import ? 'parse' : 'unparse') . '">' . $nl;
                $parseFileXml .= '    <var name="delimiter"><![CDATA['
                    . $p['parse']['delimiter'] . ']]></var>' . $nl;
                $parseFileXml .= '    <var name="enclose"><![CDATA['
                    . $p['parse']['enclose'] . ']]></var>' . $nl;
                break;
        }
        $parseFileXml .= '    <var name="fieldnames">' . $p['parse']['fieldnames'] . '</var>' . $nl;
        $parseFileXmlInter = $parseFileXml;
        $parseFileXml .= '</action>' . $nl . $nl;

        $mapXml = '';

        if (isset($p['map']) && is_array($p['map'])) {
            foreach ($p['map'] as $side=>$fields) {
                if (!is_array($fields)) {
                    continue;
                }
                foreach ($fields['db'] as $i=>$k) {
                    if ($k=='' || $k=='0') {
                        unset($p['map'][$side]['db'][$i]);
                        unset($p['map'][$side]['file'][$i]);
                    }
                }
            }
        }
        $mapXml .= '<action type="dataflow/convert_mapper_column" method="map">' . $nl;
        $map = $p['map'][$this->getEntityType()];
        if (sizeof($map['db']) > 0) {
            $from = $map[$import?'file':'db'];
            $to = $map[$import?'db':'file'];
            $mapXml .= '    <var name="map">' . $nl;
            $parseFileXmlInter .= '    <var name="map">' . $nl;
            foreach ($from as $i=>$f) {
                $mapXml .= '        <map name="' . $f . '"><![CDATA[' . $to[$i] . ']]></map>' . $nl;
                $parseFileXmlInter .= '        <map name="' . $f . '"><![CDATA[' . $to[$i] . ']]></map>' . $nl;
            }
            $mapXml .= '    </var>' . $nl;
            $parseFileXmlInter .= '    </var>' . $nl;
        }
        if ($p['map']['only_specified']) {
            $mapXml .= '    <var name="_only_specified">' . $p['map']['only_specified'] . '</var>' . $nl;
            //$mapXml .= '    <var name="map">' . $nl;
            $parseFileXmlInter .= '    <var name="_only_specified">' . $p['map']['only_specified'] . '</var>' . $nl;
        }
        $mapXml .= '</action>' . $nl . $nl;

        $parsers = array(
            'product'=>'catalog/convert_parser_product',
            'customer'=>'customer/convert_parser_customer',
        );

        if ($import) {
//            if ($this->getDataTransfer()==='interactive') {
                $parseFileXmlInter .= '    <var name="store"><![CDATA[' . $this->getStoreId() . ']]></var>' . $nl;
//            } else {
//                $parseDataXml = '<action type="' . $parsers[$this->getEntityType()] . '" method="parse">' . $nl;
//                $parseDataXml = '    <var name="store"><![CDATA[' . $this->getStoreId() . ']]></var>' . $nl;
//                $parseDataXml .= '</action>'.$nl.$nl;
//            }
//            $parseDataXml = '<action type="'.$parsers[$this->getEntityType()].'" method="parse">'.$nl;
//            $parseDataXml .= '    <var name="store"><![CDATA['.$this->getStoreId().']]></var>'.$nl;
//            $parseDataXml .= '</action>'.$nl.$nl;
        } else {
            $parseDataXml = '<action type="productimportexport/convert_parser_productexport" method="unparse">' . $nl;
            $parseDataXml .= '    <var name="store"><![CDATA[' . $this->getStoreId() . ']]></var>' . $nl;
            if (isset($p['export']['add_url_field'])) {
                $parseDataXml .= '    <var name="url_field"><![CDATA['
                    . $p['export']['add_url_field'] . ']]></var>' . $nl;
            }
            
            // Custom fields
            $recordLimitStart = isset($p['unparse']['recordlimitstart']) ? $p['unparse']['recordlimitstart'] : '0';
            $parseDataXml .= '    <var name="recordlimitstart"><![CDATA[' . $recordLimitStart . ']]></var>' . $nl;
            
            $recordLimitEnd = isset($p['unparse']['recordlimitend']) ? $p['unparse']['recordlimitend'] : '0';
            $parseDataXml .= '    <var name="recordlimitend"><![CDATA[' . $recordLimitEnd . ']]></var>' . $nl;
			
            $export_filter_by_code = isset($p['unparse']['export_filter_by_code']) ? $p['unparse']['export_filter_by_code'] : '';
            $parseDataXml .= '    <var name="export_filter_by_code"><![CDATA[' . $export_filter_by_code . ']]></var>' . $nl;
			
            $export_by_manufacturer = isset($p['unparse']['export_filter_by_manufacturer']) ? $p['unparse']['export_filter_by_manufacturer'] : '';
            $parseDataXml .= '    <var name="export_filter_by_manufacturer"><![CDATA[' . $export_by_manufacturer . ']]></var>' . $nl;
            
            $exportGroupedPosition = isset($p['unparse']['export_grouped_position']) ? $p['unparse']['export_grouped_position'] : 'false';
            $parseDataXml .= '    <var name="export_grouped_position"><![CDATA[' . $exportGroupedPosition . ']]></var>' . $nl;
            
            $exportRelatedPosition = isset($p['unparse']['export_related_position']) ? $p['unparse']['export_related_position'] : 'false';
            $parseDataXml .= '    <var name="export_related_position"><![CDATA[' . $exportRelatedPosition . ']]></var>' . $nl;
            
            $exportCrossellPosition = isset($p['unparse']['export_crossell_position']) ? $p['unparse']['export_crossell_position'] : 'false';
            $parseDataXml .= '    <var name="export_crossell_position"><![CDATA[' . $exportCrossellPosition . ']]></var>' . $nl;
            
            $exportUpsellPosition = isset($p['unparse']['export_upsell_position']) ? $p['unparse']['export_upsell_position'] : 'false';
            $parseDataXml .= '    <var name="export_upsell_position"><![CDATA[' . $exportUpsellPosition . ']]></var>' . $nl;
            
            $exportCategoryPaths = isset($p['unparse']['export_category_paths']) ? $p['unparse']['export_category_paths'] : 'false';
            $parseDataXml .= '    <var name="export_category_paths"><![CDATA[' . $exportCategoryPaths . ']]></var>' . $nl;
				
			$exportFullImagePaths = isset($p['unparse']['export_full_image_paths']) ? $p['unparse']['export_full_image_paths'] : 'false';
			$parseDataXml .= '    <var name="export_full_image_paths"><![CDATA[' . $exportFullImagePaths . ']]></var>' . $nl;
			
			$export_multi_store = isset($p['unparse']['export_multi_store']) ? $p['unparse']['export_multi_store'] : 'false';
			$parseDataXml .= '    <var name="export_multi_store"><![CDATA[' . $export_multi_store . ']]></var>' . $nl;
            
            $parseDataXml .= '</action>' . $nl . $nl;
        }

        $adapters = array(
            'product'=>'catalog/convert_adapter_product',
            'customer'=>'customer/convert_adapter_customer',
        );

        if ($import) {
            $entityXml = '<action type="' . $adapters[$this->getEntityType()] . '" method="save">' . $nl;
            $entityXml .= '    <var name="store"><![CDATA[' . $this->getStoreId() . ']]></var>' . $nl;
            $entityXml .= '</action>' . $nl . $nl;
        } else {
            $entityXml = '<action type="productimportexport/convert_adapter_productimport" method="load">' . $nl;
            $entityXml .= '    <var name="store"><![CDATA[' . $this->getStoreId() . ']]></var>' . $nl;
            foreach ($p[$this->getEntityType()]['filter'] as $f=>$v) {

                if (empty($v)) {
                    continue;
                }
                if (is_scalar($v)) {
                    $entityXml .= '    <var name="filter/' . $f . '"><![CDATA[' . $v . ']]></var>' . $nl;
                    $parseFileXmlInter .= '    <var name="filter/' . $f . '"><![CDATA[' . $v . ']]></var>' . $nl;
                } elseif (is_array($v)) {
                    foreach ($v as $a=>$b) {

                        if (strlen($b) == 0) {
                            continue;
                        }
                        $entityXml .= '    <var name="filter/' . $f . '/' . $a
                            . '"><![CDATA[' . $b . ']]></var>' . $nl;
                        $parseFileXmlInter .= '    <var name="filter/' . $f . '/'
                            . $a . '"><![CDATA[' . $b . ']]></var>' . $nl;
                    }
                }
            }
            
            $entityXml .= '    <var name="filter/adressType"><![CDATA[default_billing]]></var>' . $nl;
            
            $entityXml .= '</action>' . $nl . $nl;
        }

        // Need to rewrite the whole xml action format
        if ($import) {
            $numberOfRecords = isset($p['import']['number_of_records']) ? $p['import']['number_of_records'] : 1;
            $decimalSeparator = isset($p['import']['decimal_separator']) ? $p['import']['decimal_separator'] : ' . ';
            $parseFileXmlInter .= '    <var name="number_of_records">'
                . $numberOfRecords . '</var>' . $nl;
            $parseFileXmlInter .= '    <var name="decimal_separator"><![CDATA['
                . $decimalSeparator . ']]></var>' . $nl;
            
            // Custom fields
            $rootCatalogId = isset($p['parse']['root_catalog_id']) ? $p['parse']['root_catalog_id'] : '2';
            $parseFileXmlInter .= '    <var name="root_catalog_id"><![CDATA[' . $rootCatalogId . ']]></var>' . $nl;
            
            $import_attribute_value = isset($p['parse']['import_attribute_value']) ? $p['parse']['import_attribute_value'] : 'false';
            $parseFileXmlInter .= '    <var name="import_attribute_value"><![CDATA[' . $import_attribute_value . ']]></var>' . $nl;
			
            $attribute_for_import_value = isset($p['parse']['attribute_for_import_value']) ? $p['parse']['attribute_for_import_value'] : '';
            $parseFileXmlInter .= '    <var name="attribute_for_import_value"><![CDATA[' . $attribute_for_import_value . ']]></var>' . $nl;
			
            $update_products_only = isset($p['parse']['update_products_only']) ? $p['parse']['update_products_only'] : 'false';
            $parseFileXmlInter .= '    <var name="update_products_only"><![CDATA[' . $update_products_only . ']]></var>' . $nl;
			
            $import_images_by_url = isset($p['parse']['import_images_by_url']) ? $p['parse']['import_images_by_url'] : 'false';
            $parseFileXmlInter .= '    <var name="import_images_by_url"><![CDATA[' . $import_images_by_url . ']]></var>' . $nl;
			
            $multistoreimages = isset($p['parse']['multi_store_images']) ? $p['parse']['multi_store_images'] : 'false';
            $parseFileXmlInter .= '    <var name="multi_store_images"><![CDATA[' . $multistoreimages . ']]></var>' . $nl;
			
            $reimportImages = isset($p['parse']['reimport_images']) ? $p['parse']['reimport_images'] : 'false';
            $parseFileXmlInter .= '    <var name="reimport_images"><![CDATA[' . $reimportImages . ']]></var>' . $nl;
            
            $deleteallAndreimportImages = isset($p['parse']['deleteall_andreimport_images']) ? $p['parse']['deleteall_andreimport_images'] : 'false';
            $parseFileXmlInter .= '    <var name="deleteall_andreimport_images"><![CDATA[' . $deleteallAndreimportImages . ']]></var>' . $nl;
            
            $excludeImages = isset($p['parse']['exclude_images']) ? $p['parse']['exclude_images'] : 'false';
            $parseFileXmlInter .= '    <var name="exclude_images"><![CDATA[' . $excludeImages . ']]></var>' . $nl;
            
            $excludeGalleryImages = isset($p['parse']['exclude_gallery_images']) ? $p['parse']['exclude_gallery_images'] : 'false';
            $parseFileXmlInter .= '    <var name="exclude_gallery_images"><![CDATA[' . $excludeGalleryImages . ']]></var>' . $nl;
            
            $appendTierPrices = isset($p['parse']['append_tier_prices']) ? $p['parse']['append_tier_prices'] : 'false';
            $parseFileXmlInter .= '    <var name="append_tier_prices"><![CDATA[' . $appendTierPrices . ']]></var>' . $nl;
            
            $appendGroupPrices = isset($p['parse']['append_group_prices']) ? $p['parse']['append_group_prices'] : 'false';
            $parseFileXmlInter .= '    <var name="append_group_prices"><![CDATA[' . $appendGroupPrices . ']]></var>' . $nl;
            
            $appendCategories = isset($p['parse']['append_categories']) ? $p['parse']['append_categories'] : 'false';
            $parseFileXmlInter .= '    <var name="append_categories"><![CDATA[' . $appendCategories . ']]></var>' . $nl;
			
            $configurable_use_default = isset($p['parse']['configurable_use_default']) ? $p['parse']['configurable_use_default'] : '0';
            $parseFileXmlInter .= '    <var name="configurable_use_default"><![CDATA[' . $configurable_use_default . ']]></var>' . $nl;
            
            if ($this->getDataTransfer()==='interactive') {
                $xml = $parseFileXmlInter;
                $xml .= '    <var name="adapter">productimportexport/convert_adapter_productimport</var>' . $nl;
                $xml .= '    <var name="method">parse</var>' . $nl;
                $xml .= '</action>';
            } else {
                $xml = $fileXml;
                $xml .= $parseFileXmlInter;
                $xml .= '    <var name="adapter">productimportexport/convert_adapter_productimport</var>' . $nl;
                $xml .= '    <var name="method">parse</var>' . $nl;
                $xml .= '</action>';
            }
            //$xml = $interactiveXml.$fileXml.$parseFileXml.$mapXml.$parseDataXml.$entityXml;

        } else {
            $xml = $entityXml . $parseDataXml . $mapXml . $parseFileXml . $fileXml . $interactiveXml;
        }

        $this->setGuiData($p);
        $this->setActionsXml($xml);
/*echo "<pre>" . print_r($p,1) . "</pre>";
echo "<xmp>" . $xml . "</xmp>";
die;*/
        return $this;
    }
}