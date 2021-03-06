<?php
/**
 * Lucid Path Consulting SalesRep Extension
 *
 * LICENSE
 *
 *  1.  This is an agreement between Licensor and Licensee, who is being licensed to use the named Software.
 *  2.  Licensee acknowledges that this is only a limited nonexclusive license. Licensor is and remains the owner of all titles, rights, and interests in the Software.
 *  3.  This License permits Licensee to install the Software one (1) Magento web store per purchase. Licensee will not duplicate, reproduce, alter, or resell software.
 *  4.  This software is provided as-is with no warranty or guarantee whatsoever.
 *  5.  In the event of a defect or malfunction of the software, refunds or exchanges will be provided at the sole discretion of the licensor. Licensor reserves the right to refuse a refund, and maintains the policy that "all sales are final."
 *  6.  LICENSOR IS NOT LIABLE TO LICENSEE FOR ANY DAMAGES, INCLUDING COMPENSATORY, SPECIAL, INCIDENTAL, EXEMPLARY, PUNITIVE, OR CONSEQUENTIAL DAMAGES, CONNECTED WITH OR RESULTING FROM THIS LICENSE AGREEMENT OR LICENSEE'S USE OF THIS SOFTWARE.
 *  7.  Licensee agrees to defend and indemnify Licensor and hold Licensor harmless from all claims, losses, damages, complaints, or expenses connected with or resulting from Licensee's business operations.
 *  8.  Licensor has the right to terminate this License Agreement and Licensee's right to use this Software upon any material breach by Licensee.
 *  9.  Licensee agrees to return to Licensor or to destroy all copies of the Software upon termination of the License.
 *  10. This License Agreement is the entire and exclusive agreement between Licensor and Licensee regarding this Software. This License Agreement replaces and supersedes all prior negotiations, dealings, and agreements between Licensor and Licensee regarding this Software.
 *  11. This License Agreement is governed by the laws of California, applicable to California contracts.
 *  12. This License Agreement is valid without Licensor's signature. It becomes effective upon the download of the Software. *
 *
 * @category   LucidPath
 * @package    LucidPath_SalesRep
 * @author     Yuriy Malov
 * @copyright  Copyright (c) 2013 Lucid Path Consulting (http://www.lucidpathconsulting.com/)
 */

class LucidPath_SalesRep_Block_Adminhtml_Permissions_User_Edit_Tab_Commission extends Mage_Adminhtml_Block_Widget_Form
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