<?php

class Mxm_AllInOne_Block_System_Config_Form_Field_Syncbutton
    extends Mxm_AllInOne_Block_System_Config_Form_Field_Button {

    protected function getLabelText($element)
    {
        return $this->__('Force Sync');
    }

    protected function getActionUrl($element)
    {
        $syncType = (string)$element->getFieldConfig()->sync_type;
        return Mage::helper('adminhtml')
            ->getUrl("mxmallinone/sync/$syncType");
    }

    protected function _getElementHtml(Varien_Data_Form_Element_Abstract $element)
    {
        $html = parent::_getElementHtml($element);

        if ($element->getScope() !== 'websites') {
            return $html;
        }
        $websiteId = $element->getScopeId();

        $syncTypeName = (string)$element->getFieldConfig()->sync_type;
        $syncType = Mage::helper('mxmallinone/sync')->syncType($syncTypeName);

        $lastSyncText = $this->getLastSyncText($syncType, $websiteId);

        return $html . <<<HTML
<span style="padding-left: 8px; padding-top: 1px;">$lastSyncText</span>
HTML;

    }

    protected function getLastSyncText($syncType, $websiteId)
    {
        $lastSyncTs = Mage::helper('mxmallinone/sync')->getLastSyncTs($syncType, $websiteId);

        if ($lastSyncTs) {
            return $this->__('Last synced: %s', $lastSyncTs);
        }
        return $this->__('Never synced');
    }
} 