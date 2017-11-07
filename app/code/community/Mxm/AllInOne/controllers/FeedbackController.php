<?php
class Mxm_AllInOne_FeedbackController extends Mage_Core_Controller_Front_Action
{
    /**
     * $_REQUEST['auth_key'] = string
     * $_REQUEST['time'] = integer
     * $_REQUEST['event'] = string
     * $_REQUEST['data'] = serialised json:
     *      array(
     *          'customer_id' => integer,
     *          'customer_name' => string,
     *          'email_id' => integer,
     *          'recipient_id' => integer,
     *          'email_address' => string,
     *          'link_id' => integer,
     *          'link_type' => integer,
     *          'link_url' => integer,
     *          'unsubscribe_method' => string,
     *          'timestamp' => string,
     *          'ip_address' => string,
     *          'client_id' => integer,
     *          'client_name' => string,
     *          'device_id' => integer,
     *          'device_name' => string
     *      )
     */
    public function unsubscribeAction()
    {
        $code = 200;
        $body = 'Success';

        try {
            // authenticate
            $authKey = $this->getRequest()->getParam('auth_key', '');
            $time    = $this->getRequest()->getParam('time', 0);
            Mage::helper('mxmallinone/subscriber')->checkAuthKey($authKey, $time);

            $data = json_decode($this->getRequest()->getParam('data'), true);
            if (!is_array($data)) {
                throw new Exception('Failed retrieving data', 400);
            }

            /* @var $subscriber Mage_Newsletter_Model_Subscriber */
            $subscriber = Mage::getModel('newsletter/subscriber')
                ->loadByEmail($data['email_address']);
            $subscriber->setSubscriberStatus(Mage_Newsletter_Model_Subscriber::STATUS_UNSUBSCRIBED)
                ->setNoSync(true)
                ->save();
        } catch (Exception $e) {
            if (!($e->getCode() > 0)) {
                throw $e;
            }

            Mage::log('Mxm_AllInOne\FeedbackController::unsubscribe failed: '.serialize($this->getRequest()->getParams()));

            $code = 200; // $e->getCode(); // causes the job to be delayed and retried on Maxemail
            $body = $e->getMessage();
        }

        $this->getResponse()
            ->clearHeaders()
            ->setHttpResponseCode($code)
            ->setHeader('Content-Type', 'text/plain')
            ->setBody($body);
    }
}