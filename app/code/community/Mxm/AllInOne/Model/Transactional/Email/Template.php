<?php

class Mxm_AllInOne_Model_Transactional_Email_Template extends Mage_Core_Model_Email_Template
{
    public function getMail()
    {
        $mail = parent::getMail();
    	if (Mage::helper('mxmallinone/transactional')->isEnabled()) {
            $transport = Mage::helper('mxmallinone/transactional')->getTransport();
            $mail->setDefaultTransport($transport);
		}

        if ($this->getTemplateFilter() instanceof Mage_Newsletter_Model_Template_Filter) {
            $mail->addHeader('x-mxm-tag', 'magento,newsletter', false);
        } else {
            $templateCategory = $this->getTemplateCategory();
            $mail->addHeader('x-mxm-tag', "magento,transactional,$templateCategory", false);
        }

        return $mail;
    }

    public function getTemplateFilter()
    {
        if (empty($this->_templateFilter)) {
            $this->_templateFilter = Mage::getModel('widget/template_filter');
            $this->_templateFilter->setUseAbsoluteLinks($this->getUseAbsoluteLinks())
                ->setStoreId($this->getDesignConfig()->getStore());
        }
        return $this->_templateFilter;
    }

    /**
     * Gets an implied (rather than official) template category.
     *
     * @return string
     */
    public function getTemplateCategory()
    {
        // template could be built in (id is string) or custom (id is numeric)
        // custom templates are related to an original built in template
        if (is_numeric($this->getId())) {
            $templateCategory = $this->getData('orig_template_code');
        } else {
            $templateCategory = $this->getId();
        }

        // remove template naming and tidy up
        $templateCategory = trim(str_replace('template', '', $templateCategory), '_ ');
        if (empty($templateCategory)) {
            $templateCategory = 'unknown';
        }
        
        return $templateCategory;
    }
}