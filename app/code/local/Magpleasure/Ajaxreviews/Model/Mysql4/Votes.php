<?php

/**
 * @author Amasty Team
 * @copyright Copyright (c) 2018 Amasty (https://www.amasty.com)
 * @package Magpleasure_Ajaxreviews
 */
class Magpleasure_Ajaxreviews_Model_Mysql4_Votes extends Mage_Core_Model_Mysql4_Abstract
{
    /**
     * Resource initialization
     *
     */
    public function _construct()
    {
        $this->_init('ajaxreviews/votes', 'vote_id');
    }
}