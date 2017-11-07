<?php
/**
 * @category Customer Service Version 3.0
 * @package MageB2B_Sublogin
 * @author AIRBYTES GmbH <info@airbytes.de>
 * @copyright AIRBYTES GmbH
 * @license commercial
 * @date 09.01.2016
 */
class MageB2B_Sublogin_Block_List extends Mage_Core_Block_Template
{
    protected $_subloginCollection;
    protected $_customer;

    protected function _construct()
    {
        $this->_subloginCollection = Mage::getModel('sublogin/sublogin')->getCollection();
        $this->_customer = Mage::getSingleton('customer/session')->getCustomer();
        $this->_subloginCollection->addFieldToFilter('entity_id', array('eq' => $this->_customer->getId()));
    }

    /**
     * @return MageB2B_Sublogin_Model_Sublogin_Collection
     */
    public function getCollection()
    {
        return $this->_subloginCollection;
    }

    /**
     * @return Mage_Customer_Model_Customer
     */
    public function getCustomer()
    {
        return $this->_customer;
    }

    /**
     * @param $type
     * @param $label
     * @return string
     */
    public function getSortlink($type, $label)
    {
        $dir = 'desc';
        $class = 'not-sort';
        if ($type == $this->getRequest()->getParam('sort'))
        {
            $dir = $this->getRequest()->getParam('direction')=='asc'?'desc':'asc';
            $class = 'sort-arrow-'.(($this->getRequest()->getParam('direction')=='asc')?'asc':'desc');
        }
        return '<a class="'.$class.'" href="'.Mage::getUrl('sublogin/frontend/index/', array('sort'=>$type, 'direction'=>$dir)).'">'.
            '<span class="sort-title">'.$label.'</span></a>';
    }

    /**
     * format date
     * @param $date
     * @return string
     */
    public function dateFormat($date)
    {
        if (!$date)
        {
            return '';
        }
        return $this->formatDate($date, Mage_Core_Model_Locale::FORMAT_TYPE_SHORT);
    }

    /**
     * show add new sublogin button if conditions met
     * @return bool
     */
    public function accessNewSubloginButton()
    {
        // main customer logged in
        if (!Mage::getSingleton('customer/session')->getSubloginEmail())
        {
            if ($this->getCustomer()->getCanCreateSublogins())
            {
                if ($this->getCustomer()->getMaxNumberSublogins() == 0)
                {
                    return true;
                }
                else if ($this->getCustomer()->getMaxNumberSublogins() > $this->getCollection()->getSize())
                {
                    return true;
                }
                else
                {
                    return false;
                }
            }
            else
            {
                return false;
            }
        }
        // sublogin is logged in
        else if (Mage::helper('sublogin')->getCurrentSublogin())
        {
            if (Mage::helper('sublogin')->getCurrentSublogin()->getCreateSublogins())
            {
                if ($this->getCustomer()->getMaxNumberSublogins() == 0)
                {
                    return true;
                }
                else if ($this->getCustomer()->getMaxNumberSublogins() > $this->getCollection()->getSize())
                {
                    return true;
                }
                else
                {
                    return false;
                }
            }
            return false;
        }
        else
        {
            return false;
        }
    }

    /**
     * return notice on max sublogins reached
     * @return bool
     */
    public function noticeOnMaxSublogin()
    {
        $maxNumberOfSublogins = (int) $this->getCustomer()->getMaxNumberSublogins();
        if ($maxNumberOfSublogins != 0 && $maxNumberOfSublogins == $this->getCollection()->getSize())
        {
            return true;
        }
        return false;
    }
}
