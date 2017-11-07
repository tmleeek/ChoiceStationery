<?php
class Rock_CoreOverride_Block_Adminhtml_Review_Rating_Detailed extends Mage_Adminhtml_Block_Review_Rating_Detailed
{
	 public function getRating()
    {
        if( !$this->getRatingCollection() ) {
            if( Mage::registry('review_data') ) {
                $stores = Mage::registry('review_data')->getStores();

                $stores = array_diff($stores, array(0));

                $ratingCollection = Mage::getModel('rating/rating')
                    ->getResourceCollection()
                    ->addEntityFilter('product')
                    ->setStoreFilter($stores)
                    ->setPositionOrder()
                    ->load()
                    ->addOptionToItems();

                $this->_voteCollection = Mage::getModel('rating/rating_option_vote')
                    ->getResourceCollection()
                    ->setReviewFilter($this->getReviewId())
                    ->addOptionInfo()
                    ->load()
                    ->addRatingOptions();

            } elseif (!$this->getIsIndependentMode()) {
                $ratingCollection = Mage::getModel('rating/rating')
                    ->getResourceCollection()
                    ->addEntityFilter('product')
                    //->setStoreFilter(Mage::app()->getDefaultStoreView()->getId())
                    ->setStoreFilter(null)
                    ->setPositionOrder()
                    ->load()
                    ->addOptionToItems();
            } else {
                $ratingCollection = Mage::getModel('rating/rating')
                    ->getResourceCollection()
                    ->addEntityFilter('product')
                    ->setStoreFilter(
                        $this->getRequest()->getParam('select_stores')
                            ? $this->getRequest()->getParam('select_stores')
                            : $this->getRequest()->getParam('stores')
                    )
                    ->setPositionOrder()
                    ->load()
                    ->addOptionToItems();
                if(intval($this->getRequest()->getParam('id'))){
                    $this->_voteCollection = Mage::getModel('rating/rating_option_vote')
                        ->getResourceCollection()
                        ->setReviewFilter(intval($this->getRequest()->getParam('id')))
                        ->addOptionInfo()
                        ->load()
                        ->addRatingOptions();
                }
            }
            $this->setRatingCollection( ( $ratingCollection->getSize() ) ? $ratingCollection : false );
        }
        return $this->getRatingCollection();
    }
}
			