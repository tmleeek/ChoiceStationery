<?php
class Ewall_Autocrosssell_Test_Helper_Data extends EcomDev_PHPUnit_Test_Case {

    public function setup() {

        Ewall_Autocrosssell_Test_Model_Mocks_Foreignresetter::dropForeignKeys();
        parent::setup();
    }

    public function updateRelations($data) {

        if ($data['uid'] == '001') {
            $this->_processUpdateRelationsData($data);
        }

        if ($data['uid'] == '002') {
            $this->_processEmpty($data);
        }

        if (empty($data['relatedIds'])) {
            return Mage::helper('autocrosssell')->updateRelations(array());
        }
    }

    private function _processEmpty($data) {

        $collection = Mage::getModel('autocrosssell/autocrosssell')->getCollection();
        foreach ($collection as $item) {

            $item->delete();
        }

        Mage::helper('autocrosssell')->updateRelations($data['relatedIds']);
        $collection = Mage::getModel('autocrosssell/autocrosssell')->getCollection();
        $this->assertEquals($data['collectionCount'], $collection->count());

        foreach ($collection as $item) {

            $itemProduct = $item->getProductId();
            if (!isset($data['expected'][$itemProduct])) {
                $this->fail("Expected related on product {$itemProduct} has not been created");
            }

            $this->assertEquals($item->getRelatedArray(), $data['expected'][$itemProduct]);
        }
    }

    private function _processUpdateRelationsData($data) {

        $storeId = 1;
        Mage::app()->getStore()->setId($storeId);
        Mage::helper('autocrosssell')->updateRelations($data['relatedIds']);


        $collection = Mage::getModel('autocrosssell/autocrosssell')->getCollection()->addProductFilter(17)->addStoreFilter($storeId);
        $item = $collection->getFirstItem();
        $itemData = unserialize($item->getRelatedArray());


        foreach ($itemData as $key => $val) {

            $this->assertEquals($val, 2, 'Product ties should be increased by 1');
        }

        Mage::app()->getStore()->setId(2);
        Mage::helper('autocrosssell')->updateRelations($data['relatedIds']);


        $collection = Mage::getModel('autocrosssell/autocrosssell')->getCollection()->addProductFilter(17)->addStoreFilter($storeId);
        $item = $collection->getFirstItem();
        $itemData = unserialize($item->getRelatedArray());


        foreach ($itemData as $key => $val) {
            $this->assertEquals($val, 2, 'Product ties should not be increased by 1');
        }
    }

    public function provider__updateRalations() {

        return array(
            array(array('relatedIds' => array(17, 27, 166), 'uid' => '001')),
            array(array('relatedIds' => array())),
            array(array('relatedIds' => array(17, 27), 'uid' => '002', 'expected' => array('17' => 'a:1:{i:27;i:1;}', '27' => 'a:1:{i:17;i:1;}'), 'collectionCount' => 2)),
        );
    }

}
