<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2018 Amasty (https://www.amasty.com)
 * @package Amasty_Meta
 */
class Amasty_Meta_Block_Adminhtml_Widget_Form_Tab_Abstract_Product extends Amasty_Meta_Block_Adminhtml_Widget_Form_Tab_Abstract_Tab
{
	protected function _addFieldsToFieldset(Varien_Data_Form_Element_Fieldset $fieldSet)
	{
		$hlp = Mage::helper('ammeta');
		
		$fieldSet->addField(
			$this->_prefix . 'product_meta_title',
			'text',
			array(
				'label' => $hlp->__('Title'),
				'name'  => $this->_prefix . 'product_meta_title',
                'note'  => $hlp->__('
Example: Buy {name} [by {manufacturer|brand}] [of {color} color] [for only {price}] [in {categories}] at [{store},] {website}.	
<br/>							
<br/>Available variables:  
<br/>Category - {category}
<br/>All Categories - {categories}
<br/>Store View - {store_view}
<br/>Store      - {store}
<br/>Website    - {website}
<br/>Price - {price}
<br/>Special Price - {special_price}
<br/>Final Price - {final_price}
<br/>Final Price with Tax - {final_price_incl_tax}
<br/>Price From (bundle) - {startingfrom_price}
<br/>Price To (bundle) - {startingto_price}
<br/>Brand - {brand}
<br/>Color - {color}
<br/>And other product attributes ...'),
			) 
		);

		$fieldSet->addField(
			$this->_prefix . 'product_meta_description',
			'textarea',
			array(
				'label' => $hlp->__('Meta Description'),
				'name'  => $this->_prefix . 'product_meta_description',
			)
		);

		$fieldSet->addField(
			$this->_prefix . 'product_meta_keywords',
			'textarea',
			array(
				'label' => $hlp->__('Keywords'),
				'name'  => $this->_prefix . 'product_meta_keywords',
			)
		);

		$fieldSet->addField(
			$this->_prefix . 'product_h1_tag',
			'text',
			array(
				'label' => $hlp->__('H1 Tag'),
				'name'  => $this->_prefix . 'product_h1_tag',
				'note'  => $hlp->__('This value will override any H1 tag even it is not empty')
			)
		);

		$fieldSet->addField(
			$this->_prefix . 'product_short_description',
			'editor',
			array(
				'label'   => $hlp->__('Short Description'),
				'name'    => $this->_prefix . 'product_short_description',
                'style'   => 'width:700px; height:200px;',
                'wysiwyg' => true,
                'config'  => Mage::getSingleton('ammeta/wysiwygConfig')->getConfig(),
			)
		);

		$fieldSet->addField(
			$this->_prefix . 'product_description',
			'editor',
			array(
				'label'   => $hlp->__('Full Description'),
				'name'    => $this->_prefix . 'product_description',
                'style'   => 'width:700px; height:200px;',
                'wysiwyg' => true,
                'config'  => Mage::getSingleton('ammeta/wysiwygConfig')->getConfig(),
			)
		);
	}
	
}