<?php
/**
 * Class provides data for Magento BO
 *  @author malex <malex@bintime.com>
 *
 */
class Bintime_Sinchimport_Model_System_Config_ServerList
{
	public function toOptionArray()
	{    
		$paramsArray = array(
			'ftp.stockinthechannel.com'   => 'UK(ftp.stockinthechannel.com)',
			'ftpus.stockinthechannel.com' => 'USA(ftpus.stockinthechannel.com)',
		);

		return $paramsArray;
	}
}
?>
