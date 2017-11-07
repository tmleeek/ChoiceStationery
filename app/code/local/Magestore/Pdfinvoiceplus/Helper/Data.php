<?php
/**
 * Magestore
 * 
 * NOTICE OF LICENSE
 * 
 * This source file is subject to the Magestore.com license that is
 * available through the world-wide-web at this URL:
 * http://www.magestore.com/license-agreement.html
 * 
 * DISCLAIMER
 * 
 * Do not edit or add to this file if you wish to upgrade this extension to newer
 * version in the future.
 * 
 * @category    Magestore
 * @package     Magestore_Pdfinvoiceplus
 * @copyright   Copyright (c) 2012 Magestore (http://www.magestore.com/)
 * @license     http://www.magestore.com/license-agreement.html
 */

/**
 * Pdfinvoiceplus Helper
 * 
 * @category    Magestore
 * @package     Magestore_Pdfinvoiceplus
 * @author      Magestore Developer
 */
class Magestore_Pdfinvoiceplus_Helper_Data extends Mage_Core_Helper_Abstract
{
   public function processAllVars($varialbles = array())
    {
        /* value and label */
        $varData = array();
        foreach ($varialbles as $variable)
        {
            $allKeysLabel = array();
            $allKeys = array();
            $allVars = array();
            foreach (array_keys($variable) as $v)
            {
                $allKeysLabel['label_' . $v] = $variable[$v]['label'] . ' ' . $variable[$v]['value'];
                $allKeys[$v] = $variable[$v]['value'];
            }
            $allVars = array_merge($allKeysLabel, $allKeys);
            $varData[] = $allVars;
        }
        foreach ($varData as $value)
        {
            foreach ($value as $key => $val)
            {
                $varsData[$key] = $val;
            }
        }
        return $varsData;
    }
    
    public function arrayToStandard($variable = array())
    {
        foreach ($variable as $key => $var)
        {
            $variables[] = array($key => $var); 
        }
        return $variables;
    }
    
    public function checkEnable(){
        $config = Mage::getStoreConfig('pdfinvoiceplus/general/enable');
        return $config;
    }
    
     public function checkStoreTemplate() {
        $order = Mage::helper('pdfinvoiceplus/pdf')->getOrder();
        $collection = Mage::getModel('pdfinvoiceplus/template')->getCollection()
            ->addFieldToFilter('status', 1)
        ;
        if (Mage::helper('pdfinvoiceplus')->useMultistore()) {
            $collection->addFieldToFilter('stores', array('finset' => $order->getStoreId()));
            if ($collection->getSize() == 0) {
                $collection = Mage::getModel('pdfinvoiceplus/template')->getCollection()
                    ->addFieldToFilter('status', 1)
                    ->addFieldToFilter('stores', array('finset' => 0));
            }
        }

        if ($collection->getSize()) {
            return 1;
        } else {
            return 0;
        }
    }

    public function useMultistore(){
        $store = Mage::app()->getStore()->getId();
        $config = Mage::getStoreConfig('pdfinvoiceplus/general/use_multistore', $store);
        if($config)
            return true;
        return false;
    }
        
    public function splitString($str, $length){
        $array = str_split($str, $length);
        return $array;
    }
}