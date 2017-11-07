<?php 
class Bintime_Sinchimport_IndexController extends Mage_Adminhtml_Controller_Action
{
    var
        $_logFile;

    public function indexAction(){
        $this->_logFile="Sinch.log";
        Mage::log("Start Sinch import", null, $this->_logFile);
//        echo get_class(Mage::getModel('sinchimport/sinch'))."c".get_class(Mage::getModel('purchase/transporttask'));


        echo "Start import <br>";

        $import=Mage::getModel('sinchimport/sinch');
        
        $import->run_sinch_import();
	
        echo "Finish import<br>";

    }

    public function stockPriceImportAction(){
	$this->_logFile="Sinch.log";
        Mage::log("Start Stock & Price Sinch import", null, $this->_logFile);
//        echo get_class(Mage::getModel('sinchimport/sinch'))."c".get_class(Mage::getModel('purchase/transporttask'));

        echo "Start Stock & Price import <br>";

        $import=Mage::getModel('sinchimport/sinch');
        
        $import->run_stock_price_sinch_import();
	
        echo "Finish Stock & Price import<br>";


    }	

    public function splitFeaturesAction()
    {	
        $resource = Mage::getResourceModel('sinchimport/layer_filter_feature');
        $resource->splitProductsFeature(null);
        $this->getResponse()->setBody('<h2>done.</h2>');
    }
}    
