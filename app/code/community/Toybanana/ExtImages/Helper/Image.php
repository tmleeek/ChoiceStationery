<?php
/**
 * Magento
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@magentocommerce.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade Magento to newer
 * versions in the future. If you wish to customize Magento for your
 * needs please refer to http://www.magentocommerce.com for more information.
 *
 * @category    Mage
 * @package     Mage_Catalog
 * @copyright   Copyright (c) 2010 Magento Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

/**
 * Catalog image helper
 *
 * @author      Magento Core Team <core@magentocommerce.com>
 */

class Toybanana_ExtImages_Helper_Image
{
	 
	/**
    * Product name needed for image name
    */
	protected $_objName;
	
	/**
    * Image URL to pass to cURL
    */
	protected $_imageUrl;
	
	/**
    * System path to media directory
    */
	protected $_mediaRoot;
	
	/**
    * Path to image dir from $_mediaRoot
    * i.e. /c/o/
    */
	protected $_imageRoot;
	
	/**
    * Image name
    */
	protected $_imageName;
	
	protected $_curlTimeout;
	
	protected $_curlMaxRedir;
	
	protected $_curlConnectTimeout;
	
	/**
    * Magento URL path to media
    */
	protected $_baseUrl;
	
	/**
    * path to image from $_mediaRoot
    * i.e. /c/o/imagename.jpg
    */
	protected $_imagePath;
	
	
	
	protected function _reset()
	{
		$this->_objName = null;
		$this->_imageUrl = null;
		$this->_mediaRoot = null;
		$this->_imageRoot = null;
		$this->_imageName = null;
		$this->_curlTimeout = null;
		$this->_curlMaxRedir = null;
		$this->_baseUrl = null;
		$this->imagePath = null;
		return $this;
	}
	
	public function initProd($productName, $imageUrl)
	{
		$this->_reset();
		$this->setObjName($productName);
		$this->setImageUrl($imageUrl);
		$this->setMediaRoot(Mage::getBaseDir('media').'/catalog/product');
		$this->setImageName($this->getObjName(), $this->getImageUrl());
		$this->setImageRoot(DS . substr(str_replace(' ','',strtolower($this->getImageName())),0,1) . DS . substr(str_replace(' ','',strtolower($this->getImageName())),1,1) . DS);
		$this->setCurlConnectTimeout((Mage::getStoreConfig('ExtImages/curl/curlconntimeout')) ? Mage::getStoreConfig('ExtImages/curl/curlconntimeout') : '1');
		$this->setCurlTimeout((Mage::getStoreConfig('ExtImages/curl/curltimeout')) ? Mage::getStoreConfig('ExtImages/curl/curltimeout') : '3');
		$this->setCurlMaxRedir((Mage::getStoreConfig('ExtImages/curl/curlmaxredir')) ? Mage::getStoreConfig('ExtImages/curl/curlmaxredir') : '5');
		$this->setBaseUrl(Mage::getStoreConfig('ExtImages/general/baseurl'));
		return $this;
		
	}
	
	public function initCat($categoryName, $imageUrl)
	{
		$this->_reset();
		$this->setObjName($categoryName);
		$this->setImageUrl($imageUrl);
		$this->setMediaRoot(Mage::getBaseDir('media').'/catalog/category/');
		$this->setImageName($this->getObjName(), $this->getImageUrl());
		//$this->setImageRoot(DS . substr(str_replace(' ','',strtolower($this->getImageName())),0,1) . DS . substr(str_replace(' ','',strtolower($this->getImageName())),1,1) . DS);
		$this->setCurlConnectTimeout((Mage::getStoreConfig('ExtImages/curl/curlconntimeout')) ? Mage::getStoreConfig('ExtImages/curl/curlconntimeout') : '1');
		$this->setCurlTimeout((Mage::getStoreConfig('ExtImages/curl/curltimeout')) ? Mage::getStoreConfig('ExtImages/curl/curltimeout') : '3');
		$this->setCurlMaxRedir((Mage::getStoreConfig('ExtImages/curl/curlmaxredir')) ? Mage::getStoreConfig('ExtImages/curl/curlmaxredir') : '5');
		$this->setBaseUrl(Mage::getStoreConfig('ExtImages/general/baseurl'));
		return $this;
		
	}
	
