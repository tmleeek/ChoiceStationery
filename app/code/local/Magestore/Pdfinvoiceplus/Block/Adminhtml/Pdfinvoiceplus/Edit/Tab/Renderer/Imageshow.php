<?php

class Magestore_Pdfinvoiceplus_Block_Adminhtml_Pdfinvoiceplus_Edit_Tab_Renderer_Imageshow extends Mage_Adminhtml_Block_Widget implements Varien_Data_Form_Element_Renderer_Interface {
    public function render(Varien_Data_Form_Element_Abstract $element) {
        $i = 1;
        $system_templates = Mage::getModel('pdfinvoiceplus/systemtemplate')->getCollection();
        $currentId = Mage::getModel('pdfinvoiceplus/template')
                    ->load($this->getRequest()->getParam('id'))
                    ->getSystemTemplateId();
        $html = '<table cellspacing="0"> 
                    <tr>';
        foreach ($system_templates as $system_template) {
            $checked = '';
            if($system_template->getId() == $currentId)
                $checked = 'checked';
            if(!$this->getRequest()->getParam('id') && $i == 1)
                $checked = 'checked';
            $html.= '<td style="padding:10px" align="center">
               <div style="width:200px;height:200px;overflow:hidden"><img name="system_template_'.$system_template->getId().'" id="' . $system_template->getId() . '" onclick="checkingImg(' . $system_template->getId() . ')" src="' . Mage::getBaseUrl(Mage_Core_Model_Store::URL_TYPE_MEDIA).'magestore/pdfinvoiceplus/'.$system_template->getImage() . '" alt="" width="180"/></div>
                   <div style="text-align:center"><input type="radio" '.$checked.'  name="system_template_id" id="radio_template_' . $system_template->getId() . '" value="'.$system_template->getId().'"/></div>
               <p><label style="cursor:pointer" for="radio_template_' . $system_template->getId() . '"><b>'.$system_template->getTemplateName().'</b></label></p>
                </td>';
            if($i%4 == 0) $html .= '</tr><tr>';
            $i++;
        }
        $html .= '</tr> 
            </table>';
        return $html;
    }

}

?>
