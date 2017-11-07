<?php 
class Bintime_Sinchimport_AjaxController extends Mage_Adminhtml_Controller_Action
{
    var
        $_logFile;

    public function UpdateStatusAction() {

        $import=Mage::getModel('sinchimport/sinch');
        $message_arr = $import->getImportStatuses();
        if ($message_arr['id']) {
            // TODO use: $this->getResponse()->setBody(Mage::helper('core')->jsonEncode($result))
            // JSON
             print '{ "message": "'.$message_arr['message'].'", "finished": "'.$message_arr['finished'].'"}';
        }
        else{
            print '{ "message": "", "finished": "0"}';
        }

        return;
    }


    public function indexAction(){
	$sinch=Mage::getModel('sinchimport/sinch');
        $this->_logFile="Sinch.log";
        Mage::log("Start Sinch import", null, $this->_logFile);
//        echo get_class(Mage::getModel('sinchimport/sinch'))."c".get_class(Mage::getModel('purchase/transporttask'));


        echo "Start import <br>";
        $dir = dirname(__FILE__);
        $php_run_string_array = split(";", $sinch->php_run_strings);
        foreach($php_run_string_array as $php_run_string){
            exec("nohup ".$php_run_string." ".$dir."/../sinch_import_start_ajax.php > /dev/null & echo $!", $out);
            sleep(1);
            if (($out[0] > 0) && !$sinch->is_imort_not_run()){
                break;
            }
        }

/*
        $import=Mage::getModel('sinchimport/sinch');
        
        $import->run_sinch_import();
*/	
        echo "Finish import<br>";

    }
    public function stockPriceAction(){
	$sinch=Mage::getModel('sinchimport/sinch');
        $this->_logFile="Sinch.log";
        Mage::log("Start Stock & Price Sinch import", null, $this->_logFile);
//        echo get_class(Mage::getModel('sinchimport/sinch'))."c".get_class(Mage::getModel('purchase/transporttask'));


        echo "Start Stock & Price import <br>";
        $dir = dirname(__FILE__);
        $php_run_string_array = split(";", $sinch->php_run_strings);
        foreach($php_run_string_array as $php_run_string){
            exec("nohup ".$php_run_string." ".$dir."/../stock_price_sinch_import_start_ajax.php > /dev/null & echo $!", $out);
            sleep(1);
            if (($out[0] > 0) && !$sinch->is_imort_not_run()){
                break;
            }
        }

/*
        $import=Mage::getModel('sinchimport/sinch');
        
        $import->run_sinch_import();
*/	
        echo "Finish Stock & Price import<br>";
    }	
}    
