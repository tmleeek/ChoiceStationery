<?php
/**
 * OneStepCheckout
 * 
 * NOTICE OF LICENSE
 *
 * This source file is subject to One Step Checkout AS software license.
 *
 * License is available through the world-wide-web at this URL:
 * https://www.onestepcheckout.com/LICENSE.txt
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to mail@onestepcheckout.com so we can send you a copy immediately.
 *
 * @category   Idev
 * @package    Idev_OneStepCheckout
 * @copyright  Copyright (c) 2009 OneStepCheckout  (https://www.onestepcheckout.com/)
 * @license    https://www.onestepcheckout.com/LICENSE.txt
 */

class Idev_OneStepCheckout_Helper_Message extends Mage_GiftMessage_Helper_Message
{

    public function getInline ($type, Varien_Object $entity,
    $dontDisplayContainer = false)
    {

        $html = parent::getInline($type, $entity, $dontDisplayContainer);

        if (! empty($html)) {
            $block = Mage::getSingleton('core/layout')->createBlock(
                'giftmessage/message_inline'
            )
                ->setId('giftmessage_form_' . $this->_nextId ++)
                ->setDontDisplayContainer($dontDisplayContainer)
                ->setEntity($entity)
                ->setType($type)
                ->setTemplate('onestepcheckout/gift_message.phtml');

            $html = $block->toHtml();
        }

        return $html;
    }
}
