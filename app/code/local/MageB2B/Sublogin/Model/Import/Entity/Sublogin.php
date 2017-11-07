<?php
/**
 * @category Customer Service Version 3.0
 * @package MageB2B_Sublogin
 * @author AIRBYTES GmbH <info@airbytes.de>
 * @copyright AIRBYTES GmbH
 * @license commercial
 * @date 09.01.2016
 */
class MageB2B_Sublogin_Model_Import_Entity_Sublogin extends Mage_ImportExport_Model_Import_Entity_Abstract
{
	const COL_ENTITY_ID       = "entity_id";
	const COL_EMAIL           = "email";
	const COL_FIRSTNAME 	  = "firstname";
	const COL_LASTNAME  	  = "lastname";
	const COL_STORE_ID  	  = "store_id";
	const MAX_PASSWD_LENGTH   = 6;
	const DEFAULT_EXPIRY_DAYS = 90;
	
	/**
     * Error codes.
     */
    const ERROR_INVALID_WEBSITE      = 'invalidWebsite';
    const ERROR_INVALID_EMAIL        = 'invalidEmail';
    const ERROR_DUPLICATE_EMAIL_SITE = 'duplicateEmailSite';
    const ERROR_EMAIL_IS_EMPTY       = 'emailIsEmpty';
    const ERROR_ROW_IS_ORPHAN        = 'rowIsOrphan';
    const ERROR_VALUE_IS_REQUIRED    = 'valueIsRequired';
    const ERROR_INVALID_STORE        = 'invalidStore';
    const ERROR_EMAIL_SITE_NOT_FOUND = 'emailSiteNotFound';
    const ERROR_PASSWORD_LENGTH      = 'passwordLength';
	const ERROR_INVALID_ENTITY_ID    = 'invalidEntityId';

	protected $_entityTable;
	protected $_oldSublogins;
	protected $_newSublogins;
	protected $_storeCodeToId;
	protected $_allCustomerIds;
	
	/**
     * Validation failure message template definitions
     *
     * @var array
     */
    protected $_messageTemplates = array(
		self::ERROR_INVALID_ENTITY_ID	 => 'Invalid value in entity_id column (customer does not exists?)',
        self::ERROR_INVALID_WEBSITE      => 'Invalid value in Website column (website does not exists?)',
        self::ERROR_INVALID_EMAIL        => 'E-mail is invalid',
        self::ERROR_DUPLICATE_EMAIL_SITE => 'E-mail is duplicated in import file',
        self::ERROR_EMAIL_IS_EMPTY       => 'E-mail is not specified',
        self::ERROR_ROW_IS_ORPHAN        => 'Orphan rows that will be skipped due default row errors',
        self::ERROR_VALUE_IS_REQUIRED    => "Required attribute '%s' has an empty value",
        self::ERROR_INVALID_STORE        => 'Invalid value in Store column (store does not exists?)',
        self::ERROR_EMAIL_SITE_NOT_FOUND => 'E-mail and website combination is not found',
        self::ERROR_PASSWORD_LENGTH      => 'Invalid password length'
    );

	/**
     * Permanent entity columns.
     *
     * @var array
     */
    protected $_permanentAttributes = array(self::COL_ENTITY_ID, self::COL_EMAIL, self::COL_FIRSTNAME, self::COL_LASTNAME);

	/**
     * Constructor.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
        $this->_initStores()
			->_initCustomers()
			->_initSublogins()
		;
        $this->_entityTable   = Mage::getModel('customer/customer')->getResource()->getTable('sublogin/sublogin');
    }
	
	protected function _initCustomers() {
		$this->_allCustomerIds = Mage::getModel('customer/customer')->getCollection()->getAllIds();
		return $this;
	}
	
	/**
     * Initialize stores hash.
     *
     * @return Mage_ImportExport_Model_Import_Entity_Customer
     */
    protected function _initStores()
    {
        foreach (Mage::app()->getStores(true) as $store) {
            $this->_storeCodeToId[$store->getCode()] = $store->getId();
        }
        return $this;
    }
	
	protected function _initSublogins()
    {
		foreach (Mage::getModel('sublogin/sublogin')->getCollection() as $sublogin) {
            $email = $sublogin->getEmail();
            $entityId = $sublogin->getEntityId(); // that is customer_id
            $storeId = $sublogin->getStoreId();
            if (!isset($this->_oldSublogins[$storeId][$entityId][$email])) {
                $this->_oldSublogins[$storeId][$entityId][$email] = array();
            }
			$this->_oldSublogins[$storeId][$entityId][$email] = $sublogin->getId();
        }
		return $this;
	}

