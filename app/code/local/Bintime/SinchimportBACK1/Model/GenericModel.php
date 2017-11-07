<?php
class GenericModel {
	
	public $dbConf;
	public $db;
    protected $connection;
    protected $_DefaultAttributeSetId;
    protected $_productEntityTypeId;
    protected $_productAttributeId;
    protected $_categoryEntityTypeId;
	protected $_categoryAttributeId;
    protected $isAddProduct = false; // to add products only once into catalog_product_entity
	//protected $isUpdateProductsDistriselectorStatus = false; 
	protected $isApplyCategories = false;
	protected $isAddName = false;
	protected $isSetVisibility = false;
	protected $isEnabledIndex = false;
	protected $isAddProductForWebsite = false;
	protected $isAddStock = false;
	protected $isAddPrice = false;
	protected $isAddCost = false;
	protected $isAddTaxClass = false;
	protected $isAddWeight = false;
	protected $yesAvailability;
	protected $noAvailability;
	
	public function __construct() {
		$this->dbConf['host'] = DB_HOST;
		$this->dbConf['username'] = DB_USERNAME;
		$this->dbConf['password'] = DB_PASSWORD;
		$this->dbConf['dbname'] = DB_NAME;
		$this->db = mysql_connect($this->dbConf['host'], $this->dbConf['username'], $this->dbConf['password'])
		or die(mysql_error().' in file "'.__FILE__.'" in line '.__LINE__."\n");
		mysql_select_db($this->dbConf['dbname'], $this->db);
		$this->query('SET NAMES utf8', __LINE__);
	}
	
	public function getConnection() {
            if (!isset($this->connection)) {
				Mage::app(); 
                    $this->connection = Mage::getSingleton('core/resource')->getConnection('core_write');
            }
			return $this->connection;
        }
    
    public function _getDefaultAttributeSetId($type) {
                if (!isset($this->_DefaultAttributeSetId[$type])) {
                        $sql = "
							SELECT default_attribute_set_id
                            FROM eav_entity_type
                            WHERE entity_type_code = '{$type}'
                            LIMIT 1
                        ";
                        $result = $this->getConnection()->query($sql);
                        if ($row = $result->fetch(PDO::FETCH_ASSOC)) {
                                $this->_DefaultAttributeSetId[$type] = $row['default_attribute_set_id'];
                        }
                        else die("Exception: " . __CLASS__ . "::" . __FUNCTION__);
                }
                return $this->_DefaultAttributeSetId[$type];
        }

    public function _getProductEntityTypeId() {
                if (!$this->_productEntityTypeId) {
                        $sql = "
							SELECT entity_type_id
                            FROM eav_entity_type
                            WHERE entity_type_code = 'catalog_product'
                            LIMIT 1
                        ";
                        $result = $this->getConnection()->query($sql);
						if ($row = $result->fetch(PDO::FETCH_ASSOC)) {
                                $this->_productEntityTypeId = $row['entity_type_id'];
                        }
                        else die("Exception: " . __CLASS__ . "::" . __FUNCTION__);
                }
                return $this->_productEntityTypeId;
        }

	public function _getProductEntityTypeIdforProduct() {
                if (!$this->_productEntityTypeId) {
                        $sql = "
							SELECT entity_type_id
                            FROM eav_entity_type
                            WHERE entity_type_code = 'catalog_product'
							LIMIT 1
                        ";
                        $result = $this->getConnection()->query($sql);
                        if ($row = $result->fetch(PDO::FETCH_ASSOC)) {
                                $this->_productEntityTypeId = $row['entity_type_id'];
                        }
                        else die("Exception: " . __CLASS__ . "::" . __FUNCTION__);
                }
                return $this->_productEntityTypeId;
        }

	public function _getProductAttributeId($attributeCode) {
                if (!is_array($this->_productAttributeId)) {
                        $sql = "
							SELECT attribute_id, attribute_code
                            FROM eav_attribute
                            WHERE entity_type_id = 4
                        ";
                        $result = $this->getConnection()->query($sql);
                        while ($row = $result->fetch(PDO::FETCH_ASSOC)) {
                                $this->_productAttributeId[$row['attribute_code']] = $row['attribute_id'];
                        }
                }
                if (!isset($this->_productAttributeId[$attributeCode])) {
                    throw new Exception("Unknow attribute code: $attributeCode");
                }
                return $this->_productAttributeId[$attributeCode];
        }

    public function _getCategoryEntityTypeId() {
                if (!$this->_categoryEntityTypeId) {
                        $sql = "
							SELECT entity_type_id
                            FROM eav_entity_type
                            WHERE entity_type_code = 'catalog_category'
                            LIMIT 1
                        ";
                        $result = $this->getConnection()->query($sql);
                        if ($row = $result->fetch(PDO::FETCH_ASSOC)) {
                                $this->_categoryEntityTypeId = $row['entity_type_id'];
                        }
                        else die("Exception: " . __CLASS__ . "::" . __FUNCTION__);
                }
                return $this->_categoryEntityTypeId;
        }
    
    protected function _getCategoryAttributeId($attributeCode) {
                if (!is_array($this->_categoryAttributeId)) {
                        $sql = "
							SELECT attribute_id, attribute_code
                            FROM eav_attribute
							WHERE entity_type_id = '{$this->_getCategoryEntityTypeId()}'
                        ";
                        $result = $this->getConnection()->query($sql);
                        while ($row = $result->fetch(PDO::FETCH_ASSOC)) {
                                $this->_categoryAttributeId[$row['attribute_code']] = $row['attribute_id'];
                        }
                }
                if (!isset($this->_categoryAttributeId[$attributeCode])) {
                    echo "attCode: '$attributeCode'";
                    var_dump($this->_categoryAttributeId);
                    die("{$sql}".PHP_EOL);
                }
                return $this->_categoryAttributeId[$attributeCode];
        }

    public function query($sql, $line = false) {
		echo "=====".PHP_EOL;
		echo "Query: ".$sql.PHP_EOL;
		$resource = mysql_query($sql, $this->db);
		echo "Affected: ".mysql_affected_rows().PHP_EOL;
		echo "=====".PHP_EOL;
		
		return $resource;
		
		/*
		$current = 0;

		while ($current++ < 10) {
			echo(date('Y-m-d H:i:s').' - '.$sql . "\n\n");
            $resource = mysql_query($sql, $this->db);
			
            if (mysql_errno() != 0) {
				echo("Classes_GenericModel.php : SQLConnection internal error - Error number: " . var_export(mysql_errno(), true) . " 
					Error: ". var_export(mysql_error(), true) . ". Full info: queryStatus - " . var_export($resource, true) . "Query: " . $sql . " in line '{$line}'\n");
			}
			
			if (!$resource && (mysql_errno() == '1213' ||  mysql_errno() == '1205')) { continue; }
            else { break; }
		}//while ($current++ < 10) 
		
		if (!$resource) { 
			throw new Exception("Query: " . $sql . "\nError: " . var_export(mysql_error(), true). "Error number: " . var_export(mysql_errno(), true));
		} else { return $resource; }
		*/
	}

	public 	function getVendorAttributeId() {
        return $this->_getProductAttributeId('vendor_id');
    }

