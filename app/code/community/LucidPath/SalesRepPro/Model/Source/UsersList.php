<?php
class LucidPath_SalesRepPro_Model_Source_UsersList {

  public static function toOptionArray() {
    $collection = Mage::getResourceModel('admin/user_collection')->setOrder('firstname', 'asc')->load();

    $result   = array();
    $result[] = array('value' => "0", 'label' => "No Sales Representative");

    foreach ($collection as $admin) {
      $result[] = array('value' => $admin->getId(), 'label' => $admin->getFirstname() .' '. $admin->getLastname());
    }

    return $result;
  }
}
?>
