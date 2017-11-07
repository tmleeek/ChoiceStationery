<?php
/**
 * @category Customer Service Version 3.0
 * @package MageB2B_Sublogin
 * @author AIRBYTES GmbH <info@airbytes.de>
 * @copyright AIRBYTES GmbH
 * @license commercial
 * @date 09.01.2016
 */
class MageB2B_Sublogin_Model_Attribute extends Mage_Eav_Model_Attribute
{
    /**
     * Name of the module
     */
    const MODULE_NAME = 'MageB2B_Sublogin';

    /**
     * Prefix of model events names
     *
     * @var string
     */
    protected $_eventPrefix = 'sublogin_entity_attribute';

    /**
     * Prefix of model events object
     *
     * @var string
     */
    protected $_eventObject = 'sublogin_attribute';

    /**
     * Init resource model
     */
    protected function _construct()
    {
        $this->_init('sublogin/attribute');
    }
}
