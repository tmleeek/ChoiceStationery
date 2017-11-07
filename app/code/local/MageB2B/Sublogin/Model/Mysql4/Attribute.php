<?php
/**
* @category Customer Version 1.9
* @package MageB2B_Sublogin
* @author AIRBYTES GmbH <info@airbytes.de>
* @copyright AIRBYTES GmbH
* @license commercial
* @date 26.04.2014
*/
class MageB2B_Sublogin_Model_Mysql4_Attribute extends Mage_Eav_Model_Resource_Attribute
{
    /**
     * Get EAV website table
     *
     * Get table, where website-dependent attribute parameters are stored
     * If realization doesn't demand this functionality, let this function just return null
     *
     * @return string|null
     */
    protected function _getEavWebsiteTable()
    {
        return null;
    }
}
