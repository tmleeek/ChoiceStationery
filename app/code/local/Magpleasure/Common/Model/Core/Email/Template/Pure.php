<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2018 Amasty (https://www.amasty.com)
 * @package Magpleasure_Common
 */


if (Mage::helper('core')->isModuleEnabled('Amasty_Smtp')) {
    $autoloader = Varien_Autoload::instance();
    $autoloader->autoload('Magpleasure_Common_Model_Core_Email_Template_AmastySmtp');
} elseif (Mage::helper('core')->isModuleEnabled('Aschroder_SMTPPro')) {
    $autoloader = Varien_Autoload::instance();
    $autoloader->autoload('Magpleasure_Common_Model_Core_Email_Template_AschroderSMTPPro');
}
else {
    class Magpleasure_Common_Model_Core_Email_Template_Pure extends Mage_Core_Model_Email_Template {}
}
