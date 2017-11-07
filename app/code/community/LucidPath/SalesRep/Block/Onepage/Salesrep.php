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

class LucidPath_SalesRep_Block_Onepage_SalesRep extends Mage_Checkout_Block_Onepage_Abstract {

  protected function _construct() {
    $this->getCheckout()->setStepData('salesrep', array(
      'label'   => Mage::helper('checkout')->__(Mage::getStoreConfig('salesrep/step_setup/step_header')),
      'is_show' => true
    ));

    parent::_construct();
  }
}
?>