	public function updateVendor($distributorId) {
        $vendorAttributeId = $this->getVendorAttributeId();   
        $connection = Mage::getModel('core/resource')->getConnection('core_write');
        $manufacturers = $connection->select()
                                    ->from(array('a' => Mage::getSingleton('core/resource')->getTableName('icecat_suppliers_list')),   array('vendor' => 'name'))
                                    ->joinLeft(array('v' => Mage::getSingleton('core/resource')->getTableName('eav_attribute_option_value')), 'a.name = v.value', '')
                                    ->joinLeft(array('o' => Mage::getSingleton('core/resource')->getTableName('eav_attribute_option')), 'v.option_id =  v.option_id', '')
                                    ->where("(o.attribute_id = {$connection->quote($vendorAttributeId)} OR o.attribute_id IS NULL)")
                                    ->where("v.value_id IS NULL");
        $manufacturer = $manufacturers->query();
		while ($rows = $manufacturer->fetch(PDO::FETCH_ASSOC)) {
            try { 
                $connection->beginTransaction();
                	$data = array(
                		'attribute_id' => $vendorAttributeId,
                		'sort_order' => '0'
                	);
				$connection->insert(Mage::getSingleton('core/resource')->getTableName('eav_attribute_option'),$data);
				$select= $connection->select()->from(Mage::getSingleton('core/resource')->getTableName('core_store'),   array('store_id'));
				$result = $select->query();
				$last_id= $connection->lastInsertId();
				while ($row = $result->fetch(PDO::FETCH_ASSOC)) {
					$connection->query("INSERT IGNORE INTO eav_attribute_option_value (option_id,store_id,value)
					VALUES ('".$last_id."','".$row['store_id']."',".$connection->quote($rows['vendor']).")");
                }
				$result = $connection->commit();
            } catch (Exception $e) {
                $connection->rollBack();
                throw new Exception("Error while setting import stage: {$e->getMessage()}");
            }
        }
    }

	public function getAllDistributorsInfo() {
		echo "=====================================================================================".PHP_EOL;
		echo "Function 'getAllDistributorsInfo' start".PHP_EOL;
		echo "=====================================================================================".PHP_EOL;
		$result = $this->query("SELECT * FROM import_distributors");
		$arr = array();
		while ($row = mysql_fetch_assoc($result)) {
			$arr[] = $row;
		}
		return $arr;
	}

	public function setImportStartTime($distributorId) {
		echo "=====================================================================================".PHP_EOL;
		echo "Function 'setImportStartTime' start".PHP_EOL;
		echo "=====================================================================================".PHP_EOL;	
		$this->query("
			UPDATE import_distributors a
			SET
				a.last_start_time = NOW(),
				a.last_status = 'Started'
			WHERE a.distributor_id = '{$distributorId}'
		");
	}

	public function setImportFinishTime($distributorId) {
		echo "=====================================================================================".PHP_EOL;
		echo "Function 'setImportFinishTime' start".PHP_EOL;
		echo "=====================================================================================".PHP_EOL;
		$this->query("
			UPDATE import_distributors a
			SET
				a.last_finish_time = NOW()
			WHERE a.distributor_id = '{$distributorId}'
		");
	}
	
	public function setLastUpdated($distributorId) {
		echo "=====================================================================================".PHP_EOL;
		echo "Function 'setLastUpdated' start".PHP_EOL;
		echo "=====================================================================================".PHP_EOL;
		$this->query("
			UPDATE import_distributors
			SET updated = NOW()
			WHERE distributor_id = {$distributorId}
		");
	}
	
	public function setProductIndexPrice($distributorId,$websiteId) {
		echo "=====================================================================================".PHP_EOL;
		echo "Function 'setProductIndexPrice' start".PHP_EOL;
		echo "=====================================================================================".PHP_EOL;
		$this->query("
			INSERT INTO catalog_product_index_price (
				entity_id,
                customer_group_id,
                website_id,
                tax_class_id,
				price,
                final_price,
                min_price,
                max_price
			)
            SELECT 
				a.entity_id,
                c.customer_group_id, 
                {$websiteId}, 
                2, 
                b.price,
                b.price,
                b.price,
                b.price
            FROM catalog_product_entity a
            INNER JOIN import_distributor_offer_{$distributorId} b
				ON a.distributor_product_id = b.distributor_product_code
                AND a.distributor_id = b.distributor_id
            INNER JOIN customer_group c
            WHERE a.distributor_id = {$distributorId}
				ON DUPLICATE KEY UPDATE
					price = b.price,
					final_price = b.price,
					min_price = b.price,
					max_price = b.price
		");
	}

	public function setCatalogProductWebsite($websiteId) {
		echo "=====================================================================================".PHP_EOL;
		echo "Function 'setCatalogProductWebsite' start".PHP_EOL;
		echo "=====================================================================================".PHP_EOL;
		$this->query("
			REPLACE INTO catalog_product_website (
				product_id,
				website_id
				)
			SELECT 
				a.product_id, 
				'{$websiteId}' 
			FROM catalog_product_website a 
			WHERE a.website_id = {$websiteId};
		");
	}
	
	public function setImportStage($distributorId, $importStage, $lastStatus) {
		echo PHP_EOL."=====================================================================================".PHP_EOL;
		echo "Function 'setImportStage' start".PHP_EOL;
		echo "=====================================================================================".PHP_EOL;
		$this->query("
			UPDATE import_distributors a
			SET
				a.import_stage = '{$importStage}',
				a.last_status = '{$lastStatus}'
			WHERE a.distributor_id = '{$distributorId}'
		");
	}

	public function getOneImportSettings($distributorId) {
		echo "=====================================================================================".PHP_EOL;
		echo "Function 'getOneImportSettings' start".PHP_EOL;
		echo "=====================================================================================".PHP_EOL;
		$result = $this->query("
			SELECT *
			FROM import_distributors_settings a
			WHERE a.distributor_id = '{$distributorId}'
		");
		$row = mysql_fetch_assoc($result);
		if (!$row) {
			return false;
		} else {
			return $row;
		}
	}
	
	public function getDistributorId($code) {
		echo "=====================================================================================".PHP_EOL;
		echo "Function 'getDistributorId' start".PHP_EOL;
		echo "=====================================================================================".PHP_EOL;
		echo "Start import - {$code}".PHP_EOL;
		$result = $this->query("
			SELECT distributor_id
			FROM import_distributors a
			WHERE a.distributor_code = '{$code}'
		");
		$row = mysql_fetch_assoc($result);
		if (!$row) {
			return false;
		} else {
			return $row['distributor_id'];
		}
	}

	public function getOneDistributorInfo($distributorId) {
		echo "=====================================================================================".PHP_EOL;
		echo "Function 'getDistributorId' start".PHP_EOL;
		echo "=====================================================================================".PHP_EOL;
		$result = $this->query("
			SELECT *
			FROM import_distributors a
			WHERE a.distributor_id = {$distributorId}
		");
		if ($row = mysql_fetch_assoc($result)) {
			return $row;
		}
	}

	public function createTemporaryTable($distributorId, $columnsCount) {
		echo "=====================================================================================".PHP_EOL;
		echo "Function 'createTemporaryTable' start".PHP_EOL;
		echo "=====================================================================================".PHP_EOL;		
		if ($columnsCount < 0) {
			throw new Exception("Not enough mapped file fields");
		}
		$columnsArray = array();
		for ($i = 0; $i <= $columnsCount; $i++) {
			$columnsArray[] = "column$i TEXT";
		}
		$columnsString = implode(', ', $columnsArray);
		$this->query("DROP TABLE IF EXISTS import_firstdata_table_{$distributorId}");
		$this->query("CREATE TABLE import_firstdata_table_{$distributorId} ({$columnsString})");
	}

	public function loadDataToTemporaryTable($distributorId, $fileName, $delimiter, $encloser,$lineTerminator, $lines=0) {
		echo "=====================================================================================".PHP_EOL;
		echo "Function 'loadDataToTemporaryTable' start".PHP_EOL;
		echo "=====================================================================================".PHP_EOL;
		$this->query("
			LOAD DATA LOCAL INFILE '{$fileName}'
			INTO TABLE import_firstdata_table_{$distributorId}
			FIELDS TERMINATED BY '{$delimiter}'
			OPTIONALLY ENCLOSED BY '{$encloser}'
			LINES TERMINATED BY '{$lineTerminator}'
			IGNORE {$lines} LINES
		");

      //for ingram_uk : vendor is column1
      $result = $this->query("select distributor_name from import_distributors where distributor_id = " . $distributorId);
      $arr = mysql_fetch_array($result);
      $vendorname = $arr[0];
      
      if ($vendorname == 'ingram_uk') 
          {
           $result = $this->query("DELETE FROM import_firstdata_table_" . $distributorId . " WHERE column0 = 'D'");

           
           $result = $this->query("UPDATE import_firstdata_table_" . $distributorId . " SET column8 = TRIM(LEADING '0' FROM column8)");
           $result = $this->query("UPDATE import_firstdata_table_" . $distributorId . " SET column13 = '' WHERE column13 = '9999999999999'");


           $result = $this->query("UPDATE import_firstdata_table_" . $distributorId . " SET column2 = trim('\t' from column2)");
           $result = $this->query("UPDATE import_firstdata_table_" . $distributorId . " SET column2 = trim('\r' from trim('\n' from column2))");

           $result = $this->query("UPDATE import_firstdata_table_" . $distributorId . " SET column3 = trim(column3)");
           $result = $this->query("UPDATE import_firstdata_table_" . $distributorId . " SET column3 = trim('\t' from column3)");
           $result = $this->query("UPDATE import_firstdata_table_" . $distributorId . " SET column3 = trim('\r' from trim('\n' from column3))");
           $result = $this->query("UPDATE import_firstdata_table_" . $distributorId . " SET column3 = trim(column3)");
           $result = $this->query("DELETE FROM import_firstdata_table_" . $distributorId . " WHERE column3 = '' OR column3 IS NULL");

           $result = $this->query("UPDATE import_firstdata_table_" . $distributorId . " SET column4 = trim(column4)");
           $result = $this->query("UPDATE import_firstdata_table_" . $distributorId . " SET column4 = trim('\t' from column4)");
           $result = $this->query("UPDATE import_firstdata_table_" . $distributorId . " SET column4 = trim('\r' from trim('\n' from column4))");

           $result = $this->query("UPDATE import_firstdata_table_" . $distributorId . " SET column5 = trim(column5)");
           $result = $this->query("UPDATE import_firstdata_table_" . $distributorId . " SET column5 = trim('\t' from column5)");
           $result = $this->query("UPDATE import_firstdata_table_" . $distributorId . " SET column5 = trim('\r' from trim('\n' from column5))");

           $result = $this->query("UPDATE import_firstdata_table_" . $distributorId . " SET column6 = trim('\t' from column6)");
           $result = $this->query("UPDATE import_firstdata_table_" . $distributorId . " SET column6 = trim('\r' from trim('\n' from column6))");

           $result = $this->query("UPDATE import_firstdata_table_" . $distributorId . " SET column7 = trim(column7)");
           $result = $this->query("UPDATE import_firstdata_table_" . $distributorId . " SET column7 = trim('\t' from column7)");
           $result = $this->query("UPDATE import_firstdata_table_" . $distributorId . " SET column7 = trim('\r' from trim('\n' from column7))");
           $result = $this->query("UPDATE import_firstdata_table_" . $distributorId . " SET column7 = trim(column7)");
           $result = $this->query("DELETE FROM import_firstdata_table_" . $distributorId . " WHERE column7 = '' OR column7 IS NULL");

           $result = $this->query("UPDATE import_firstdata_table_" . $distributorId . " SET column13 = trim('\t' from column13)");
           $result = $this->query("UPDATE import_firstdata_table_" . $distributorId . " SET column13 = trim('\r' from trim('\n' from column13))");

           $result = $this->query("UPDATE import_firstdata_table_" . $distributorId . " SET column14 = trim('\t' from column14)");
           $result = $this->query("UPDATE import_firstdata_table_" . $distributorId . " SET column14 = trim('\r' from trim('\n' from column14))");


           $result = $this->query("UPDATE import_firstdata_table_" . $distributorId . " SET column5 = CONCAT(TRIM(column4), ' ', TRIM(column5))");
           $result = $this->query("UPDATE import_firstdata_table_" . $distributorId . " SET column1 = SUBSTRING(column1, 1, LOCATE(' - ',column1)) WHERE column1 LIKE '% - %'"); 



           $result = $this->query("UPDATE import_distributor_" . $distributorId . "_stock_table idst
                                   JOIN import_firstdata_table_" . $distributorId . " ift ON idst.distributor_product_code = ift.column3
                                   SET idst.vendor_product_code = ift.column7");

          }

	}
	
	public function createDistributorsTable($distributorId) {
		echo "=====================================================================================".PHP_EOL;
		echo "Function 'createDistributorsTable' start".PHP_EOL;
		echo "=====================================================================================".PHP_EOL;
		$this->query("DROP TABLE IF EXISTS import_distributor_offer_{$distributorId}");
		$this->query("
			CREATE TABLE import_distributor_offer_{$distributorId} (
				id INTEGER(10) UNSIGNED NOT NULL PRIMARY KEY AUTO_INCREMENT,
				distributor_id INTEGER(10),
				product_name VARCHAR(255),
				distributor_product_code VARCHAR(255),
				vendor_product_code VARCHAR(255),
				category VARCHAR(200),
				category_id INTEGER(10),
				vendor VARCHAR(255),
				vendor_id INTEGER(10),
				action VARCHAR(255),
				eta VARCHAR(255),
				eta_number int(10),
            packing_unit VARCHAR(255),
            img_url VARCHAR(255),
				market_price VARCHAR(255),
				price DECIMAL(8, 2),
				stock int(10),
				ean_code VARCHAR(255),
				product_description VARCHAR(1500),
				weight DECIMAL(10, 2),
				icecat_product_id int(11),
				icecat_supplier_id int(11),
				KEY `import_distributor_offer_1_category` (`category`),
				KEY `import_distributor_offer_1_vendor` (`vendor`),
				KEY (`distributor_product_code`),
				KEY (`vendor_product_code`),
				KEY (`vendor_id`)				
			) CHARSET=utf8			
		");
	}

	public function loadDataToDistributorsTable($distributorId, $columnsArray) {
		echo "=====================================================================================".PHP_EOL;
		echo "Function 'loadDataToDistributorsTable' start".PHP_EOL;
		echo "=====================================================================================".PHP_EOL;
		$this->query("ALTER TABLE import_firstdata_table_{$distributorId} ADD COLUMN columnx INT(1);");		
		$this->query("
			INSERT IGNORE INTO import_distributor_offer_{$distributorId} (
				distributor_id,
				product_name,
				distributor_product_code,
				vendor_product_code,
				category,
				vendor,
				vendor_id,
				price,
				stock,
				ean_code,
				product_description,
				weight,
				eta,
				eta_number,
				market_price,
				action
			)
			SELECT
				{$distributorId},
				TRIM(a.column" . $columnsArray['column_product_name'] . "),
				TRIM(a.column" . $columnsArray['column_product_code'] . "),
				TRIM(a.column" . $columnsArray['column_vendor_product_code'] . "),
				TRIM(a.column" . $columnsArray['column_category'] . "),
				TRIM(a.column" . $columnsArray['column_vendor'] . "),
				TRIM(a.column" . $columnsArray['column_action'] . "),
				TRIM(a.column" . $columnsArray['column_price'] . "),
				TRIM(a.column" . $columnsArray['column_stock'] . "),
				TRIM(a.column" . $columnsArray['column_ean_code'] . "),
				TRIM(a.column" . $columnsArray['column_description'] . "),
				TRIM(a.column" . $columnsArray['column_weight'] . "),
				TRIM(a.column" . $columnsArray['column_eta'] . "),
				TRIM(a.column" . $columnsArray['column_eta_number'] . "),
				TRIM(a.column" . $columnsArray['column_market_price'] . "),
				TRIM(a.column" . $columnsArray['column_action'] . ")
			FROM import_firstdata_table_{$distributorId} a
		");

      //for ingram_uk
      $result = $this->query("select distributor_name from import_distributors where distributor_id = " . $distributorId);
      $arr = mysql_fetch_array($result);
      $vendorname = $arr[0];
      
      if ($vendorname == 'ingram_uk') 
          {
           $result = $this->query("UPDATE import_distributor_offer_{$distributorId} ido
                                   JOIN import_distributor_" . $distributorId . "_stock_table idst USING(distributor_product_code)
                                   SET ido.stock = COALESCE(idst.stock,0)");
          }
	}
		
	public function transferProducts($distributorId) {
		echo "=====================================================================================".PHP_EOL;
		echo "Function 'transferProducts'(GenericModel.php) start".PHP_EOL;
		echo "=====================================================================================".PHP_EOL;
		$this->query("
			DELETE a
			FROM import_distributor_offer a
			WHERE a.distributor_id = {$distributorId}
		");
        $this->query("
			INSERT IGNORE INTO import_distributor_offer(
				distributor_id,
				product_name,
				distributor_product_code,
				vendor_product_code,
				category,
				category_id,
				vendor,
				vendor_id,
				price, 
				stock,
				ean_code,
				product_description,
				weight,
				eta,
				eta_number,
				market_price,
				action,
				best
			)
			SELECT
			   {$distributorId},
				product_name,
				distributor_product_code,
				vendor_product_code,
				category,
				category_id,
				vendor,
				vendor_id,
				price, 
				stock,
				ean_code,
				product_description,
				weight,
				eta,
				eta_number,
				market_price,
				action,
				0
			FROM import_distributor_offer_{$distributorId}
		");
		//$this->applyPriceRules($distributorId);
		$this->query("
			UPDATE import_distributor_offer
			SET euprice = price
			WHERE euprice IS NULL
		");
	}

	public function applyPriceRules($distributorId) {
		echo "=====================================================================================".PHP_EOL;
		echo "Function 'applyPriceRules' start".PHP_EOL;
		echo "=====================================================================================".PHP_EOL;
		$pricerulesArray = $this->_getPricerulesList($distributorId);
		$i=1;
		foreach($pricerulesArray as $pricerule) {
			$where = "";
			if (empty($pricerule['marge'])) $marge = "NULL";
			else $marge = $pricerule['marge'];

			if (empty($pricerule['fixed'])) $fixed = "NULL";
			else $fixed = $pricerule['fixed'];

			if (empty($pricerule['final_price'])) $final_price = "NULL";
			else $final_price = $pricerule['final_price'];

			if (!empty($pricerule['price_from'])) $where.= " AND a.price > ".$pricerule['price_from'];

			if (!empty($pricerule['price_to'])) $where.= " AND a.price < ".$pricerule['price_to'];

			if (!empty($pricerule['vendor_id'])) $where.= " AND vendor_id = ".$pricerule['vendor_id'];

		  //if(!empty($pricerule['vendor_product_id']))
		  //  $where.= " AND vendor_product_id = ".$pricerule['vendor_product_id'];

			if (!empty($pricerule['vendor_product_id'])) $where.= " AND vendor_product_code = '".$pricerule['vendor_product_id']."'";

			if (!empty($pricerule['category_id'])) $where.= " AND category_id = ".$pricerule['category_id'];

			if (!empty($pricerule['store_id'])) $where.= " AND store_id = ".$pricerule['store_id'];

			if (!empty($pricerule['distributor_id'])) $where.= " AND distributor_id = ".$pricerule['distributor_id'];

			$this->createCalcPriceFunc();

			$this->query("
				UPDATE import_distributor_offer a
				SET a.euprice = (SELECT func_calc_price(a.price,
					".$marge." ,
					".$fixed.",
					".$final_price."))
				WHERE a.euprice IS NULL
					".$where
			);
		} 
	}

	protected function _getPricerulesList($distributorId) {
		$rulesArray = array();
		$result = $this->query("
			SELECT *
			FROM import_pricerules
			ORDER BY rating DESC
		");
		while($row = mysql_fetch_assoc($result)) {
			$rulesArray[$row['id']] = $row;
		}
		return $rulesArray;
	}

	public function createCalcPriceFunc() {
		echo "=====================================================================================".PHP_EOL;
		echo "Function 'createCalcPriceFunc' start".PHP_EOL;
		echo "=====================================================================================".PHP_EOL;
		$this->query("DROP FUNCTION IF EXISTS func_calc_price");
		$this->query("
			CREATE FUNCTION func_calc_price(price decimal(8,2) , marge decimal(10,2), fixed decimal(10,2), final_price decimal(10,2)) RETURNS decimal(8,2)
				BEGIN
					IF marge IS NOT NULL THEN
						RETURN price + price * marge / 100;
					END IF;
					IF fixed IS NOT NULL THEN
						RETURN price + fixed;
					END IF;
					IF final_price IS NOT NULL THEN
						RETURN final_price;
					END IF;
					RETURN price;
				  END
		");
	}

	public function applyMappings($distributorId) {
		echo "======================================================================================".PHP_EOL;
		echo "Function 'applyMappings' start...".PHP_EOL;
		echo "======================================================================================".PHP_EOL;
		$this->query("
			REPLACE INTO mappings_vendor (
				source_vendor_name,
				distributor_id	
			)
			SELECT
				DISTINCT(b.vendor),
				{$distributorId}	
			FROM import_distributor_offer_{$distributorId} b
			WHERE b.vendor NOT IN (
				SELECT a.source_vendor_name
				FROM mappings_vendor a
				WHERE a.distributor_id = '{$distributorId}'
			)
		");		
		//add new vendors, which came from distri and didn't exist in vendor mapping table
		$this->query("DROP TABLE IF EXISTS magento_brands");
		$this->query("
			CREATE TABLE magento_brands (
				vendor_id int(10) DEFAULT NULL,
				name varchar(255) NOT NULL,
				UNIQUE KEY vendor_id (vendor_id)
			) ENGINE=MyISAM DEFAULT CHARSET=latin1
		");
		$this->query("
			INSERT IGNORE INTO magento_brands 
			SELECT 
				b.option_id, 
				b.value 
			FROM  eav_attribute_option a 
			INNER JOIN eav_attribute_option_value b  
				ON (a.option_id = b.option_id 
				AND a.attribute_id = {$this->_getProductAttributeId('vendor_id')})
		");
		$this->query("
			UPDATE mappings_vendor a 
			INNER JOIN magento_brands b 
				ON (a.source_vendor_name = b.name and a.distributor_id = {$distributorId}) 
			SET a.vendor_id = b.vendor_id
		");
		$this->query("
			UPDATE import_distributor_offer_{$distributorId} a
			LEFT JOIN mappings_vendor b
				ON a.vendor = b.source_vendor_name
			AND b.distributor_id = '{$distributorId}'
			SET a.vendor_id = b.vendor_id
			WHERE b.vendor_id IS NOT NULL
		");
		//remove unmapped and zomby categories
		$this->query("
			DELETE a FROM mappings_category a
			LEFT JOIN catalog_category_entity b 
			ON a.category_id = b.entity_id 
			WHERE b.entity_id IS NULL AND a.distributor_id = '{$distributorId}'
		");
		//$query = "UPDATE mappings_category SET category_id = NULL WHERE category_id = 0";
        //$result = $this->query($query, __LINE__);
		$this->query("
			REPLACE INTO mappings_category (
				source_category_name,
				distributor_id
			)
			SELECT
				DISTINCT(category),
				'{$distributorId}'
			FROM import_distributor_offer_{$distributorId}
			WHERE category NOT IN (
				SELECT a.source_category_name
				FROM mappings_category a
				WHERE a.distributor_id = '{$distributorId}'
			)
		");							
		$this->query("
			UPDATE import_distributor_offer_{$distributorId} a
			LEFT JOIN mappings_category b
			ON a.category = b.source_category_name
			AND b.distributor_id = '{$distributorId}'
			SET a.category_id = b.category_id
			WHERE b.category_id IS NOT NULL
		");
		/**icecat***/
		/*	
		$this->query("
			UPDATE import_distributor_offer_{$distributorId} ido 
			INNER JOIN icecat_products_mapping ipm
				ON ido.vendor_product_code = ipm.m_prod_id 
				AND ido.vendor_id = ipm.vendor_id
			SET ido.vendor_product_code = ipm.prod_id
		");
		$this->query("
			UPDATE import_distributor_offer_{$distributorId} a 
			JOIN mappings_vendor mv ON (a.vendor_id = mv.vendor_id AND mv.distributor_id = 1) 
			JOIN icecat_suppliers_list isl ON (isl.name = mv.source_vendor_name) SET a.icecat_supplier_id = isl.supplier_id
		");
		$this->query("
			UPDATE import_distributor_offer_{$distributorId} a 
			INNER JOIN icecat_products b 
				ON (a.icecat_supplier_id = b.supplier_id 
				AND a.vendor_product_code = b.mpn) 
			SET a.icecat_product_id = b.product_id
		");
		* */
	}
	
	public function applyIcecatCategoryMappings($distributorId) {
		echo "======================================================================================".PHP_EOL;
		echo "Function 'applyIcecatCategoryMappings' start...".PHP_EOL;
		echo "======================================================================================".PHP_EOL;


		$this->query("create index idx_vendor_vendor_product_code_category_id on import_distributor_offer_{$distributorId}( distributor_product_code, category_id)");

		$this->query("
			UPDATE import_distributor_offer_{$distributorId} a
			INNER JOIN icecat_suppliers_list b
				ON a.vendor = b.name  
			INNER JOIN icecat_product_list d  
				ON b.supplier_id =  d.supplier_id  
				AND a.vendor_product_code = d.prod_id  
			INNER JOIN icecat_categories_name c 
				ON c.category_id = d.category_id  
				AND c.language_id = 1 
			INNER JOIN catalog_category_entity_varchar e  
				ON c.value = e.value 
				AND e.attribute_id = {$this->_getCategoryAttributeId('name')} 
			SET a.category_id = e.entity_id;
		");
	}

	protected function yesAvailability() {
		$av = 0;
		$attr = $this->_getProductAttributeId('availability');    
		$connection = Mage::getModel('core/resource')->getConnection('core_write');                     
		$query = $connection->select()->from(array('a' => 'eav_attribute_option'))
												->join(array('b' => 'eav_attribute_option_value'), 'a.option_id = b.option_id')
												->where('a.attribute_id = ?', $attr)
												->where('b.value = \'yes\'');
		//$res = $connection->fetchOne($query);
		// $this->_yesAvailability = $res['option_id'];
		$query = $connection->query($query);
		if ($row = $query->fetch(PDO::FETCH_ASSOC)) {
			$av = $row['option_id'];
		}
		//  else die("Exception: " . __CLASS__ . "::" . __FUNCTION__);
		return $av;
	}

	protected function noAvailability() {
		echo "======================================================================================".PHP_EOL;
		echo "Function 'noAvailability' start...".PHP_EOL;
		echo "======================================================================================".PHP_EOL;
		$av = 0;
		$attr = $this->_getProductAttributeId('availability');
		$connection = Mage::getModel('core/resource')->getConnection('core_write');
		$query = $connection->select()->from(array('a' => 'eav_attribute_option'))
												->join(array('b' => 'eav_attribute_option_value'), 'a.option_id = b.option_id')
												->where('a.attribute_id = ?', $attr)
												->where('b.value = \'no\'');
		//$res = $connection->fetchOne($query);
		//$this->_noAvailability = $res['option_id'];
		$query = $connection->query($query);
		if ($row = $query->fetch(PDO::FETCH_ASSOC)) {
			$av = $row['option_id'];
        } else { 
			//				die("Exception: " . __CLASS__ . "::" . __FUNCTION__);
		}
		return $av;
	}

	public function setCatalogProductIndexEav($distributorId, $storeId) {
		echo "======================================================================================".PHP_EOL;
		echo "Function 'setCatalogProductIndexEav' start...".PHP_EOL;
		echo "======================================================================================".PHP_EOL;
		$this->query("
			INSERT IGNORE INTO catalog_product_index_eav  (
				entity_id,
				attribute_id,
				store_id,
				value
				)
			SELECT 
				a.entity_id, 
				'{$this->_getProductAttributeId('vendor_id')}', 
				'{$storeId}', 
				b.vendor_id 
			FROM catalog_product_entity a
			INNER JOIN import_distributor_offer b
				ON a.distributor_product_id = b.distributor_product_code
			WHERE a.distributor_id = '$distributorId'	
		");
	}
	
	public function setCatalogProductEntityInt($distributorId, $storeId) {
		echo "======================================================================================".PHP_EOL;
		echo "Function 'setCatalogProductEntityInt' start...".PHP_EOL;
		echo "======================================================================================".PHP_EOL;
		
	$result = $this->query("
	INSERT IGNORE catalog_product_entity_int (
   	entity_type_id,
      attribute_id,
      store_id,
      entity_id,
      value
		)
		SELECT 
			{$this->_getProductEntityTypeId()}, 
			'{$this->_getProductAttributeId('vendor_id')}', 
			'{$storeId}',
			a.entity_id, 
			b.vendor_id
		FROM catalog_product_entity a
		INNER JOIN import_distributor_offer b
        ON (a.vendor_id = b.vendor_id AND a.vendor_product_id = b.vendor_product_code)
        WHERE a.distributor_id = '$distributorId'
		  ");
		
				
	/*$result = $this->query("
	INSERT IGNORE catalog_product_entity_int (
   	entity_type_id,
      attribute_id,
      store_id,
      entity_id,
      value
	)
	SELECT
   {$this->_getProductEntityTypeId()},
	{$this->_getProductAttributeId('availability')}, 
	{$storeId}, 
	a.entity_id, 
	IF(b.qty = 0,{$this->noAvailability()},{$this->yesAvailability()}) AS availability 
	FROM catalog_product_entity a
	INNER JOIN `cataloginventory_stock_item`  b
        ON a.entity_id = b.product_id
	WHERE a.distributor_id = '$distributorId'
		  ");*/			  	  
	}

	public function addProduct($distributorId,$websiteId) {
		echo "======================================================================================".PHP_EOL;
		echo "Function 'addProduct' start".PHP_EOL;
		echo "======================================================================================".PHP_EOL;
		//Setting old products disabled (but not deleted).
		
		$count = $this->query("
			SELECT COUNT(*)
			FROM catalog_product_entity_int a
			INNER JOIN catalog_product_entity b
			ON a.entity_id = b.entity_id
			AND b.distributor_id = {$distributorId}
			AND a.attribute_id = {$this->_getProductAttributeId('status')}
			WHERE store_id = {$websiteId}
		");
		
		$count = mysql_fetch_array($count);
		$all_count = (int) $count[0];
		
		echo "function addProduct".PHP_EOL;
		echo "all_count = {$all_count}".PHP_EOL;
		echo "websiteId = {$websiteId}".PHP_EOL;

		if ($all_count > 0) {
			$a = 0;
			while ($a <=  $all_count) {
				$result = $this->query("
				DELETE FROM catalog_product_entity_int  
				WHERE attribute_id = {$this->_getProductAttributeId('status')} 
					AND store_id = " . $websiteId . " 
					AND entity_id IN (
				SELECT entity_id 
				FROM catalog_product_entity 
				WHERE distributor_id = " . $distributorId . "
				)  
				LIMIT 1000
				");
				$a = $a+1000;	
			}
		}

		// execute only once. it don't have store product configurator
		if (!$this->isAddProduct) {
			$this->isAddProduct = true;
			//Inserting new products and updating old others.
			$result = $this->query("
				INSERT INTO catalog_product_entity (
					entity_type_id,
					attribute_set_id,
					type_id,
					sku,
					updated_at,
					has_options,
					distributor_product_id,
					distributor_id,
					vendor_product_id,
					vendor_id
				)
				SELECT
					{$this->_getProductEntityTypeId()},
					{$this->_getDefaultAttributeSetId('catalog_product')},
					'simple',
					CONCAT(a.distributor_id, '_', a.distributor_product_code),
					NOW(),
					0,
					a.distributor_product_code,
					a.distributor_id,
					a.vendor_product_code,
					a.vendor_id
				FROM import_distributor_offer a
				WHERE a.vendor_id IS NOT NULL
					AND a.distributor_id = {$distributorId}
					AND vendor_id != 0
				ON DUPLICATE KEY UPDATE
					updated_at = NOW(),
					vendor_product_id = a.vendor_product_code,
					vendor_id = a.vendor_id
			");

			$result = $this->query("
				DELETE a FROM catalog_product_entity a
				LEFT JOIN import_distributor_offer b
				ON a.distributor_id = b.distributor_id AND a.distributor_product_id = b.distributor_product_code
				WHERE b.distributor_product_code IS NULL;
			");
		}
	}

	public function setEnable($distributorId,$websiteId) {
		//Set enabled
		echo "=====================================================================================".PHP_EOL;
		echo "Function 'setEnable' start".PHP_EOL;
		echo "=====================================================================================".PHP_EOL;
		$result = $this->query("
			INSERT IGNORE INTO catalog_product_entity_int (
				entity_type_id,
				attribute_id,
				store_id,
				entity_id,
				value
			)
			SELECT
				{$this->_getProductEntityTypeId()},
				{$this->_getProductAttributeId('status')},
				{$websiteId},
				a.entity_id,
				1
			FROM catalog_product_entity a
			INNER JOIN import_distributor_offer b
				ON a.distributor_id = b.distributor_id
				AND a.distributor_product_id = b.distributor_product_code
			WHERE a.distributor_id = '$distributorId'
		");
	}

	public function updateProductsDistriselectorStatus($distributorId,$websiteId) {
		echo "=====================================================================================".PHP_EOL;
		echo "Function 'updateProductsDistriselectorStatus' start".PHP_EOL;
		echo "=====================================================================================".PHP_EOL;
			$this->query("
				UPDATE catalog_product_entity_int a
				INNER JOIN catalog_product_entity b using(entity_id)
				JOIN import_distributor_offer c
					ON b.distributor_id = c.distributor_id
					AND b.distributor_product_id = c.distributor_product_code
				SET value = 2
				WHERE c.best = 'N' AND a.attribute_id = {$this->_getProductAttributeId('status')} AND a.store_id = {$websiteId}
			");			
			$this->query("
				UPDATE catalog_product_entity_int a
				INNER JOIN catalog_product_entity b using(entity_id)
				JOIN import_distributor_offer c
					ON b.distributor_id = c.distributor_id
					AND b.distributor_product_id = c.distributor_product_code
				SET value = 1
				WHERE c.best = 'N' AND a.attribute_id = {$this->_getProductAttributeId('visibility')} AND a.store_id = {$websiteId}
			");		
			$this->query("
				UPDATE catalog_product_entity_int a
				INNER JOIN catalog_product_entity b using(entity_id)
				JOIN import_distributor_offer c
					ON b.distributor_id = c.distributor_id
					AND b.distributor_product_id = c.distributor_product_code
				SET value = 1
				WHERE c.best = 'Y' AND a.attribute_id = {$this->_getProductAttributeId('status')} AND a.store_id = {$websiteId}
			");		
			$this->query("
				UPDATE catalog_product_entity_int a
				INNER JOIN catalog_product_entity b using(entity_id)
				JOIN import_distributor_offer c
					ON b.distributor_id = c.distributor_id
					AND b.distributor_product_id = c.distributor_product_code
				SET value = 4
				WHERE c.best = 'Y' AND a.attribute_id = {$this->_getProductAttributeId('visibility')} AND a.store_id = {$websiteId}
			");
	}

	public function clearCategories($distributorId)	{
		echo "======================================================================================".PHP_EOL;
		echo "Function 'clearCategories' start".PHP_EOL;
		echo "======================================================================================".PHP_EOL;
		$this->query("
			DELETE x FROM catalog_category_product x
			INNER JOIN catalog_product_entity a
				ON a.entity_id = x.product_id AND a.distributor_id = {$distributorId}
		 ");
	}

	public function applyCategories($distributorId,$websiteId) {
		echo "======================================================================================".PHP_EOL;
		echo "Function 'applyCategories' start".PHP_EOL;
		echo "======================================================================================".PHP_EOL;

		if (!$this->isApplyCategories) {
			$this->isApplyCategories = true;

			//Unifying products with categories.
			$this->query("
				INSERT IGNORE INTO catalog_category_product (
					category_id,
					product_id,
					position
				)
				SELECT
					b.category_id,
					a.entity_id,
					'1'
				FROM catalog_product_entity a
				INNER JOIN import_distributor_offer b
					ON  a.distributor_product_id = b.distributor_product_code
					AND b.distributor_id = a.distributor_id
				WHERE b.category_id IS NOT NULL
					AND b.distributor_id = '{$distributorId}'
			");
			// add all products to default category
			$defaultCategoryId = 2;
			$this->query("
				INSERT IGNORE INTO catalog_category_product (
					category_id,
					product_id,
					position
				)
				SELECT
					{$defaultCategoryId},
					a.entity_id,
					'1'
				FROM catalog_product_entity a
			");
			//Indexing products and categories in the shop
			$this->query("
				INSERT IGNORE INTO catalog_category_product_index (
					category_id,
					product_id,
					position,
					is_parent,
					store_id,
					visibility
				)
				SELECT
					a.category_id,
					a.product_id,
					a.position,
					1,
					b.store_id,
					4
				FROM catalog_category_product a
				INNER JOIN core_store b
				WHERE b.store_id = '{$websiteId}' 
			");
		}
	}

	public function applyCategoriesForStores($distributorId,$storeId) {
		echo "======================================================================================".PHP_EOL;
		echo "Function 'applyCategoriesForStores' start".PHP_EOL;
		echo "======================================================================================".PHP_EOL;
			$this->query("
				INSERT IGNORE INTO catalog_category_product_index (
					category_id,
					product_id,
					position,
					is_parent,
					store_id,
					visibility
				)
				SELECT
					a.category_id,
					a.product_id,
					a.position,
					1,
					b.store_id,
					4
				FROM catalog_category_product a
				INNER JOIN core_store b
				WHERE b.store_id = '{$storeId}' 
			");
	}	

	public function addName($distributorId,$storeId) {
		echo "=====================================================================================".PHP_EOL;
		echo "Function 'addName' start".PHP_EOL;
		echo "=====================================================================================".PHP_EOL;
		if (!$this->isAddName) {
			$this->isAddName = true;
		}
		//Set product name
		$this->query("
			INSERT INTO catalog_product_entity_varchar (
				entity_type_id,
				attribute_id,
				store_id,
				entity_id,
				value
			)
			SELECT
				{$this->_getProductEntityTypeId()},
				{$this->_getProductAttributeId('name')},
				{$storeId},
				a.entity_id,
				b.product_name
			FROM catalog_product_entity a
			INNER JOIN import_distributor_offer b
				ON a.distributor_product_id = b.distributor_product_code
				AND b.distributor_id = a.distributor_id
			WHERE	b.distributor_id = '{$distributorId}'
			ON DUPLICATE KEY UPDATE
				attribute_id = {$this->_getProductAttributeId('name')},
				store_id = {$storeId},
				value = b.product_name
		");
	}

	public function addDescription($distributorId,$storeId) {
		echo "=====================================================================================".PHP_EOL;
		echo "Function 'addDescription' start".PHP_EOL;
		echo "=====================================================================================".PHP_EOL;
		$this->query("
			INSERT INTO catalog_product_entity_varchar (
				entity_type_id,
				attribute_id,
				store_id,
				entity_id,
				value
			)
			SELECT
				{$this->_getProductEntityTypeId()},
				{$this->_getProductAttributeId('description')},
				{$storeId},
				a.entity_id,
				b.product_description
			FROM catalog_product_entity a
         	INNER JOIN import_distributor_offer b
				ON a.distributor_product_id = b.distributor_product_code
				AND b.distributor_id = a.distributor_id
			WHERE	b.distributor_id = '{$distributorId}'
			ON DUPLICATE KEY UPDATE
				attribute_id = {$this->_getProductAttributeId('description')},
				store_id = {$storeId},
				value = b.product_description
		");
   }

	public function addShortDescription($distributorId,$storeId) {
		echo "=====================================================================================".PHP_EOL;
		echo "Function 'addShortDescription' start".PHP_EOL;
		echo "=====================================================================================".PHP_EOL;
		$this->query("
			INSERT INTO catalog_product_entity_varchar (
				entity_type_id,
				attribute_id,
				store_id,
				entity_id,
				value
			)
			SELECT
				{$this->_getProductEntityTypeId()},
				{$this->_getProductAttributeId('short_description')},
				{$storeId},
				a.entity_id,
				b.product_description
			 FROM catalog_product_entity a
				INNER JOIN import_distributor_offer b
				ON a.distributor_product_id = b.distributor_product_code
				AND b.distributor_id = a.distributor_id
			 WHERE b.distributor_id = '{$distributorId}'
			 ON DUPLICATE KEY UPDATE
				attribute_id = {$this->_getProductAttributeId('short_description')},
				store_id = {$storeId},
				value = b.product_description
		");
   }

	public function addIcecatName($distributorId,$storeId) {
		$this->query("
			INSERT INTO catalog_product_entity_varchar (
				entity_type_id,
				attribute_id,
				store_id,
				entity_id,
				value
			)
			SELECT
				{$this->_getProductEntityTypeId()},
				{$this->_getProductAttributeId('name')},
				{$storeId},
				a.entity_id,
				b.name
			FROM catalog_product_entity a
			INNER JOIN icecat_products b
				ON a.icecat_product_id = b.product_id
				WHERE a.distributor_id = '{$distributorId}'	
			ON DUPLICATE KEY UPDATE
				attribute_id = {$this->_getProductAttributeId('name')},
				store_id = {$storeId},
				value = b.name
		");
   }

	public function addIcecatDescription($distributorId,$storeId) {
		//adding Icecat Descriptions
		$result = $this->query("
			INSERT INTO catalog_product_entity_varchar (
				entity_type_id,
				attribute_id,
				store_id,
				entity_id,
				value
			)
			SELECT
				{$this->_getProductEntityTypeId()},
				{$this->_getProductAttributeId('description')},
				{$storeId},
				a.entity_id,
				b.short_description
			FROM catalog_product_entity a
			INNER JOIN icecat_products_description b
				ON a.icecat_product_id = b.product_id
				WHERE	a.distributor_id = '{$distributorId}'	
			ON DUPLICATE KEY UPDATE
				attribute_id = {$this->_getProductAttributeId('description')},
				store_id = {$storeId},
				value = b.short_description
		");
   }

	public function setVisibility($distributorId,$storeId) {
		echo "=====================================================================================".PHP_EOL;
		echo "Function 'setVisibility' start".PHP_EOL;
		echo "=====================================================================================".PHP_EOL;
		if (!$this->isSetVisibility) {
			$this->isSetVisibility = true;
		}
		$this->query("
			INSERT IGNORE INTO catalog_product_entity_int (
				entity_type_id,
				attribute_id,
				store_id,
				entity_id,
				value
			)
			SELECT
				{$this->_getProductEntityTypeId()},
				{$this->_getProductAttributeId('visibility')},
				{$storeId},
				a.entity_id,
				4
			FROM catalog_product_entity a
			WHERE a.distributor_id = '{$distributorId}'
		");
	}

	public function enabledIndex($distributorId,$storeId) {
		echo "=====================================================================================".PHP_EOL;
		echo "Function 'enabledIndex' start".PHP_EOL;
		echo "=====================================================================================".PHP_EOL;
		if (!$this->isEnabledIndex) {
			$this->isEnabledIndex = true;
			//Enabling product index.
			$this->query("
				INSERT INTO catalog_product_enabled_index (
					product_id,
					store_id,
					visibility
				)
				SELECT
					a.entity_id,
					b.store_id,
					4
				FROM catalog_product_entity a
				INNER JOIN core_store b
					WHERE b.store_id <> 0
						AND a.distributor_id = '{$distributorId}'
				ON DUPLICATE KEY UPDATE visibility = 4
			");
		}
	}

	public function addProductForWebsite($distributorId,$websiteId) {
		echo "=====================================================================================".PHP_EOL;
		echo "Function 'addProductForWebsite' start".PHP_EOL;
		echo "=====================================================================================".PHP_EOL;
		$this->query("
			INSERT IGNORE INTO catalog_product_website (
				product_id,
				website_id
			)
			SELECT a.entity_id, {$websiteId}
			FROM catalog_product_entity a
			WHERE a.distributor_id = '{$distributorId}'
		");
	}

	public function addStock($distributorId,$websiteId) {
		echo "=====================================================================================".PHP_EOL;
		echo "Function 'addStock' start".PHP_EOL;
		echo "=====================================================================================".PHP_EOL;
		$this->query("
			INSERT INTO cataloginventory_stock_item (
				product_id,
				stock_id,
				is_in_stock,
				manage_stock,
				qty
			)
			SELECT
				a.entity_id,
				1,
				IF(COALESCE(b.stock,0) > 0, 1, 0),
				1,
				COALESCE(b.stock,0)
			FROM catalog_product_entity a
			INNER JOIN import_distributor_offer b
				ON a.distributor_product_id = b.distributor_product_code
				AND a.distributor_id = b.distributor_id
			WHERE a.distributor_id = {$distributorId}
				ON DUPLICATE KEY UPDATE
					qty = COALESCE(b.stock,0),
					is_in_stock = IF(COALESCE(b.stock,0) > 0, 1, 0),
					stock_id = 1,
					manage_stock = 1
		");		// is_in_stock, IF(COALESCE(b.stock,0) > 0, 1, 0),
		$this->query("
			INSERT INTO cataloginventory_stock_status (
				product_id,
				website_id,
				stock_id,
				stock_status,
				qty
			)
			SELECT
				product_id,
				{$websiteId},
				1,
				1,
				a.qty
			FROM cataloginventory_stock_item a
			ON DUPLICATE KEY UPDATE
				qty = a.qty
		");
	}

	public function addPrice($distributorId,$storeId) {
		echo "=====================================================================================".PHP_EOL;
		echo "Function 'addPrice' start".PHP_EOL;
		echo "=====================================================================================".PHP_EOL;
		if (!$this->isAddPrice) {
			$this->isAddPrice = true;
		}
		$this->query("
			INSERT INTO catalog_product_entity_decimal (
				entity_type_id,
				attribute_id,
				store_id,
				entity_id,
				value
			)
			SELECT
				{$this->_getProductEntityTypeId()},
				{$this->_getProductAttributeId('price')},
				{$storeId},
				a.entity_id,
				b.euprice
			FROM catalog_product_entity	a
			INNER JOIN import_distributor_offer b
				ON a.distributor_product_id = b.distributor_product_code
				AND a.distributor_id = b.distributor_id
			WHERE a.distributor_id = '{$distributorId}'
			ON DUPLICATE KEY UPDATE
				value = b.euprice
		");
	}

	public function setSearchFulltext($storeId) {
		echo "=====================================================================================".PHP_EOL;
		echo "Function 'setSearchFulltext' start".PHP_EOL;
		echo "=====================================================================================".PHP_EOL;
		//Refresh fulltext search
		$this->query("DELETE from catalogsearch_fulltext WHERE store_id = '{$storeId}'");
		$this->query("
			INSERT IGNORE INTO catalogsearch_fulltext (
				product_id, 
				store_id, 
				data_index
			)
			SELECT 
				product_id, 
				'{$storeId}',
				data_index
			FROM catalogsearch_fulltext_tmp
		");
	}

	public function setSearchFulltextTmp() {
		echo "=====================================================================================".PHP_EOL;
		echo "Function 'setSearchFulltextTmp' start".PHP_EOL;
		echo "=====================================================================================".PHP_EOL;
		$this->query("DROP TABLE IF EXISTS catalogsearch_fulltext_tmp");
		$this->query("CREATE TEMPORARY TABLE catalogsearch_fulltext_tmp LIKE catalogsearch_fulltext");
		$this->query("ALTER TABLE catalogsearch_fulltext_tmp DROP COLUMN store_id");
        //TODO add groupconcat for different categories
		$result = $this->query("
			INSERT IGNORE catalogsearch_fulltext_tmp (
				product_id, 
				data_index
			)
			SELECT 
				a.entity_id, 
				REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(CONCAT_WS(' ', a.vendor_product_id, c.value, d.value, e.value), '|', 'bar'), '\"', 'quote'), '\'', 'apos'), '#', 'num'), '\\\\', 'backslash'), '/', 'slash'), '.', 'dot'), '=', 'equality'), '-', 'minus'), '+', 'plus')
			FROM catalog_product_entity a
			INNER JOIN catalog_category_product b 
				ON a.entity_id = b.product_id
			INNER JOIN catalog_category_entity_varchar c 
				ON b.category_id = c.entity_id 
				AND c.attribute_id = {$this->_getCategoryAttributeId('name')}
			INNER JOIN eav_attribute_option_value d 
				ON a.vendor_id = d.option_id
			INNER JOIN catalog_product_entity_varchar e 
				ON a.entity_id = e.entity_id 
				AND e.attribute_id = {$this->_getProductAttributeId('name')}
         ");
	}	

	public function addCost($distributorId,$storeId) {
		echo "=====================================================================================".PHP_EOL;
		echo "Function 'addCost' start".PHP_EOL;
		echo "=====================================================================================".PHP_EOL;
		if (!$this->isAddCost) {
			$this->isAddCost = true;
			//add cost attribute
			$result = $this->query("
				DELETE x FROM catalog_product_entity_decimal x
				INNER JOIN catalog_product_entity a
					ON x.entity_id = a.entity_id
				INNER JOIN import_distributor_offer b
					ON a.distributor_product_id = b.distributor_product_code
					AND a.distributor_id = b.distributor_id
				WHERE attribute_id ={$this->_getProductAttributeId('cost')}
			");
		}
		$this->query("
			REPLACE INTO catalog_product_entity_decimal (
				entity_type_id,
				attribute_id,
				store_id,
				entity_id,
				value
			)
			SELECT
				{$this->_getProductEntityTypeId()},
				{$this->_getProductAttributeId('cost')},
				{$storeId},
				a.entity_id,
				b.price
			FROM catalog_product_entity	a
			INNER JOIN import_distributor_offer b
				ON a.distributor_product_id = b.distributor_product_code
				AND a.distributor_id = b.distributor_id
			WHERE b.distributor_id = '{$distributorId}'
		");
	}

	public function addTaxClass($distributorId,$storeId) {
		echo "=====================================================================================".PHP_EOL;
		echo "Function 'addTaxClass' start".PHP_EOL;
		echo "=====================================================================================".PHP_EOL;
		if (!$this->isAddTaxClass) {
			$this->isAddTaxClass = true;
		}
		$this->query("
			INSERT IGNORE INTO catalog_product_entity_int (
				entity_type_id,
				attribute_id,
				store_id,
				entity_id,
				value
			)
			SELECT
			{$this->_getProductEntityTypeId()},
			{$this->_getProductAttributeId('tax_class_id')},
			{$storeId},
			entity_id,
			2
			FROM catalog_product_entity
			WHERE distributor_id = '{$distributorId}'
		");
	}

	public function addWeight($distributorId,$storeId) {
		echo "=====================================================================================".PHP_EOL;
		echo "Function 'addWeight' start".PHP_EOL;
		echo "=====================================================================================".PHP_EOL;
		if (!$this->isAddWeight) {
			$this->isAddWeight = true;
			//Add weight value
		}
		$this->query("
			INSERT INTO catalog_product_entity_decimal (
				entity_type_id,
				attribute_id,
				store_id,
				entity_id,
				value
			)
			SELECT
				{$this->_getProductEntityTypeId()},
				{$this->_getProductAttributeId('weight')},
				{$storeId},
				a.entity_id,
				b.weight
			FROM catalog_product_entity a
			INNER JOIN import_distributor_offer b
				ON a.distributor_product_id = b.distributor_product_code
				AND a.distributor_id = b.distributor_id
			WHERE a.distributor_id = '{$distributorId}'
			ON DUPLICATE KEY UPDATE
				value = b.weight
		");
	}

	public function replaceMagentoProducts($distributorId) {
		echo "=====================================================================================".PHP_EOL;
		echo "Function 'replaceMagentoProducts' start".PHP_EOL;
		echo "=====================================================================================".PHP_EOL;
		//remove vendor from product name
        $this->query("
			UPDATE import_distributor_offer
            SET product_name =
				IF(LOCATE(vendor, product_name) = 1,
                SUBSTR(product_name, CHAR_LENGTH(vendor) + 2),
                product_name
		)");
		$this->clearCategories($distributorId);
		$result = $this->query("SELECT * FROM core_store ");
		if (!$result) {
			echo 'Error: Stores not found!'.PHP_EOL;
			return false;
		}
		//Filling temporary search table for all stores
		while ( $store = mysql_fetch_assoc($result) ) {
			$storeId = $store ['store_id'];
			$this->addProduct($distributorId,$storeId);
			$this->setEnable($distributorId,$storeId);
			$this->setVisibility($distributorId,$storeId);//
			$this->addStock($distributorId,$storeId);//
			$this->updateProductsDistriselectorStatus($distributorId,$storeId);
			$this->applyCategories($distributorId,$storeId); 
			$this->addName($distributorId,$storeId);				
			$this->addDescription($distributorId,$storeId);
			$this->addShortDescription($distributorId,$storeId);
			$this->enabledIndex($distributorId,$storeId);
			$this->addPrice($distributorId,$storeId);
			$this->addCost($distributorId,$storeId);
			$this->addTaxClass($distributorId,$storeId);
			$this->addWeight($distributorId,$storeId);
			$this->setSearchFulltextTmp();
			$this->setSearchFulltext($storeId);
			//$this->addStock($distributorId,'0');
			$this->setCatalogProductIndexEav($distributorId,$storeId);
			$this->setCatalogProductEntityInt($distributorId,$storeId);
			$this->applyCategoriesForStores($distributorId,$storeId);
		}
		$result = $this->query("SELECT * FROM core_website");
		if (!$result) {
			echo 'Error: Stores not found!'.PHP_EOL;
			return false;
		}
		while ( $website = mysql_fetch_assoc($result)) {
			$websiteId = $website ['website_id'];
			$this->addProductForWebsite($distributorId,$websiteId);
			$this->setProductIndexPrice($distributorId,$websiteId);
			if($websiteId>1){
				$this->setCatalogProductWebsite($websiteId);
			}
			$this->addStock($distributorId,$websiteId);
		}	
	}
}
