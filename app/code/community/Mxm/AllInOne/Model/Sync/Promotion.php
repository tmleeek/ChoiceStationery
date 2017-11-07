<?php
class Mxm_AllInOne_Model_Sync_Promotion extends Mxm_AllInOne_Model_Sync_Abstract
{
    protected $fieldMap = array(
        'rule_id'   => 'Rule Id',
        'rule_name' => 'Rule Name',
    );

    /**
     * @var string
     */
    protected $importPath = '/Magento/Promotions';

    public function __construct()
    {
        parent::__construct();
        $this->syncType = Mxm_AllInOne_Helper_Sync::SYNC_TYPE_PROMOTION;
    }

    protected function doSync($ids = null)
    {
//        Mage::log(__METHOD__);

        $promotions  = array();
        /* @var $collection Mage_Salesrule_Model_Resource_Rule_Collection */
        $collection = Mage::getModel('salesrule/rule')->getCollection()
            ->addWebsiteFilter($this->getWebsite())
            ->addIsActiveFilter();

        $collection->getSelect()
            ->where('to_date is null or to_date >= ?', Varien_Date::now());

        foreach ($collection as $promotion) {
            $promotions[] = $this->getPromotionArray($promotion);
        }

        if (!empty($promotions)) {
            $this->importDatatable($promotions, true);
            Mage::log("\tSynced " . count($promotions) . " promotions for website {$this->getWebsite()->getCode()}");
        }
    }

    protected function getPromotionArray($rule)
    {
        return array(
            'rule_id'   => $rule->getId(),
            'rule_name' => $rule->getName(),
        );
    }
}
