<?php
class Mxm_AllInOne_CouponController extends Mage_Core_Controller_Front_Action
{
    /**
     * $_REQUEST['auth_key'] = string
     * $_REQUEST['time']     = integer
     * $_REQUEST['rule_id']  = integer
     * $_REQUEST['quantity'] = integer
     */
    public function generateAction()
    {
        try {
            $this->getResponse()->setHeader('Content-type', 'text/plain');

            $authKey  = $this->getRequest()->getParam('auth_key');
            $time     = $this->getRequest()->getParam('time');
            $ruleId   = $this->getRequest()->getParam('rule_id');
            $quantity = $this->getRequest()->getParam('quantity');
            Mage::helper('mxmallinone/coupon')->checkAuthKey($authKey, $ruleId, $time);

            if (!$quantity) {
                return;
            }

            $couponData = array(
                'rule_id' => $ruleId,
                'qty'     => $quantity,
                'length'  => 10,
                'format'  => 'alphanum',
            );

            /* @var $salesRule Mage_SalesRule_Model_Rule */
            $salesRule = Mage::getModel('salesrule/rule')->load($ruleId);
            if ($salesRule->getId() !== $ruleId) {
                throw new Exception('Invalid rule ID', 400);
            }

            /* @var $generator Mage_SalesRule_Model_Coupon_Massgenerator */
            $generator = $salesRule->getCouponMassGenerator();
            if (!$generator->validateData($couponData)) {
                throw new Exception('Invalid coupon data', 400);
            }

            $now = Varien_Date::now();

            $generator->setData($couponData)
                ->generatePool();

            /* @var $couponCollection Mage_SalesRule_Model_Resource_Coupon_Collection */
            $couponCollection = Mage::getModel('salesrule/coupon')->getCollection();
            $couponCollection->addRuleToFilter($salesRule)
                ->addGeneratedCouponsFilter()
                ->addFieldToFilter('times_used', array('eq' => 0))
                ->addFieldToFilter('created_at', array('gteq' => $now))
                ->setPageSize($quantity);

            foreach ($couponCollection as $coupon) {
                echo $coupon->getCode() . "\n";
            }

        } catch (Exception $e) {
            if ($e->getCode() > 0) {
                $this->getResponse()->setHttpResponseCode($e->getCode())
                    ->setBody($e->getMessage());
                return;
            } else {
                throw $e;
            }
        }
    }
}