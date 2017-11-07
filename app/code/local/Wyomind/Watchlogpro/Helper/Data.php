<?php

class Wyomind_Watchlogpro_Helper_Data extends Wyomind_Watchlog_Helper_Data {

    public function checkWarning() {
        return;
    }

    public function checkNotification() {
        return;
    }

    public function createLog($login, $message, $ip_status, $type) {
        $data = array(
            "login" => $login,
            "ip" => Mage::helper('core/http')->getRemoteAddr(),
            "date" => Mage::getModel('core/date')->gmtDate('Y-m-d H:i:s'),
            "type" => $type,
            "useragent" => Mage::helper('core/http')->getHttpUserAgent(),
            "message" => $message,
            "url" => Mage::app()->getRequest()->getRequestUri(),
            "ip_status" => $ip_status
        );

        $model = Mage::getModel('watchlog/watchlog')->load(0);
        $model->setData($data);
        $model->save();
    }

    public function whitelist($ip) {
        $whitelist = Mage::helper('core')->jsonDecode(Mage::getStoreConfig("watchlogpro/settingspro/whitelist"));
        if (!in_array($ip, $whitelist)) {
            $whitelist[] = $ip;
        }
        Mage::getConfig()->saveConfig("watchlogpro/settingspro/whitelist", Mage::helper('core')->jsonEncode($whitelist), "default", "0");
        Mage::app()->getCacheInstance()->cleanType('config');
    }

    public function unwhitelist($ip) {
        $whitelist = Mage::helper('core')->jsonDecode(Mage::getStoreConfig("watchlogpro/settingspro/whitelist"));
        if (in_array($ip, array_values($whitelist))) {
            $ak = array_keys($whitelist, $ip);
            $whitelist[$ak[0]] = null;
        }
        $whitelist = array_filter($whitelist);
        Mage::getConfig()->saveConfig("watchlogpro/settingspro/whitelist", Mage::helper('core')->jsonEncode($whitelist), "default", "0");
        Mage::app()->getCacheInstance()->cleanType('config');
    }

    public function blacklist($ip) {
        $blacklist_full = Mage::helper('core')->jsonDecode(Mage::getStoreConfig("watchlogpro/settingspro/blacklist"));
        $blacklist = array();
        foreach ($blacklist_tmp as $bl) {
            $blacklist[] = $bl['ip'];
        }

        if (!in_array($ip, $blacklist)) {
            $blacklist_full[] = array('ip' => $ip);
        }
        Mage::getConfig()->saveConfig("watchlogpro/settingspro/blacklist", Mage::helper('core')->jsonEncode($blacklist_full), "default", "0");
        Mage::app()->getCacheInstance()->cleanType('config');
    }

    public function unblacklist($ip) {

        $blacklist_full = Mage::helper('core')->jsonDecode(Mage::getStoreConfig("watchlogpro/settingspro/blacklist"));

        $blacklist = array();
        foreach ($blacklist_full as $bl) {
            $blacklist[] = $bl['ip'];
        }

        $new_blacklist = array();
        foreach ($blacklist_full as $bl) {
            if ($bl['ip'] !== $ip) {
                $new_blacklist[] = $bl;
            }
        }

        Mage::getConfig()->saveConfig("watchlogpro/settingspro/blacklist", Mage::helper('core')->jsonEncode($new_blacklist), "default", "0");
        Mage::app()->getCacheInstance()->cleanType('config');
    }

    public function blacklistTemporary($ip, $nb_attempts) {
        $blacklist_full = Mage::helper('core')->jsonDecode(Mage::getStoreConfig("watchlogpro/settingspro/blacklist"));
        if (!$blacklist_full) {
            $blacklist_full = array();
        }

        $blacklist = array();

        foreach ($blacklist_full as $bl) {
            $blacklist[] = $bl['ip'];
        }

        $notif = false;

        if (!in_array($ip, $blacklist) && Mage::getStoreConfig('watchlogpro/settingspro/block_duration') != 0) {
            $nb_min = Mage::getStoreConfig('watchlogpro/settingspro/block_duration');
            $now = Mage::getModel('core/date')->gmtTimestamp() + $nb_min * 60;
            $blacklist_full[] = array('ip' => $ip, 'until' => date('Y-m-d H:i:s', $now));
            $notif = true;
        }
        if (!in_array($ip, $blacklist) && Mage::getStoreConfig('watchlogpro/settingspro/block_duration') == 0) {
            $now = "";
            $blacklist_full[] = array('ip' => $ip);
            $notif = true;
        }


        Mage::getConfig()->saveConfig("watchlogpro/settingspro/blacklist", Mage::helper('core')->jsonEncode($blacklist_full), "default", "0");
        Mage::app()->getCacheInstance()->cleanType('config');

        if ($notif) {
            // send report
            $emailTemplate = Mage::getModel('core/email_template')->loadDefault('watchlogpro_report');

            $emailTemplateVariables = array("data" => array(
                    "ip" => $ip,
                    "nb_attempts" => $nb_attempts,
                    "until" => date('jS F Y \a\t h:iA', $now)
                )
            );

            $processedTemplate = $emailTemplate->getProcessedTemplate($emailTemplateVariables);


            foreach (explode(',', Mage::getStoreConfig("watchlogpro/settingspro/report_emails")) as $email) {
                if (trim($email) != "") {
                    $mail = Mage::getModel('core/email')
                            ->setToEmail($email)
                            ->setBody($processedTemplate)
                            ->setSubject(Mage::getStoreConfig("watchlogpro/settingspro/report_title"))
                            ->setFromEmail($email)
                            ->setFromName('Magento | Watchlog')
                            ->setType('html');
                    $mail->send();
                }
            }
        }
    }

    public function isWhitelisted($ip,$whitelist) {
        foreach ($whitelist as $wip) {
            if ($ip === $wip) {
                return true;
            } else {
                $wip_exploded = explode('.',$wip);
                $ip_exploded = explode('.',$ip);
                $match = true;
                for ($i = 0; $i<4; $i++) {
                    $match &= ($wip_exploded[$i] == $ip_exploded[$i] || $wip_exploded[$i] == '*');
                }
                if ($match) {
                    return true;
                }
            }
        }
        return false;
    }
    
}
