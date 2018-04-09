<?php
/**
 * Mirasvit
 *
 * This source file is subject to the Mirasvit Software License, which is available at http://mirasvit.com/license/.
 * Do not edit or add to this file if you wish to upgrade the to newer versions in the future.
 * If you wish to customize this module for your needs
 * Please refer to http://www.magentocommerce.com for more information.
 *
 * @category  Mirasvit
 * @package   Sphinx Search Ultimate
 * @version   2.3.1
 * @revision  601
 * @copyright Copyright (C) 2013 Mirasvit (http://mirasvit.com/)
 */


class Mirasvit_SearchIndex_Helper_Data extends Mage_Core_Helper_Abstract
{
    public function getSearchEngine()
    {
        $engine = null;

        if (Mage::helper('mstcore')->isModuleInstalled('Mirasvit_SearchSphinx')) {
            $engine = Mage::helper('searchsphinx')->getEngine();
        } elseif (Mage::helper('core')->isModuleEnabled('Mirasvit_SearchShared')) {
            $engine = Mage::getSingleton('searchshared/engine_fulltext');
        }

        return $engine;
    }

    public function prepareString($string)
    {
        $string = strip_tags($string);
        $string = str_replace('|', ' ', $string);
        $string = ' '.$string.' ';

        $expressions = Mage::getSingleton('searchindex/config')->getMergeExpressins();

        foreach ($expressions as $expr) {
            $matches = null;
            preg_match_all($expr['match'], $string, $matches);

            foreach ($matches[0] as $math) {
                $math = preg_replace($expr['replace'], $expr['char'], $math);
                $string .= ' '.$math;
            }
        }

        return $string;
    }
    
    public function prepareCategory()
    {
		 $query  = Mage::helper('catalogsearch')->getEscapedQueryText();
		 $term = preg_replace('/([A-Z,a-z]{1,})([\d])/','\1 \2', $query);
		//$term= preg_replace('/(\d+)/', '${1} ', $term1);
		$termArray=array(); $termfinal='';
		$resultfinal=array();
		$write = Mage::getSingleton("core/resource")->getConnection("core_write");
		$termArray=explode(" ",strtolower($term));
		
		
		
		$termfinal1=$termArray[0];
		$termfinal2=$termArray[1];
		$termfinal3=$termArray[2];
		$termfinal4=$termArray[4];
		
		
		/*$make = array("Brother", "Canon", "Dell", "Dymo", "Epson", "Hp", "IBM", "Kodak", "Konica", "Minolta", "Konica Minolta", "Kyocera", "Lexmark", "OKI", "Olivetti", "Panasonic", "Philips", "Pitney", "Bowes", "Pitney Bowes", "Ricoh", "Sagem", "Samsung", "Sharp", "Toshiba", "Xerox", "HP");
		if(in_array(ucfirst($termArray),$make)){
			
			for($i=0;$i<$termArray;$i++)
			{
				$q.="name LIKE '%".$termArray[$i]."%' or";
			}
			
		}else{
			
		for($i=0;$i<$termArray;$i++)
			{
				$q.="name LIKE '%".$termArray[$i]."%' AND";
			}
	     }*/
	      $sql_check_link_id="SELECT value_id FROM am_finder_value where name LIKE '%".$termfinal1."%' AND name LIKE '%".$termfinal2."%' AND name LIKE '%".$termfinal3."%' "; 
		
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
