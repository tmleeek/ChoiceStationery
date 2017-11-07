<?php
/**
 * @category Customer Service Version 3.0
 * @package MageB2B_Sublogin
 * @author AIRBYTES GmbH <info@airbytes.de>
 * @copyright AIRBYTES GmbH
 * @license commercial
 * @date 09.01.2016
 */
class MageB2B_Sublogin_Block_Admin_Autocompleter extends Mage_Adminhtml_Block_Widget_Form
    implements Varien_Data_Form_Element_Renderer_Interface
{
    protected $_usecase;
    /**
     * Form element instance
     */
    protected $_element;

    protected $_autocompleteData = array();

    public function setAutocompleteData($data)
    {
        $this->_autocompleteData = $data;
    }

    public function getAutoCompleteData($id=null)
    {
        if ($id !== null)
        {
            if (isset($this->_autocompleteData[$id]))
                return $this->_autocompleteData[$id]['label'];
            else
                return '';
        }
        return $this->_autocompleteData;
    }

    /**
     * Define customer price template file
     *
     */
    public function __construct()
    {
        $this->setTemplate('sublogin/autocomplete.phtml');
    }

    /**
     * Retrieve current edit product instance
     *
     * @return Mage_Catalog_Model_Product
     */
    public function getProduct()
    {
        return Mage::registry('product');
    }

    /**
     * Render HTML
     *
     * @param Varien_Data_Form_Element_Abstract $element
     * @return string
     */
    public function render(Varien_Data_Form_Element_Abstract $element)
    {
        $this->setElement($element);
        return $this->toHtml();
    }

    /**
     * Set form element instance
     *
     * @param Varien_Data_Form_Element_Abstract $element
     * @return MageB2B_Pricesystem_Block_Tableinput
     */
    public function setElement(Varien_Data_Form_Element_Abstract $element)
    {
        $this->_element = $element;
        return $this;
    }

    /**
     * Retrieve form element instance
     */
    public function getElement()
    {
        return $this->_element;
    }

    /**
     * Sort customer price values callback method
     *
     * @param array $a
     * @param array $b
     * @return int
     */
    protected function _sortCustomerPrices($a, $b)
    {
        if ($a['website_id'] != $b['website_id']) {
            return $a['website_id'] < $b['website_id'] ? -1 : 1;
        }
        if ($a['customer'] != $b['customer']) {
            return $a['customer'] < $b['customer'] ? -1 : 1;
        }
        if ($a['price_qty'] != $b['price_qty']) {
            return $a['price_qty'] < $b['price_qty'] ? -1 : 1;
        }

        return 0;
    }

    /**
     * Retrieve allowed customers
     *
     * @param int $customerId  return name by customer id
     * @return array|string
     */
    protected function formatCustomerId($customerId)
    {
        if (!Mage::getStoreConfig('customer_id/customer_id/auto_increment'))
            return $customerId;
        if (!Mage::getStoreConfig('customer_id/customer_id/template_force_apply'))
            return $customerId;
        if (!$customerId)
            return $customerId;

        // no number inside customer_id
        if (!preg_match('/[1-9]+[0-9]*/', $customerId, $result))
            return $customerId;
        $number = $result[0];
        $template = Mage::getStoreConfig('customer_id/customer_id/id_template');
        $p = sscanf($customerId, $template);
        if (!is_array($p) || !isset($p[0]) || !$p[0])
        {
            return sprintf($template, $number);
        }
        return $customerId;
    }

    public function getCustomers()
    {
        $collection = Mage::getResourceModel('customer/customer_collection')
            ->addNameToSelect()
            ->addAttributeToSelect('customer_id')
            ->joinAttribute('billing_postcode', 'customer_address/postcode', 'default_billing', null, 'left')
            ->joinAttribute('billing_city', 'customer_address/city', 'default_billing', null, 'left');
        if ($this->websiteFilter)
            $collection->addFieldToFilter('website_id', array('in', array($this->websiteFilter)));
        $ret = array();
        foreach ($collection as $item)
        {
            $label = $item->getName();
            if($item->getBillingPostcode())
                $label .= ' / ' . $item->getBillingCity() . ' / ' . $item->getBillingPostcode();
            if($cId = $this->formatCustomerId($item->getCustomerId()))
                $label .= ' / '. $cId;
            $ret[$item->getId()] = array(
                'id' => $item->getId(),
                'label' => $label,
            );
        }

        return $ret;
    }
    
    public function getSublogins()
    {
		$sublogins = Mage::getModel('sublogin/sublogin')->getCollection();
		$ret = array();
		if ($sublogins->count())
		{
			foreach ($sublogins as $sublogin)
			{
				$ret[$sublogin->getId()] = array(
					'id' => $sublogin->getId(),
					'label' => $sublogin->getEmail(),
				);
			}
		}
		return $ret;
	}

    public function getProducts()
    {
        $collection = Mage::getResourceModel('catalog/product_collection')
            ->addAttributeToSelect('name');
        if ($this->websiteFilter)
            $collection->addWebsiteFilter($this->websiteFilter);
        $ret=array();
        foreach ($collection as $data)
            $ret[$data->getId()] = array('id'=>$data->getId(),'label'=>$data->getName().' ('.$data->getSku().')');
        return $ret;
    }
}