	public function curl_get_image()
	{
		$image = null;
		try
		{
			if (!is_null($this->getImageUrl()) && !is_null($this->getObjName())) :
				if ($this->getBaseUrl()):
					$this->setImageUrl($this->getBaseUrl() . $this->getImageUrl());
				endif;
				# fetch remote image.
				$purl = parse_url($this->getImageUrl());
				if(isset($purl['scheme']) && ($purl['scheme'] == 'http' || $purl['scheme'] == 'https')):
					$finfo = pathinfo($this->getImageUrl());
					# grab the image, and cache it so we have something to work with..
					$local_filepath = $this->getMediaRoot().$this->getImageRoot().$this->getImageName();
					$download_image = true;
					if(file_exists($local_filepath)):
						$download_image = false;
					endif;
					if($download_image == true):
						//Mage::log('Download = true');
						$data = array(
							'IMAGE_URL' => $this->getImageUrl(),
							'CURL_CONNECTTIMEOUT' => $this->getCurlConnectTimeout(),
							'CURL_TIMEOUT' => $this->getCurlTimeout(),
							'CURL_MAXREDIR' => $this->getCurlMaxRedir(),
							'MEDIA_ROOT' => $this->getMediaRoot(),
							'IMAGE_ROOT' => $this->getImageRoot(),
							'LOCAL_FILEPATH' => $local_filepath,
							'LOGGING_ON' => Mage::getStoreConfig('dev/log/active')
						);

						$query = http_build_query($data);
						// You can POST a file by prefixing with an @ (for <input type="file"> fields)
						//$data['file'] = '@/home/user/world.jpg';
						$curlUrl =  Mage::getBaseURL(Mage_Core_Model_Store::URL_TYPE_WEB) . "extimages/curl.php";
						$url = parse_url($curlUrl,PHP_URL_PATH);
						$scheme = parse_url($curlUrl,PHP_URL_SCHEME);
						$foServer = $_SERVER['SERVER_ADDR'];
						$fwServer = Mage::app()->getFrontController()->getRequest()->getHttpHost();
						$port = 80;
						$errno = '';
						$errstr = '';
						$conn_timeout = 1;
						$rw_timeout = 1;
						//$time_start = microtime(true);
						$fp = fsockopen($foServer, $port, $errno, $errstr, $conn_timeout);
						/*$time_end = microtime(true);
						$time = number_format($time_end - $time_start,5);
						Mage::log("Time for fsockopen to complete : $time sec.");*/
						if (!$fp) {
						   Mage::log("$errstr ($errno)");
						   return $image;
						}
						$out = "POST $url HTTP/1.1\r\n";
						$out .= "Host: $fwServer\r\n";
						$out .= "User-Agent: Mozilla/5.0 (Windows; U; Windows NT 5.1; en-US; rv:1.7.5)".
									" Gecko/20041107 Firefox/1.0\r\n";
						$out .= "Content-Type: application/x-www-form-urlencoded\r\n";
						$out .= "Content-Length: ".strlen($query)."\r\n";
						$out .= "Connection: close\r\n\r\n";
						$out .= $query;
						stream_set_blocking($fp, false);
						stream_set_timeout($fp, $rw_timeout);
						//$time_start = microtime(true);
						$fw = fwrite($fp, $out);
						/*$fwOut = "";
						while(!feof($fp)){
							$fwOut .= fgets($fp);
						}
						list($header, $body) = preg_split("/\R\R/", $fwOut, 2);
						Mage::log($header);
						Mage::log($body);*/
						if(!$fw || !(strlen($out) == $fw)) {
							Mage::log("Error writing POST data to $url");
						}
						fclose($fp);
						$fp = false;
						/*$time_end = microtime(true);
						$time = number_format($time_end - $time_start,5);
						Mage::log("Time for POST to complete : $time sec.");
						Mage::log("String was " . strlen($out) . " bytes. $fw bytes were written");*/
						// Curl Too Slow. Using fsockopen method above.
						/*$user_agent = "Mozilla/5.0 (Windows; U; Windows NT 5.1; en-US; rv:1.7.5)".
									" Gecko/20041107 Firefox/1.0";
						$options = array(
							CURLOPT_POST => true,
							CURLOPT_POSTFIELDS => $query,
							CURLOPT_IPRESOLVE => CURL_IPRESOLVE_V4,
							CURLOPT_RETURNTRANSFER => true,
							CURLOPT_FOLLOWLOCATION => true,
							CURLOPT_SSL_VERIFYPEER => false,
							CURLOPT_FRESH_CONNECT => true,
							CURLOPT_NOSIGNAL => true,
							CURLOPT_CONNECTTIMEOUT_MS => 50,
							CURLOPT_TIMEOUT_MS => 50,
							CURLOPT_USERAGENT => $user_agent,
							CURLOPT_HTTPHEADER => array('Connection: close'),
							CURLOPT_PORT => 80
						);
						$ch = curl_init($url);
						curl_setopt_array($ch, $options);
						$time_start = microtime(true);
						$return = curl_exec($ch);
						$time_end = microtime(true);
						$time = $time_end - $time_start;
						$info = curl_getinfo($ch);
						if ($return === false || $info['http_code'] != 200) {
							$response = "cUrl POST to $url failed. ";
							if (curl_error($ch)) {
								$response .= "Error:  ". curl_error($ch);
							}
							$response .= ". cURL took " . number_format($time,5) . " seconds.";
							Mage::log($response);
						}
						curl_close($ch);*/
					endif;
					$this->setImagePath($local_filepath);
					## check to see if file exists. if not, log failure.
					if(file_exists($this->getImagePath()) == false):
						Mage::log('External Image Download Failed for URL: ' . $this->getImageUrl() . ' to location: '. $this->getImagePath());
						return $image;
					endif;
					$image = $this->getImageRoot().$this->getImageName();
				endif;
			endif;
		}
		catch (Exception $e)
		{
			Mage::log($e);
		}
		return $image;
	}
	
