<?php
/* @var $task Mxm_AllInOne_Model_Setup_Task */
$task = $this;
/* @var $setup Mxm_AllInOne_Model_Setup */
$setup = $task->getSetup();
/* @var $facts Mxm_AllInOne_Model_Setup_Facts */
$facts = $setup->getFacts();

$basketTypesIndexed = $setup->getApi('basket_type')->fetchAll();
$basketTypes = array();
foreach ($basketTypesIndexed as $type) {
    $basketTypes[$type['basket_name']] = $type;
}
unset($basketTypesIndexed);

$stages = array('browsing', 'billing', 'shipping', 'shipping_method', 'payment', 'review');

foreach ($setup->getStores() as $storeId => $store) {
    $basketName = "Magento_{$store->getCode()}";
    if (isset($basketTypes[$basketName])) {
        $basket = $basketTypes[$basketName];
        $id = $basket['basket_type_id'];
        $setup->log("Found basket type $basketName (ID: $id)");
    } else {
        $id = $setup->getApi('basket_type')->insert(array(
            'basket_name'       => $basketName,
            'stages'            => $stages,
            'trigger_frequency' => 1
        ));
        $basket = $setup->getApi('basket_type')->find($id);
        $setup->log("Created basket type $basketName (ID: $id)");
    }
    $facts->setBasketType($basketName, $id);
    if ($setup->isMultiStore()) {
        Mage::helper('mxmallinone/sca')->setBasketType(
            $basket['basket_type_id'],
            $basket['security_salt'],
            $storeId
        );
        $setup->log("Set basket type for store $storeId");
    } else {
        Mage::helper('mxmallinone/sca')->setBasketType(
            $basket['basket_type_id'],
            $basket['security_salt']
        );
        $setup->log("Set basket type for default scope");
    }
}