	/**
     * EAV entity type code getter.
     *
     * @abstract
     * @return string
     */
    public function getEntityTypeCode()
    {
        return 'sublogin';
    }

	/**
     * Validate data row.
     *
     * @param array $rowData
     * @param int $rowNum
     * @return boolean
     */
    public function validateRow(array $rowData, $rowNum)
    {
		// 1. check whether email is valid, if yes return true
		// 2. check whether email is already exist for the same customer and store, if yes return false
        if (isset($this->_validatedRows[$rowNum])) {
        // check that row is already validated
            return !isset($this->_invalidRows[$rowNum]);
        }
        $this->_validatedRows[$rowNum] = true;
        $this->_processedEntitiesCount ++;
		$email        = $rowData[self::COL_EMAIL];
        $emailToLower = strtolower($rowData[self::COL_EMAIL]);
		$entityId        = $rowData[self::COL_ENTITY_ID];
		$storeId        = $rowData[self::COL_STORE_ID];
        
        $oldSubloginsToLower = array_change_key_case($this->_oldSublogins, CASE_LOWER);
		$newSubloginsToLower = array_change_key_case($this->_newSublogins, CASE_LOWER);

        // BEHAVIOR_DELETE use specific validation logic
        if (Mage_ImportExport_Model_Import::BEHAVIOR_DELETE == $this->getBehavior()) {
            if(!isset($oldSubloginsToLower[$storeId][$entityId][$email])){
				$this->addRowError(self::ERROR_EMAIL_SITE_NOT_FOUND, $rowNum);
			}
        } else { 
            if (!Zend_Validate::is($email, 'EmailAddress')) {
                $this->addRowError(self::ERROR_INVALID_EMAIL, $rowNum);
            } else {
                if (isset($newSubloginsToLower[$storeId][$entityId][$email])) {
                    $this->addRowError(self::ERROR_DUPLICATE_EMAIL_SITE, $rowNum);
                }
                $this->_newSublogins[$storeId][$entityId][$email] = false;

                if(!empty($rowData[self::COL_STORE_ID]) && !is_numeric($rowData[self::COL_STORE_ID]) && !isset($this->_storeCodeToId[$rowData[self::COL_STORE_ID]]) ){
					$this->addRowError(self::ERROR_INVALID_STORE, $rowNum);
				}elseif( is_numeric($rowData[self::COL_STORE_ID]) && !@in_array($rowData[self::COL_STORE_ID], $this->_storeCodeToId)){
					$this->addRowError(self::ERROR_INVALID_STORE, $rowNum);
				}
				
				// check whether entity id is valid, that means whether it exists or not
				if(!@in_array($entityId, $this->_allCustomerIds)){ // check whether customer id is valid
					$this->addRowError(self::ERROR_INVALID_ENTITY_ID, $rowNum);
				}
				
                // check password
                if (isset($rowData['password']) && strlen($rowData['password'])
                    && Mage::helper('core/string')->strlen($rowData['password']) < self::MAX_PASSWD_LENGTH
                ) {
                    $this->addRowError(self::ERROR_PASSWORD_LENGTH, $rowNum);
                }
            }
        }
        return !isset($this->_invalidRows[$rowNum]);
    }

    /**
     * Save sublogins data to DB.
     *
     * @throws Exception
     * @return bool Result of operation.
     */
    protected function _importData()
    {
        if (Mage_ImportExport_Model_Import::BEHAVIOR_DELETE == $this->getBehavior()) {
            $this->_deleteSublogins();
        } else {
            $this->_saveSublogins();
        }
        return true;
    }

	protected function _deleteSublogins()
    {
		while ($bunch = $this->_dataSourceModel->getNextBunch()) {
            $idToDelete = array();
            foreach ($bunch as $rowNum => $rowData) {
                if ($this->validateRow($rowData, $rowNum))
                {
					$email        = $rowData[self::COL_EMAIL];
					$emailToLower = strtolower($rowData[self::COL_EMAIL]);
					$entityId     = $rowData[self::COL_ENTITY_ID];
					$storeId      = $rowData[self::COL_STORE_ID];
                    $idToDelete[] = $this->_oldSublogins[$storeId][$entityId][$emailToLower];
                }
            }
			if ($idToDelete) {
                $this->_connection->query(
                    $this->_connection->quoteInto(
                        "DELETE FROM `{$this->_entityTable}` WHERE `id` IN (?)", $idToDelete
                    )
                );
            }
        }
        return $this;
	}

