<?php
class Rock_ProductCPImport_Adminhtml_ProductImportController extends Mage_Adminhtml_Controller_Action
{
    /**
     * Return some checking result
     *
     * @return void
     */
    public function importAction()
    {
        echo "test";
        exit;
        $result = 1;
        Mage::app()->getResponse()->setBody($result);
    }

    public function indexAction(){
        echo "test";
        exit;
    }

    protected function _isAllowed()
    {
        return true;
    }
}
?>