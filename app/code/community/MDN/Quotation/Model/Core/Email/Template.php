<?php

class MDN_Quotation_Model_Core_Email_Template extends Mage_Core_Model_Email_Template {

    /**
     * 
     */
    public function sendTransactionalWithAttachment($templateId, $sender, $email, $name, $vars=array(), $storeId=null, $attachment=null) {
        $this->setSentSuccess(false);
        if (($storeId === null) && $this->getDesignConfig()->getStore()) {
            $storeId = $this->getDesignConfig()->getStore();
        }

        if (is_numeric($templateId)) {
            $this->load($templateId);
        } else {
            $localeCode = Mage::getStoreConfig('general/locale/code', $storeId);
            $this->loadDefault($templateId, $localeCode);
        }

        if (!$this->getId()) {
            throw Mage::exception('Mage_Core', Mage::helper('core')->__('Invalid transactional email code: ' . $templateId));
        }

        if (!is_array($sender)) {
            $this->setSenderName(Mage::getStoreConfig('trans_email/ident_' . $sender . '/name', $storeId));
            $this->setSenderEmail(Mage::getStoreConfig('trans_email/ident_' . $sender . '/email', $storeId));
        } else {
            $this->setSenderName($sender['name']);
            $this->setSenderEmail($sender['email']);
        }

        $this->setSentSuccess($this->sendWithAttachment($email, $name, $vars, $attachment));
        return $this;
    }

    /**
     * Overwrite to manage attachments
     * */
    public function sendWithAttachment($email, $name=null, array $variables = array(), $attachment=null) {
        if (!$this->isValidForSend()) {
            return false;
        }

        if (is_null($name)) {
            $name = substr($email, 0, strpos($email, '@'));
        }

        $variables['email'] = $email;
        $variables['name'] = $name;

        ini_set('SMTP', Mage::getStoreConfig('system/smtp/host'));
        ini_set('smtp_port', Mage::getStoreConfig('system/smtp/port'));

        $mail = $this->getMail();
        if (is_array($email)) {
            foreach ($email as $emailOne) {
                $mail->addTo($emailOne, $name);
            }
        } else {
            $mail->addTo($email, '=?utf-8?B?' . base64_encode($name) . '?=');
        }

        $this->setUseAbsoluteLinks(true);
        $text = $this->getProcessedTemplate($variables, true);

        if ($this->isPlain()) {
            $mail->setBodyText($text);
        } else {
            $mail->setBodyHTML($text);
        }

        // test subject
        $subject = $this->getProcessedTemplateSubject($variables);
        if( empty($subject) ) $subject = 'no subject';
        
        $mail->setSubject('=?utf-8?B?' . base64_encode($subject) . '?=');
        $mail->setFrom($this->getSenderEmail(), $this->getSenderName());

        //Ajoute la piece jointe
        if ((is_array($attachment))) {
            foreach ($attachment as $item) {
                $pj = $mail->createAttachment($item['content']);
                $pj->filename = $item['name'];
            }
        }

        try {
            $mail->send(); // Zend_Mail warning..
            $this->_mail = null;
        } catch (Exception $e) {
            return false;
        }

        return true;
    }

}