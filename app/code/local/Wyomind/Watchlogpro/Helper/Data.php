<?php
/**
 * Copyright © 2017 Wyomind. All rights reserved.
 * See LICENSE.txt for license details.
 */
class Wyomind_Watchlogpro_Helper_Data extends Wyomind_Watchlog_Helper_Data
{
    public function checkWarning() 
    {
        return null;
    }

    public function checkNotification() 
    {
        return null;
    }

    public function createLog($login, $message, $ipStatus, $type) 
    {
        $coreHttpHelper = Mage::helper('core/http');
        $data = array(
            'login'     => $login,
            'ip'        => $coreHttpHelper->getRemoteAddr(),
            'date'      => Mage::getModel('core/date')->gmtDate('Y-m-d H:i:s'),
            'type'      => $type,
            'useragent' => $coreHttpHelper->getHttpUserAgent(),
            'message'   => $message,
            'url'       => Mage::app()->getRequest()->getRequestUri(),
            'ip_status' => $ipStatus
        );

        $model = Mage::getModel('watchlog/watchlog')->load(0);
        $model->setData($data);
        $model->save();
    }

    public function whitelist($ip) 
    {
        $pattern = "/^(\d{1,3})\.(\d{1,3})\.(\*|(?:\d{1,3}))\.(\*|(?:\d{1,3}))$/";
        if (true == preg_match($pattern, $ip)) {
            $whitelist = array();
            $coreHelper = Mage::helper('core');
            if (Mage::getStoreConfig('watchlogpro/settingspro/whitelist') != '') {
                $whitelist = $coreHelper->jsonDecode(Mage::getStoreConfig('watchlogpro/settingspro/whitelist'));
            }

            if (false === Mage::helper('watchlogpro')->isListed($ip, $whitelist)) {
                $whitelist[] = $ip;
            }
            
            Mage::getConfig()->saveConfig(
                'watchlogpro/settingspro/whitelist', 
                $coreHelper->jsonEncode($whitelist), 
                'default',
                '0'
            );
            Mage::app()->getCacheInstance()->cleanType('config');
        }
    }

    public function unwhitelist($ip) 
    {
        $whitelist = array();
        $coreHelper = Mage::helper('core');
        
        if (Mage::getStoreConfig('watchlogpro/settingspro/whitelist') != '') {
            $whitelist = $coreHelper->jsonDecode(Mage::getStoreConfig('watchlogpro/settingspro/whitelist'));
        }
        
        if (in_array($ip, array_values($whitelist))) {
            $ak = array_keys($whitelist, $ip);
            $whitelist[$ak[0]] = null;
        }
        
        $whitelist = array_filter($whitelist);
        
        Mage::getConfig()->saveConfig(
            'watchlogpro/settingspro/whitelist', 
            $coreHelper->jsonEncode($whitelist), 
            'default',
            '0'
        );
        Mage::app()->getCacheInstance()->cleanType('config');
    }

    public function blacklist($ip) 
    {
        $pattern = "/^(\d{1,3})\.(\d{1,3})\.(\*|(?:\d{1,3}))\.(\*|(?:\d{1,3}))$/";
        if (true == preg_match($pattern, $ip)) {
            $blacklist = array();
            $coreHelper = Mage::helper('core');

            if (Mage::getStoreConfig('watchlogpro/settingspro/blacklist') != '') {
                $blacklist = $coreHelper->jsonDecode(Mage::getStoreConfig('watchlogpro/settingspro/blacklist'));
            }
            
            if (false === Mage::helper('watchlogpro')->isListed($ip, $blacklist)) {
                $blacklist[] = array('ip' => $ip);
            }
            
            Mage::getConfig()->saveConfig(
                'watchlogpro/settingspro/blacklist', 
                $coreHelper->jsonEncode($blacklist), 
                'default', 
                '0'
            );
            Mage::app()->getCacheInstance()->cleanType('config');
        }
    }

    public function unblacklist($ip) 
    {
        $blacklist = array();
        $coreHelper = Mage::helper('core');
        
        if (Mage::getStoreConfig('watchlogpro/settingspro/blacklist') != '') {
            $blacklist = $coreHelper->jsonDecode(Mage::getStoreConfig('watchlogpro/settingspro/blacklist'));
        }
        
        $newBlacklist = array();
        foreach ($blacklist as $bl) {
            if ($bl['ip'] !== $ip) {
                $newBlacklist[] = $bl;
            }
        }

        Mage::getConfig()->saveConfig(
            'watchlogpro/settingspro/blacklist', 
            $coreHelper->jsonEncode($newBlacklist), 
            'default', 
            '0'
        );
        Mage::app()->getCacheInstance()->cleanType('config');
    }

    public function blacklistTemporary($ip, $nbAttempts) 
    {
        $blacklistFull = array();
        $coreHelper = Mage::helper('core');
        
        if (Mage::getStoreConfig('watchlogpro/settingspro/blacklist') != '') {
            $blacklistFull = $coreHelper->jsonDecode(Mage::getStoreConfig('watchlogpro/settingspro/blacklist'));
        }

        $blacklist = array();

        foreach ($blacklistFull as $bl) {
            $blacklist[] = $bl['ip'];
        }

        $notif = false;
        $blockDuration = Mage::getStoreConfig('watchlogpro/settingspro/block_duration');

        if (!in_array($ip, $blacklist) && $blockDuration != 0) {
            $now = Mage::getModel('core/date')->gmtTimestamp() + $blockDuration * 60;
            $blacklistFull[] = array('ip' => $ip, 'until' => date('Y-m-d H:i:s', $now));
            $notif = true;
        }
        
        if (!in_array($ip, $blacklist) && $blockDuration == 0) {
            $now = time();
            $blacklistFull[] = array('ip' => $ip);
            $notif = true;
        }
        
        Mage::getConfig()->saveConfig(
            'watchlogpro/settingspro/blacklist', 
            $coreHelper->jsonEncode($blacklistFull), 
            'default', 
            '0'
        );
        Mage::app()->getCacheInstance()->cleanType('config');

        if ($notif) {
            // send report
            $emailTemplate = Mage::getModel('core/email_template')->loadDefault('watchlogpro_report');

            $emailTemplateVariables = array(
                'data' => array(
                    'ip'            => $ip,
                    'nb_attempts'   => $nbAttempts,
                    'until'         => date('jS F Y \a\t h:iA', $now)
                )
            );

            $processedTemplate = $emailTemplate->getProcessedTemplate($emailTemplateVariables);

            foreach (explode(',', Mage::getStoreConfig('watchlogpro/settingspro/report_emails')) as $email) {
                if (trim($email) != '') {
                    $mail = Mage::getModel('core/email')
                            ->setToEmail($email)
                            ->setBody($processedTemplate)
                            ->setSubject(Mage::getStoreConfig('watchlogpro/settingspro/report_title'))
                            ->setFromEmail($email)
                            ->setFromName('Magento | Watchlog')
                            ->setType('html');
                    $mail->send();
                }
            }
        }
    }

    public function isListed($ip, $whitelist) 
    {
        foreach ($whitelist as $wip) {
            if ($ip === $wip) {
                return true;
            } else {
                if (true === is_array($wip)) {
                    $wip = $wip['ip'];
                }
                
                $wipExploded = explode('.', $wip);
                $ipExploded = explode('.', $ip);
                $match = true;
                for ($i = 0; $i<4; $i++) {
                    $match &= ($wipExploded[$i] == $ipExploded[$i] || $wipExploded[$i] == '*');
                }
                if ($match) {
                    return true;
                }
            }
        }
        
        return false;
    }
}