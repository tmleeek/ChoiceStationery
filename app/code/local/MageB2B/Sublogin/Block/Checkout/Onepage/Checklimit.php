<?php
/**
 * @category Customer Service Version 3.0
 * @package MageB2B_Sublogin
 * @author AIRBYTES GmbH <info@airbytes.de>
 * @copyright AIRBYTES GmbH
 * @license commercial
 * @date 09.01.2016
 */
class MageB2B_Sublogin_Block_Checkout_Onepage_Checklimit extends Mage_Checkout_Block_Onepage
{
    public function __construct()
    {
        $sublogin = Mage::helper('sublogin')->getCurrentSublogin();
        if ($sublogin) {
            $subloginBudgets = $sublogin->getBudgets();
        } else {
            if (Mage::getSingleton('customer/session')->isLoggedIn()) {
                $customer = Mage::getSingleton('customer/session')->getCustomer();
                $sublogin = $customer;
                
                $budgets = $customer->getData('budgets');
                $budgetsArray = json_decode($budgets, true);
                $budgets = array();
                if ($budgetsArray || count($budgetsArray) > 0) 
                {
                    foreach ($budgetsArray as $budgetSingle)
                    {
                        $budgetVarienObj = new Varien_Object();
                        $budgetVarienObj->setData($budgetSingle);                
                        $budgets[] = $budgetVarienObj;
                    }
                }
                
                if (count($budgets) > 0) {
                    $subloginBudgets = $budgets;
                } else {
                    return;
                }
                
            } else {
                return;
            }
        }
        
        // get budgets for current month, current year and current day
        $perOrderLimit = 0;
        $budgetTypeLimit = 0;
        $today = date('Y-m-d');
        $currentMonth = date('m');
        $currentYear = date('Y');
        $appliedPriority = 1000;
        $budgetType = '';
        if ($subloginBudgets)
        {
            foreach ($subloginBudgets as $subloginBudget)
            {
                if ($today == $subloginBudget->getYear().'-'.$subloginBudget->getMonth().'-'.$subloginBudget->getDay() && $subloginBudget->getBudgetType() == 'day')
                {
                    if ($appliedPriority > 1)
                    {
                        $budgetType = 'day';
                        $appliedPriority = 1;
                        $perOrderLimit = $subloginBudget->getPerOrder();
                        $budgetTypeLimit = $subloginBudget->getAmount();
                    }
                }
				elseif ($subloginBudget->getBudgetType() == 'daily')
                {
                    if ($appliedPriority > 2)
                    {
                        $budgetType = 'daily';
                        $appliedPriority = 2;
                        $perOrderLimit = $subloginBudget->getPerOrder();
                        $budgetTypeLimit = $subloginBudget->getDaily();
                    }
                }
                elseif ($currentYear.'-'.$currentMonth == $subloginBudget->getYear().'-'.$subloginBudget->getMonth() && $subloginBudget->getBudgetType() == 'month')
                {
                    if ($appliedPriority > 3)
                    {
                        $budgetType = 'month';
                        $appliedPriority = 3;
                        $perOrderLimit = $subloginBudget->getPerOrder();
                        $budgetTypeLimit = $subloginBudget->getAmount();
                    }
                }
                elseif ($subloginBudget->getBudgetType() == 'monthly')
                {
                    if ($appliedPriority > 4)
                    {
                        $budgetType = 'monthly';
                        $appliedPriority = 4;
                        $perOrderLimit = $subloginBudget->getPerOrder();
                        $budgetTypeLimit = $subloginBudget->getMonthly();
                    }
                }
                elseif ($currentYear == $subloginBudget->getYear() && $subloginBudget->getBudgetType() == 'year')
                {
                    if ($appliedPriority > 5)
                    {
                        $budgetType = 'year';
                        $appliedPriority = 5;
                        $perOrderLimit = $subloginBudget->getPerOrder();
                        $budgetTypeLimit = $subloginBudget->getAmount();
                    }
                }
                elseif ($subloginBudget->getBudgetType() == 'yearly')
                {
                    if ($appliedPriority > 6)
                    {
                        $budgetType = 'yearly';
                        $appliedPriority = 6;
                        $perOrderLimit = $subloginBudget->getPerOrder();
                        $budgetTypeLimit = $subloginBudget->getYearly();
                    }
                }
            }
        }
        
        $checkoutSession = Mage::getSingleton('checkout/session');
        // check for per order limit
        if ($perOrderLimit > 0)
        {
            $quoteBaseGrandTotal = $checkoutSession->getQuote()->getBaseGrandTotal();
            if ($quoteBaseGrandTotal > $perOrderLimit)
            {
                $checkoutSession->addError(Mage::helper('sublogin')->__('You are not allowed to place order which has total greater than %s', Mage::helper('core')->formatCurrency($perOrderLimit)));
                
                $url = Mage::getUrl('checkout/cart');
                Mage::app()->getFrontController()->getResponse()->setRedirect($url);
                return;
            }
        }
        
        // check budgetTypeLimit
        if ($budgetTypeLimit)
        {
            // get order totals which was placed by this sublogin
            // and filter order based on appliedPriority
            // 1 = day
            // 2 = daily
            // 3 = month
            // 4 = monthly
            // 5 = year
            // 6 = yearly
            $reportFromDate = null;
            if ($appliedPriority == 1 || $appliedPriority == 2)
            {
                $reportFromDate = date("Y-m-d");
            }
            if ($appliedPriority == 3 || $appliedPriority == 4)
            {
                $reportFromDate = date("Y-m-d", mktime(0, 0, 0, $currentMonth, 1, $currentYear));
            }
            if ($appliedPriority == 5 || $appliedPriority == 6)
            {
                $reportFromDate = date("Y-m-d", mktime(0, 0, 0, 1, 1, $currentYear));
            }
            
            if (!$reportFromDate) {
                return;
            }
            
            $reportFromDate .= ' 00:00:00';         
            $_customerOrderCollection = Mage::getResourceModel('reports/customer_totals_collection')
                ->addAttributeToFilter('customer_email', $sublogin->getEmail())
                ->addAttributeToFilter('state', array('neq'=>Mage_Sales_Model_Order::STATE_CANCELED))
                ->addAttributeToFilter('created_at', array('from' => $reportFromDate, 'to' => now(), 'datetime' => true));
            
            $_customerPlacedOrderTotal = 0;
            foreach ($_customerOrderCollection as $_customerC) {
                $_customerPlacedOrderTotal += $_customerC->getBaseGrandTotal();
            }
            
            // adding current quote total
            $quoteBaseGrandTotal = $checkoutSession->getQuote()->getBaseGrandTotal();
            
            if (($_customerPlacedOrderTotal + $quoteBaseGrandTotal) > $budgetTypeLimit)
            {
                $continueOrderAmount = $budgetTypeLimit - $_customerPlacedOrderTotal;
				
				if ($appliedPriority == 2 || $appliedPriority == 4 || $appliedPriority == 6)
				{
					$checkoutSession->addError(Mage::helper('sublogin')->__('Your %s purchase limit %s is reached.', $budgetType, Mage::helper('core')->formatCurrency($budgetTypeLimit)));
				}
				else
				{
					$checkoutSession->addError(Mage::helper('sublogin')->__('You can not proceed as your purchase limit %s for this %s is reached.', Mage::helper('core')->formatCurrency($budgetTypeLimit), $budgetType));
				}
                
                if ($continueOrderAmount > 0)
                {
                    $checkoutSession->addError(Mage::helper('sublogin')->__('You can continue with this order if your cart total is maximum upto %s.', 
                    Mage::helper('core')->formatCurrency($continueOrderAmount)));
                }
                
                $url = Mage::getUrl('checkout/cart');
                Mage::app()->getFrontController()->getResponse()->setRedirect($url);
                return;
            }
        }
    }
}
