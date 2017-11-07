<?php 

class Rock_CoreOverride_Model_Observer extends Varien_Event_Observer
{
    /**
     * Adds column to admin customers grid
     *
     * @param Varien_Event_Observer $observer
     * @return Atwix_CustomGrid_Model_Observer
     */
    public function appendCustomColumn(Varien_Event_Observer $observer)
    {
		
        /* $block = $observer->getBlock();
        if (!isset($block)) {
            return $this;
        }
	
        if ($block->getType() == 'adminhtml/catalog_product_grid') {
			//echo "test";exit;
            /* @var $block Mage_Adminhtml_Block_Customer_Grid */
         /*   $block->addColumnAfter('status', array(
                'header'    => 'status',
                'type'      => 'text',
                'index'     => 'status',
            ), 'sku'); 
        } */
    }
}