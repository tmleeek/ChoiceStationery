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

class LucidPath_SalesRep_Adminhtml_OrderController extends Mage_Adminhtml_Controller_Action {

  /**
   * Check current user permission on resource and privilege
   *
   * Mage::getSingleton('admin/session')->isAllowed('admin/catalog')
   * Mage::getSingleton('admin/session')->isAllowed('catalog')
   *
   * @param   string $user
   * @param   string $resource
   * @return  boolean
   */
  public function isAllowed($user, $resource) {
    $acl = Mage::getResourceModel('admin/acl')->loadAcl();

    if (!preg_match('/^admin/', $resource)) {
      $resource = 'admin/'.$resource;
    }

    try {
      return $acl->isAllowed($user->getAclRole(), $resource);
    } catch (Exception $e) {
      try {
        if (!$acl->has($resource)) {
          return $acl->isAllowed($user->getAclRole(), null);
        }
      } catch (Exception $e) { }
    }
    return false;
  }

  public function changeSalesrepAction() {
    if (Mage::getSingleton('admin/session')->isAllowed('sales/order/actions/salesrep/change_salesrep_name')) {
      if ($this->getRequest()->isPost() && $this->getRequest()->getPost('order_id')) {
        Mage::helper('salesrep')->getCommissionEarned($this->getRequest()->getPost('order_id'), $this->getRequest()->getPost('salesrep_user_id'));

    	$_order   = Mage::getModel('sales/order')->load($this->getRequest()->getPost('order_id'));
    	$salesrep = Mage::getModel('salesrep/salesrep')->loadByOrder($_order);
        $this->getResponse()->setBody(Mage::helper('core')->jsonEncode(array('success' => 1, 'salesrep' => $this->getSalesrepOutput($salesrep))));
      }
    }
  }
  
  
  public function changeSalesrepRepAction() {
    if (Mage::getSingleton('admin/session')->isAllowed('sales/order/actions/salesrep/change_salesrep_name')) {
      if ($this->getRequest()->isPost() && $this->getRequest()->getPost('order_id')) {
      	$order_id = $this->getRequest()->getPost('order_id');
      	$rep_id   = $this->getRequest()->getPost('salesrep_rep_id');
        Mage::helper('salesrep')->getCommissionEarned($this->getRequest()->getPost('order_id'), $this->getRequest()->getPost('salesrep_rep_id'));
        
    	$_order   = Mage::getModel('sales/order')->load($this->getRequest()->getPost('order_id'));
    	$salesrep = Mage::getModel('salesrep/salesrep')->loadByOrder($_order);
        $this->getResponse()->setBody(Mage::helper('core')->jsonEncode(array('success' => 1, 'salesrep' => $this->getSalesrepOutput($salesrep))));
      }
    }
  }

  public function changeCommissionStatusAction() {
    if (Mage::getSingleton('admin/session')->isAllowed('sales/order/actions/salesrep/change_salesrep_commission_status')) {
      if ($this->getRequest()->isPost() && $this->getRequest()->getPost('order_id')) {
        if (is_array($this->getRequest()->getPost('order_id'))) {
          foreach ($this->getRequest()->getPost('order_id') as $order_id) {
            $salesrep = Mage::getModel('salesrep/salesrep')->loadByOrder($order_id);
            $salesrep->setCommissionStatus($this->getRequest()->getPost('salesrep_commission_status'));
            $salesrep->save();
          }
        } else {
          $salesrep = Mage::getModel('salesrep/salesrep')->loadByOrder($this->getRequest()->getPost('order_id'));
          $salesrep->setCommissionStatus($this->getRequest()->getPost('salesrep_commission_status'));
          $salesrep->save();
        }
      }
    }
  }
  
  private function getSalesrepOutput($salesrep) {
    $result = array();

    $result['admin_id']   = $salesrep->getAdminId();
    $result['admin_name'] = $salesrep->getAdminName();
    $result['rep_commission_earned']      = $salesrep->getCommissionEarned();
    $result['rep_commission_earned_text'] = Mage::helper('core')->currency($salesrep->getCommissionEarned(), true, false);
    $result['rep_commission_status'] = $salesrep->getCommissionStatus();

    return $result;
  }
    //Added by quickfix script. Take note when upgrading this module! Powered by SupportDesk (www.supportdesk.nu)
    function _isAllowed()
    {
        return Mage::getSingleton('admin/session')->isAllowed('sales/order/actions/salesrep/change_salesrep_commission_status');
    }
}
?>