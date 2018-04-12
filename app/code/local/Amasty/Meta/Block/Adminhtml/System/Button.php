<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2018 Amasty (https://www.amasty.com)
 * @package Amasty_Meta
 */


class Amasty_Meta_Block_Adminhtml_System_Button extends Mage_Adminhtml_Block_System_Config_Form_Field
{
    protected function _getElementHtml(Varien_Data_Form_Element_Abstract $element)
	{
		$buttonBlock = Mage::app()->getLayout()->createBlock('adminhtml/widget_button');

		$params = array(
			'store_key' => $buttonBlock->getRequest()->getParam('store')
		);

        $updateParams = array(
            'init_url' => $this->getUrl("adminhtml/ammeta_url/init", $params),
            'process_url' => $this->getUrl("adminhtml/ammeta_url/process", $params),
            'conclude_url' => $this->getUrl("adminhtml/ammeta_url/done")
        );

        $encodedParams = Mage::helper('core')->jsonEncode($updateParams);
        $encodedParams = str_replace('"', '\'', $encodedParams);

		$data = array(
			'label'     => Mage::helper('ammeta')->__('Apply Template For Product URLs'),
			'onclick'   => '(new amUrlUpdate('.$encodedParams.','
                . '$(\'ammeta_product_url_template\').getValue())).start()',
			'class'     => '',
		);

		$buttonBlock->setData($data);

        $applyBlock = Mage::app()->getLayout()->createBlock('core/template');

        $applyBlock
            ->setTemplate('amasty/ammeta/apply.phtml')
            ->setButton($buttonBlock)
        ;

		return $applyBlock->toHtml();
	}
}