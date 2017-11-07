<?php
/**
* @category Customer Version 1.5
* @package MageB2B_Sublogin
* @author AIRBYTES GmbH <info@airbytes.de>
* @copyright AIRBYTES GmbH
* @license commercial
* @date 26.04.2014
*/
class MageB2B_Sublogin_Model_Mysql4_Sublogin_Collection extends Mage_Core_Model_Mysql4_Collection_Abstract
{
    /**
     * when this collections gets used from inside the grid
     * some functionality is added to work with the joined customer-tables
     */
    public $inGrid = false;

    public function _construct()
    {
        parent::_construct();
        $this->_init('sublogin/sublogin');
    }

    /**
     * correct active state
     * look at sublogin model for function correctActive()
     *
     * @return Mage_Core_Model_Resource_Db_Collection_Abstract
     */
    protected function _afterLoad()
    {
        foreach ($this->_items as $item)
        {
            $item->correctActive();
        }
        return parent::_afterLoad();
    }

    /**
     * this is for display in grid
     * @param array|string $field
     * @param null $condition
     * @return Mage_Eav_Model_Entity_Collection_Abstract
     */
    public function addFieldToFilter($field, $condition=null)
    {
        if ($this->inGrid) {
            $map = array(
                'cemail'       => 'customer.email',
                'email'        => 'main_table.email',
                'ccustomer_id' => 'customer_id_table.value',
            );
            if (isset($map[$field]))
                $field = $map[$field];
        }
        return parent::addFieldToFilter($field, $condition);
    }
}
