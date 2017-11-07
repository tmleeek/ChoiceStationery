<?php
/**
 * Class provides data for Magento BO
 *  @author Sergey Stepanchuk <info@bintime.com>
 *
 */
class Bintime_Sinchimport_Model_System_Config_ProdRewrite
{
    public function toOptionArray()
    {    
    	$paramsArray = array(
            'MERGE' => 'Merge',
    	    'REWRITE' => 'Overwrite',
    	);
        return $paramsArray;
    }
}
