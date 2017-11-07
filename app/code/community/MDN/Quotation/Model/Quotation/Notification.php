<?php

class MDN_Quotation_Model_Quotation_Notification extends Mage_Core_Model_Abstract {

    /**
     * Send email to customer
     */
    public function NotifyCustomer($quote) {
        $translate = Mage::getSingleton('core/translate');
        $translate->setTranslateInline(false);

        $templateId = Mage::getStoreConfig('quotation/quote_notification/email_template', $quote->getCustomer()->getStoreId());
        $identityId = Mage::getStoreConfig('quotation/quote_notification/email_identity', $quote->getCustomer()->getStoreId());

        //Create array for used variables in email template
        $data = array(
            'subject' => Mage::helper('quotation')->__('New quote available'),
            'caption' => $quote->getcaption(),
            'name' => $quote->getCustomer()->getName(),
            'customer_email' => $quote->getCustomer()->getemail(),
            'increment_id' => $quote->getincrement_id(),
            'direct_url' => Mage::helper('quotation/DirectAuth')->getDirectUrl($quote),
        );

        //Attachment
        $Attachments = array();
        if ($quote->GetLinkedProduct() == null) {
            $quote->commit();
        }
        $pdf = Mage::getModel('Quotation/quotationpdf')->getPdf(array($quote));
        $Attachment = array();
        $Attachment['name'] = Mage::helper('quotation')->__('Quotation #') . $quote->getincrement_id() . '.pdf';
        $Attachment['content'] = $pdf->render();
        $Attachments[] = $Attachment;

        //add custom attachment
        if (Mage::helper('quotation/Attachment')->attachmentExists($quote)) {
            $attachmentPath = Mage::helper('quotation/Attachment')->getAttachmentPath($quote);
            if (file_exists($attachmentPath)) {
                $customAttachment = array();
                $customAttachment['name'] = $quote->getadditional_pdf() . '.pdf';
                $customAttachment['content'] = file_get_contents($attachmentPath);
                $Attachments[] = $customAttachment;
            }
        }

        //send email
        if (!empty($templateId))
            Mage::getModel('Quotation/Core_Email_Template')
                    ->setDesignConfig(array('area' => 'adminhtml', 'store' => $quote->getCustomer()->getStoreId()))
                    ->sendTransactionalWithAttachment(
                            $templateId,
                            $identityId,
                            $quote->getCustomer()->getemail(),
                            $quote->getCustomer()->getname(),
                            $data,
                            null,
                            $Attachments);
        else
            throw new Exception('Template Transactionnel Empty');


        //store notification date
        $quote->setnotification_date(date('y-m-d h:i'))
                ->setstatus(MDN_Quotation_Model_Quotation::STATUS_ACTIVE)
                ->save();
        $translate->setTranslateInline(true);

        //add notification in history
        $quote->addHistory(Mage::helper('quotation')->__('Customer notified'));

        return $quote;
    }

    /**
     * Send an email to store manager to notify about new quote request
     */
    public function NotifyCreationToAdmin($quote) {
        $translate = Mage::getSingleton('core/translate');
        $translate->setTranslateInline(false);

        $templateId = Mage::getStoreConfig('quotation/quotation_request/email_template', $quote->getCustomer()->getStoreId());
        $identityId = Mage::getStoreConfig('quotation/quotation_request/email_identity', $quote->getCustomer()->getStoreId());
        $sendTo = Mage::getStoreConfig('quotation/quotation_request/send_to', $quote->getCustomer()->getStoreId());

        $url = Mage::helper('adminhtml')->getUrl('Quotation/Admin/edit', array('quote_id' => $quote->getId()));
        $data = array
            (
            'subject' => Mage::Helper('quotation')->__('New Quotation Request'),
            'customer' => $quote->getCustomer()->getName(),
            'increment_id' => $quote->getincrement_id(),
            'url' => $url
        );

        if (!empty($templateId)) {
            Mage::getModel('Quotation/Core_Email_Template')
                    ->setDesignConfig(array('area' => 'adminhtml', 'store' => $quote->getCustomer()->getStoreId()))
                    ->sendTransactionalWithAttachment(
                            $templateId,
                            $identityId,
                            $sendTo,
                            null,
                            $data);
        }
        else
            throw new Exception('Template Transactionnel Empty');

        $translate->setTranslateInline(true);

        return $this;
    }

}