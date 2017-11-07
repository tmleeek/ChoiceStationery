<?php

/**
 * Description of Process
 *
 * @author Ea Design
 */
class Magestore_Pdfinvoiceplus_Model_Variables_Process extends Mage_Core_Model_Variable
{

    public function getVariablesOptionArray($type, $withGroup = false)
    {
        $collection = $this->getCollection();
        $variables = array();
        $allVars = array();
        foreach ($collection->toOptionArray() as $variable)
        {
            $variables[] = array(
                'value' => '{{customVar code=' . $variable['value'] . '}}',
                'label' => Mage::helper('core')->__('%s', $variable['label'])
            );
        }
        if ($withGroup && $variables)
        {
            $variables = array(
                'label' => Mage::helper('core')->__('Custom Variables'),
                'value' => $variables
            );
        }

        $variableHelper = Mage::helper('pdfinvoiceplus/variable');
        
        $helperCustomer = $variableHelper->getCustomerVariables();
        $helperShipPay = $variableHelper->getShipPayVariables();
        $helperInvoice = $variableHelper->getInvoiceVariables();
        $helperOrder = $variableHelper->getOrderVariables();
        $helperCreditmemo = $variableHelper->getCreditMemoVariables();
        
        if($type == 'order'){
            $allVars = array(
                $helperOrder,
                $helperShipPay,
                $helperCustomer,
            );
        }elseif($type == 'invoice'){
            $allVars = array(
                $helperInvoice,
                $helperShipPay,
                $helperCustomer,
            );
        }else{
            $allVars = array(
                $helperCreditmemo,
                $helperShipPay,
                $helperCustomer,
            );
        }
        return $allVars;
    }

}