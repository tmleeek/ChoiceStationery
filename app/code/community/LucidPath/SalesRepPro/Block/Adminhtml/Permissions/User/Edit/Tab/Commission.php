<?php
class LucidPath_SalesRepPro_Block_Adminhtml_Permissions_User_Edit_Tab_Commission extends Mage_Adminhtml_Block_Widget_Form
                                                                              implements Mage_Adminhtml_Block_Widget_Tab_Interface {

  public function __construct() {
    parent::__construct();
  }

  protected function _prepareForm() {
    $model = Mage::registry('permissions_user');

    $form = new Varien_Data_Form();

    $form->setHtmlIdPrefix('user_');

    $fieldset = $form->addFieldset('base_fieldset', array('legend'=>Mage::helper('adminhtml')->__('User Commission Rate')));

    if ($model->getUserId()) {
      $fieldset->addField('user_id', 'hidden', array('name' => 'user_id'));
    } else {
      if (! $model->hasData('is_active')) {
        $model->setIsActive(1);
      }
    }

    $fieldset->addField('salesrep_commission_rate',
                        'text',
                        array(
                              'name'  => 'salesrep_commission_rate',
                              'label' => Mage::helper('adminhtml')->__('Commission Rate'),
                              'id'    => 'salesrep_commission_rate',
                              'title' => Mage::helper('adminhtml')->__('Commission Rate'),
                              'required' => false,
                              'note' => Mage::helper('salesrep')->__('If left blank, the default will be used (specified under System -> Config -> Sales Representative Pro)'))
    );

    $fieldset->addField('user_roles',
                        'hidden',
                        array(
                              'name' => 'user_roles',
                              'id'   => '_user_roles')
    );

    $data = $model->getData();

    $form->setValues($data);
    $this->setForm($form);

    return parent::_prepareForm();
  }

  /**
  * Prepare label for tab
  *
  * @return string
  */
  public function getTabLabel() {
    return $this->__('Commission');
  }

  /**
  * Prepare title for tab
  *
  * @return string
  */
  public function getTabTitle() {
    return $this->__('Commission');
  }

  /**
  * Returns status flag about this tab can be shown or not
  *
  * @return true
  */
  public function canShowTab() {
    return true;
  }

  /**
  * Returns status flag about this tab hidden or not
  *
  * @return true
  */
  public function isHidden() {
    return false;
  }
}
?>
