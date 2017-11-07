<?php
/**
 * aheadWorks Co.
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the EULA
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://ecommerce.aheadworks.com/AW-LICENSE.txt
 *
 * =================================================================
 *                 MAGENTO EDITION USAGE NOTICE
 * =================================================================
 * This software is designed to work with Magento community edition and
 * its use on an edition other than specified is prohibited. aheadWorks does not
 * provide extension support in case of incorrect edition use.
 * =================================================================
 *
 * @category   AW
 * @package    AW_Afptc
 * @version    1.1.12
 * @copyright  Copyright (c) 2010-2012 aheadWorks Co. (http://www.aheadworks.com)
 * @license    http://ecommerce.aheadworks.com/AW-LICENSE.txt
 */

class AW_Afptc_Model_Rule_Condition_Product_Subselect extends Mage_SalesRule_Model_Rule_Condition_Product_Subselect
{
    public function __construct()
    {
        parent::__construct();
        $this->setType('awafptc/rule_condition_product_subselect');
    }

    /**
     * validate
     *
     * @param Varien_Object $object Quote
     * @return boolean
     */
    public function validate(Varien_Object $object)
    {
        if (!$this->getConditions()) {
            return false;
        }

        $attr = $this->getAttribute();
        $total = 0;
        foreach ($object->getQuote()->getAllItems() as $item) {
            $ruleIdOption = $item->getOptionByCode('aw_afptc_rule');
            if (
                null !== $ruleIdOption
                // && $ruleIdOption->getValue() == $this->getRule()->getId()
            ) {
                continue;
            }
            $validator = Mage::getSingleton('rule/condition_combine');
            $validator->setConditions($this->getConditions());
            if ($validator->validate($item)) {
                $total += $item->getData($attr);
            }
        }
        return $this->validateAttribute($total);
    }
}