	protected function setObjName($objName)
	{
		$this->_objName = $objName;
		return $this;
	}
	
	protected function setImageUrl($imageUrl)
	{
		$this->_imageUrl = $imageUrl;
		return $this;
	}
	
	protected function setMediaRoot($mediaRoot)
	{
		$this->_mediaRoot = $mediaRoot;
		return $this;
	}
	
	protected function setImageRoot($imageRoot)
	{
		$this->_imageRoot = $imageRoot;
		return $this;
	}
	
	protected function setCurlTimeout($curlTimeout)
	{
		$this->_curlTimeout = $curlTimeout;
		return $this;
	}
	
	protected function setCurlConnectTimeout($curlConnectTimeout)
	{
		$this->_curlConnectTimeout = $curlConnectTimeout;
		return $this;
	}
	
	protected function setCurlMaxRedir($curlMaxRedir)
	{
		$this->_curlMaxRedir = $curlMaxRedir;
		return $this;
	}
	
	protected function setBaseUrl($baseUrl)
	{
		$this->_baseUrl = $baseUrl;
		return $this;
	}
	
	protected function setImageName($objName, $imageUrl)
	{
		$cleanName = iconv('UTF-8', 'ASCII//TRANSLIT', utf8_encode($objName)); //converts utf-8 characters to ?s so we can remove them
		$preImageName = strtolower(str_replace(' ','-',$cleanName));
		$replace=""; //what you want to replace the bad characters with
		$pattern="/([[:alnum:]_\.-]*)/"; //basically all the filename-safe characters
		$bad_chars=preg_replace($pattern,$replace,$preImageName); //leaves only the "bad" characters
		$bad_arr=str_split($bad_chars); //split them up into an array for the str_replace() func.
		$goodImageName = substr(str_replace($bad_arr,$replace,$preImageName),0,50);
		$this->_imageName = $goodImageName . '-' . md5($imageUrl) . '.jpg';
		return $this;
	}
	
	protected function setImagePath($imagePath)
	{
		$this->_imagePath = $imagePath;
		return $this;
	}
	
	protected function getObjName()
	{
		return $this->_objName;
	}
	
	protected function getImageUrl()
	{
		return $this->_imageUrl;
	}
	
	protected function getMediaRoot()
	{
		return $this->_mediaRoot;
	}
	
	protected function getImageRoot()
	{
		return $this->_imageRoot;
	}
	
	protected function getCurlTimeout()
	{
		return $this->_curlTimeout;
	}
	
	protected function getCurlConnectTimeout()
	{
		return $this->_curlConnectTimeout;
	}
	
	protected function getCurlMaxRedir()
	{
		return $this->_curlMaxRedir;
	}
	
	protected function getBaseUrl()
	{
		return $this->_baseUrl;
	}
	
	protected function getImageName()
	{
		return $this->_imageName;
	}
	
	protected function getImagePath()
	{
		return $this->_imagePath;
	}

}
