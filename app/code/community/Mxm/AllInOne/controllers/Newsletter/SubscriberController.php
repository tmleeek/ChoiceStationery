<?php
include "Mage/Newsletter/controllers/SubscriberController.php";

class Mxm_AllInOne_Newsletter_SubscriberController extends Mage_Newsletter_SubscriberController
{
    /**
     * New subscription action
     */
    public function newAction()
    {
        parent::newAction();

        if (!Mage::helper('mxmallinone/subscriber')->hasAddFields()) {
            return;
        }

        $email   = (string) $this->getRequest()->getPost('email');
        /* @var $subscriber Mage_Newsletter_Model_Subscriber */
        $subscriber = Mage::getModel('newsletter/subscriber')
            ->loadByEmail($email);

        if (is_null($subscriber->getId())) {
            return;
        }

        if (($title = $this->getRequest()->getPost('title', false))) {
            $subscriber->setMxmTitle($title);
        }
        if (($firstName = $this->getRequest()->getPost('firstname', false))) {
            $subscriber->setMxmFirstname($firstName);
        }
        if (($lastName = $this->getRequest()->getPost('lastname', false))) {
            $subscriber->setMxmLastname($lastName);
        }

        $subscriber->save();
    }
}
