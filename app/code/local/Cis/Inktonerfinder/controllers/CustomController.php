<?php
class Cis_Inktonerfinder_CustomController extends Mage_Core_Controller_Front_Action
{
    
    public function getBrandAction()
    {
        $configOptions = Mage::helper('inktonerfinder')->getStoreConfigvalues();
        $url = $configOptions['brands_url'].'uid='.$configOptions['user_id'].'&security_key='.$configOptions['key'].'&password='.$configOptions['password'].'&version='.$configOptions['version'].'&language='.$configOptions['language'].'&source='.$configOptions['base_url'];
				echo file_get_contents ( $url );
    }
    
    public function getModelsAndModelSeriesAjaxAction()
    {
			$manufactureId = $this->getRequest()->getParam('manufacture');
			$configOptions = Mage::helper('inktonerfinder')->getStoreConfigvalues();
			if($configOptions['remove_modelseries'] == 1)
				$Url = $configOptions['modelseries_url'].'brand_id='.$manufactureId.'&uid='.$configOptions['user_id'].'&security_key='.$configOptions['key'].'&password='.$configOptions['password'].'&version='.$configOptions['version'].'&language='.$configOptions['language'].'&source='.$configOptions['base_url'];
			else
				$Url = $configOptions['modeltype_url'].'brand_id='.$manufactureId.'&uid='.$configOptions['user_id'].'&security_key='.$configOptions['key'].'&password='.$configOptions['password'].'&version='.$configOptions['version'].'&language='.$configOptions['language'].'&source='.$configOptions['base_url'];
			echo file_get_contents($Url);
    }
    
    public function getModelsListAjaxAction()
    {
        $manufactureId = $this->getRequest()->getParam('manufacture');
        $modelserieId =  $this->getRequest()->getParam('modelseries');
        
        $html .= '<option value="">Select Model</option>';     
        $configOptions = Mage::helper('inktonerfinder')->getStoreConfigvalues();
            
        $url = $configOptions['model_url'].'brand_id='.$manufactureId.'&modelserie_id='.$modelserieId.'&uid='.$configOptions['user_id'].'&security_key='.$configOptions['key'].'&password='.$configOptions['password'].'&version='.$configOptions['version'].'&language='.$configOptions['language'].'&source='.$configOptions['base_url'];
       echo $types =  file_get_contents ( $url );
    }
    
    public function productsAutoCompletePrototypeAction()
    {
        $term = $this->getRequest()->getParam('term');
        $configOptions = Mage::helper('inktonerfinder')->getStoreConfigvalues();
        $term = rawurlencode($term);
        $url = $configOptions['model_text_search'].'q='.$term.'&uid='.$configOptions['user_id'].'&security_key='.$configOptions['key'].'&password='.$configOptions['password'].'&version='.$configOptions['version'].'&language='.$configOptions['language'].'&source='.$configOptions['base_url'];
        //print_r($url);die();
        echo file_get_contents ($url);      
        //$typesRecords  =  json_decode ( $products, true ) ;       
        /*$html =  '<ul>';
        foreach( $typesRecords as $typesName){
          
                foreach( $typesName as $type){                  
                    $html .= '<li id ="'.$type["id"].'">'.$type["val"].'</li>';                  
                 }
        }     
        $html .=  '</ul>';
        echo $html;*/
    }    
}