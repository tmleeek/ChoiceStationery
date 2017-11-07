<?php
class LucidPath_SalesRep_Helper_Data extends Mage_Checkout_Helper_Data {
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
}
?>