    /**
     * @return saving rows of sublogin
     */
	protected function _saveSublogins()
    {
        $resource       = Mage::getModel('sublogin/sublogin');
        $strftimeFormat = Varien_Date::convertZendToStrftime(Varien_Date::DATETIME_INTERNAL_FORMAT, true, true);
        $table = $resource->getResource()->getTable('sublogin/sublogin'); //$resource->getResource()->getEntityTable();
        $nextAutoId   = Mage::getResourceHelper('importexport')->getNextAutoincrement($table);
        while ($bunch = $this->_dataSourceModel->getNextBunch())
        {
			$entityRowsIn = array();
            $entityRowsUp = array();
			
			$oldSubloginsToLower = array_change_key_case($this->_oldSublogins, CASE_LOWER);
			
			foreach ($bunch as $rowNum => $rowData) {
				if (!$this->validateRow($rowData, $rowNum)) {
                    continue;
                }

				$defaultExpireDate = gmstrftime($strftimeFormat, strtotime("+ ".self::DEFAULT_EXPIRY_DAYS." days"));
				$entityId = empty($rowData[self::COL_ENTITY_ID]) ? 0 : $rowData[self::COL_ENTITY_ID];
				$storeId = empty($rowData[self::COL_STORE_ID]) ? 0 : $rowData[self::COL_STORE_ID];
				$email = empty($rowData[self::COL_EMAIL]) ? '' : $rowData[self::COL_EMAIL];
				$emailToLower = strtolower($email);
				
				// save sublogin
				 $entityRow = array(
					'entity_id' 			=> 	$entityId,
					'customer_id' 			=> 	empty($rowData['customer_id']) ? '' : $rowData['customer_id'],
					'email' 				=> 	$emailToLower,
					'password' 				=> 	empty($rowData['password']) ? Mage::helper('core')->getHash('123456', 2) : Mage::helper('core')->getHash($rowData['password'], 2),
					'rp_token' 				=> 	empty($rowData['rp_token']) ? 0 : $rowData['rp_token'],
					'rp_token_created_at' 	=> 	empty($rowData['rp_token_created_at']) ? 0 : $rowData['rp_token_created_at'],
					'firstname'			 	=> 	empty($rowData[self::COL_FIRSTNAME]) ? 'firstname' : $rowData[self::COL_FIRSTNAME],
					'lastname'			 	=> 	empty($rowData[self::COL_LASTNAME]) ? 'lastname' : $rowData[self::COL_LASTNAME],
					'expire_date'			=> 	empty($rowData['expire_date']) ? $defaultExpireDate : $rowData['expire_date'],
					'active' 				=> 	empty($rowData['active']) ? 0 : $rowData['active'],
					'send_backendmails'		=> 	empty($rowData['send_backendmails']) ? 1 : $rowData['send_backendmails'],
					'store_id'				=> 	empty($rowData['store_id']) ? 0 : $rowData['store_id'],
					'address_ids'			=> 	empty($rowData['address_ids']) ? '' : $rowData['address_ids'],
					'create_sublogins'		=> 	empty($rowData['create_sublogins']) ? 0 : $rowData['create_sublogins'],
					'is_subscribed'		    => 	empty($rowData['is_subscribed']) ? 0 : $rowData['is_subscribed'],
					'prefix'				=> 	empty($rowData['prefix']) ? '' : $rowData['prefix'],
				);
				if (isset($oldSubloginsToLower[$storeId][$entityId][$emailToLower]))
                { // edit
					$entityRow['id']   	= $oldSubloginsToLower[$storeId][$entityId][$emailToLower];
					$entityRowsUp[] 	= $entityRow;
				}
                else
                {
					$autoId = $nextAutoId++;
					$entityRow['id'] = $autoId;
					$entityRowsIn[] = $entityRow;
					$this->_newSublogins[$storeId][$entityId][$emailToLower] = $autoId;
				}
			}
			$this->_saveSubloginEntity($entityRowsIn, $entityRowsUp);
		}
		return $this;
	}

    /**
     * @param $entityRowsIn
     * @param $entityRowsUp
     * @return $this
     */
	protected function _saveSubloginEntity($entityRowsIn, $entityRowsUp)
    {
		if ($entityRowsIn) {
            $this->_connection->insertMultiple($this->_entityTable, $entityRowsIn);
        }
        if ($entityRowsUp) {
            $this->_connection->insertOnDuplicate(
                $this->_entityTable,
                $entityRowsUp
            );
        }
        return $this;
	}
}