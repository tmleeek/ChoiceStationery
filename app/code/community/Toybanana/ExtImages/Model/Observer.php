<?php
/*
 * Created on Sep 1, 2013
 *
 * To change the template for this generated file go to
 * Window - Preferences - PHPeclipse - PHP - Code Templates
 */
 
 
 class Toybanana_ExtImages_Model_Observer{

 
	protected $_extEnabled;
	
	protected function _reset()
	{
		$this->_extEnabled = false;
		return $this;
	}
	
    public function updateProductImageUrls(Varien_Event_Observer $observer){
    	if (Mage::getStoreConfig('ExtImages/general/enabled')) :
    		$_helper = Mage::helper('ExtImages/Image');
	        $product = $observer->getData('product');
			$this->_reset();
			$this->setExtEnabled($product->getData('use_external_images'));
	        if ($product->getData('use_external_images')) {
				$imageUrl = $product->getData('image_external_url');
				$smallUrl = $product->getData('small_image_external_url');
				$thumbnailUrl = $product->getData('thumbnail_external_url');
				$product->setImage( $_helper->initProd($product->getName(),$imageUrl)->curl_get_image());
				switch (true) {
					case ($smallUrl == $imageUrl):
						//Mage::log("Url was same as image url, not downloading Small Image.");
						$product->setSmallImage($product->getImage());
						break;
					default:
						$product->setSmallImage($_helper->initProd($product->getName(),$smallUrl)->curl_get_image());
				}
				switch (true) {
					case ($thumbnailUrl == $imageUrl):
						//Mage::log("Url was same as image url, not downloading Thumbnail.");
						$product->setThumbnail($product->getImage());
						break;
					case ($thumbnailUrl == $smallUrl):
						//Mage::log("Url was same as small url, not downloading Thumbnail.");
						$product->setThumbnail($product->getSmallImage());
						break;
					default:
						$product->setThumbnail($_helper->initProd($product->getName(),$thumbnailUrl)->curl_get_image());
				}
				$product->unsMediaGallery('images');
				$product->setMediaGallery ( array ('images' => array (), 'values' => array () ) );
				if (!Mage::getStoreConfig('ExtImages/general/usegallery')) :
					return;
				endif;
				if ($product->getData('external_gallery')) {
					$images = array();
					$position = 0;
					$valueid = 0;
					$arrImages = explode(',',$product->getData('external_gallery'));
					$ext_gallery = array_unique($arrImages);
					if(!$product->getImage() == "") {
						$ext_image['file'] = $product->getImage();
						$ext_image['disabled'] = 0;
						$ext_image['position'] = $position;
						$ext_image['value_id'] = ++$valueid;
						$images[$position] = $ext_image;
						$position ++;
					}
					foreach ($ext_gallery as $image) {
						$remoteImage = $_helper->initProd($product->getName(),$image)->curl_get_image();
						if ($remoteImage) :
							$ext_image['file'] = $remoteImage;
							$ext_image['disabled'] = 0;
							$ext_image['position'] = $position;
							$ext_image['value_id'] = ++$valueid;
							$images[$position] = $ext_image;
							$position ++;
						endif;
					}
					$product->setData('media_gallery', array('images' => $images, 'values' => array()));
				}
		    }
        endif;
        return;
    }
    
    public function updateProductImageUrlsInCollection($observer){
    	if (Mage::getStoreConfig('ExtImages/general/enabled')) :
    		$_helper = Mage::helper('ExtImages/Image');
	        foreach ($observer->getCollection() as $product) {
	        	if ($product->getData('use_external_images')) {
					$smallUrl = $product->getData('small_image_external_url');
					$thumbnailUrl = $product->getData('thumbnail_external_url');
					$product->setSmallImage($_helper->initProd($product->getName(),$smallUrl)->curl_get_image());
					switch (true) {
						case ($thumbnailUrl == $smallUrl):
							//Mage::log("Url was same as small url, not downloading Thumbnail.");
							$product->setThumbnail($product->getSmallImage());
							break;
						default:
							$product->setThumbnail($_helper->initProd($product->getName(),$thumbnailUrl)->curl_get_image());
					}
	        	}
	        }
	    endif;
        return;
    }
    
    public function updateEavAttributes($observer){
    	if (Mage::getStoreConfig('ExtImages/general/enabled')) :
			$observer->getCollection()->addAttributeToSelect('use_external_images');
			$observer->getCollection()->addAttributeToSelect('image_external_url');
			$observer->getCollection()->addAttributeToSelect('small_image_external_url');
			$observer->getCollection()->addAttributeToSelect('thumbnail_external_url');
			$observer->getCollection()->addAttributeToSelect('external_gallery');
		endif;
		return;
    }
    
    public function updateGalleryImages($observer){
    	if (Mage::getStoreConfig('ExtImages/general/enabled')) :
	    	$product = $observer->getData('product');
	    	if ($product->getData('use_external_images') || $this->getExtEnabled()) :
				$product->unsMediaGallery('images');
			endif;
		endif;
		return;
    	
    }
	
	public function updateCategoryImageUrls($observer){
		if (Mage::getStoreConfig('ExtImages/general/enabled')) :
			$this->_reset();
			$_helper = Mage::helper('ExtImages/Image');
			$_category = $observer->getData('category');
			if ($_category->getData('cat_use_external_images')) :
				if($_category->getData('cat_external_image')) {
					$_category->setImage($_helper->initCat($_category->getName(),$_category->getData('cat_external_image'))->curl_get_image());
				}
				if($_category->getData('cat_external_thumbnail')) {
					$_category->setThumbnail($_helper->initCat($_category->getName(),$_category->getData('cat_external_thumbnail'))->curl_get_image());
				}
			endif;
		endif;
		return;
	}
	
	protected function setExtEnabled($enabled)
	{
		$this->_extEnabled = $enabled;
		return $this;
	}
	
	protected function getExtEnabled()
	{
		return $this->_extEnabled;
	}
}
?>
