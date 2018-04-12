<?php

class Inchoo_FeaturedProducts_SearchcustomController extends Mage_Core_Controller_Front_Action
{
    public function index1Action()
    {
		 $term=$this->getRequest()->getParam('term');
		
         $term = preg_replace('/([A-Z,a-z]{1,})([\d])/','\1 \2', $term);
		Mage::app()->getStore()->setConfig(
        Mage_Catalog_Helper_Product_Flat::XML_PATH_USE_PRODUCT_FLAT, '0');
        
        $collection = Mage::getModel('catalog/product')->getCollection()
                     ->addAttributeToSelect("*")
		             ->addFieldToFilter('printer_search', array(
		array('like' => '%'.$term.'%'), 
		array('like' => '%'.$term), 
		array('like' => $term.'%') 
		));
         $collection->load(); 
      /*  echo  $collection->getSelect();
        exit;*/
        
        $attributeId = Mage::getResourceModel('eav/entity_attribute')->getIdByCode('catalog_product','model');
		$attribute = Mage::getModel('catalog/resource_eav_attribute')->load($attributeId);
		$attributeOptions = $attribute ->getSource()->getAllOptions();
		
		 
		
    // echo $collection->getSelect();
		foreach ($attributeOptions as $data){
		
		 $result[$data['value']] = $data['label'];
		}
		$termArray=array(); $termfinal='';
		
		$termArray=explode(" ",$term);
		//echo "<pre>"; print_r($termArray);exit;
		foreach($termArray as $tArray)
		{
			$termfinal.=$tArray.' || ';
		}
		//echo $termfinal;exit;
		$resultFinal = array_filter($result, function ($item) use ($termfinal) {
			/*if (stripos($item, substr($termfinal, 0, -2)) !== false) {
				return true;
			}*/
			
		return true;
	});
		echo json_encode($resultFinal);

    }
    public function indexAction()
    {
		$term1=$this->getRequest()->getParam('term');
		$term = preg_replace('/([A-Z,a-z]{1,})([\d])/','\1 \2', $term1);
		//$term= preg_replace('/(\d+)/', '${1} ', $term1);
		$termArray=array(); $termfinal='';
		$resultfinal=array();
		$write = Mage::getSingleton("core/resource")->getConnection("core_write");
		$termArray=explode(" ",strtolower($term));
		
		$termfinal1=$termArray[0];
		$termfinal2=$termArray[1];
		$termfinal3=$termArray[2];
		$sql_check_link_id="SELECT model FROM printer_model where model LIKE '%".$termfinal1."%' AND model LIKE '%".$termfinal2."%' AND model LIKE '%".$termfinal3."%' ";
        $data=$write->FetchAll($sql_check_link_id);
        
        
        foreach($data as $dresult)
        {
			foreach($dresult as $ddata){
			$resultfinal[]=$ddata;
		   }
		}
		//echo "<pre>"; print_r($resultfinal);exit;
		echo json_encode($resultfinal);
	}
    
    
}
