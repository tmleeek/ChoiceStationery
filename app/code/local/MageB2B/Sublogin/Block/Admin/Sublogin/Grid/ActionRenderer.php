<?php
/**
 * @category Customer Service Version 3.0
 * @package MageB2B_Sublogin
 * @author AIRBYTES GmbH <info@airbytes.de>
 * @copyright AIRBYTES GmbH
 * @license commercial
 * @date 09.01.2016
 */
class MageB2B_Sublogin_Block_Admin_Sublogin_Grid_ActionRenderer extends Mage_Adminhtml_Block_Widget_Grid_Column_Renderer_Abstract
{
    public function render(Varien_Object $row)
    {
        $actions = array();
        $actions[] = array(
            '@'=>  array(
                'href'  => $this->getUrl('adminhtml/customer/edit',
                    array(
                        'id' => $row->getEntityId(),
                    )
                ),
                'target'=>'_blank'
            ),
            '#' => Mage::helper('sublogin')->__('Edit Customer')
        );
        $actions[] = array(
            '@'=>  array(
                'href'  => $this->getUrl('adminhtml/sublogin_index/edit',
                    array(
                        'id' => $row->getId(),
                    )
                ),
                'target'=>'_self'
            ),
            '#' => Mage::helper('sublogin')->__('Edit Sublogin')
        );
        $actions[] = array(
            '@'=>  array(
                'href'  => $this->getUrl('adminhtml/sublogin_index/deleteSingleSublogin',
                    array(
                        'id' => $row->getId(),
                    )
                ),
                'target'=>'_self'
            ),
            '#' => Mage::helper('sublogin')->__('Delete Sublogin')
        );

        return $this->_actionsToHtml($actions);
    }

    /**
     * @param $value
     * @return string
     */
    protected function _getEscapedValue($value)
    {
        return addcslashes(htmlspecialchars($value),'\\\'');
    }

    /**
     * @param array $actions
     * @return string
     */
    protected function _actionsToHtml(array $actions)
    {
        $html = array();
        $attributesObject = new Varien_Object();
        foreach ($actions as $action)
        {
            $attributesObject->setData($action['@']);
            $html[] = '<a ' . $attributesObject->serialize() . '>' . $action['#'] . '</a>';
        }
        return implode('<span class="separator">&nbsp;|&nbsp;</span>', $html);
    }

}
