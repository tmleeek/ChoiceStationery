<?php
set_time_limit(0);
$mageFilename = 'app/Mage.php';
require_once $mageFilename;
Mage::setIsDeveloperMode(true);
ini_set('display_errors', 1);
umask(0);
Mage::app('admin');
Mage::register('isSecureArea', 1);
echo "Script start"; echo '<br/>'; 
mysql_query('SET foreign_key_checks = 0');
//die();
    $fileLocation = "var/import/import_review.csv"; // Set your CSV path here
    $fp = fopen($fileLocation, 'r');
    $count = 1;

    while($data = fgetcsv($fp)){
        if($count > 1){
            //initiate required variables
            $_createdAt     = $data[0];
            $_sku           = $data[1];
            $_catalog       = Mage::getModel('catalog/product');
            $_productId     = $_catalog->getIdBySku($_sku);
            $_statusId      = $data[2];
            $_title         = $data[3];
            $_detail        = $data[4];
           // $_customerId    = $data[6];
            $_nickname      = $data[5];
            $_storeId      = $data[8]; 
            $_entity_pk_value      = $_productId; 
            $_rating      = $data[11]; 

            //load magento review model and assign values
            $review = Mage::getModel('review/review');
            $review->setCreatedAt($_createdAt); //created date and time
            $review->setEntityPkValue($_entity_pk_value);//product id
            $review->setStatusId($_statusId); // status id
            $review->setTitle($_title); // review title
            $review->setDetail($_detail); // review detail
            $review->setEntityId(1); // leave it 1
            $review->setStoreId($_storeId); // store id
           // $review->setCustomerId($_customerId); //null is for administrator
            $review->setNickname($_nickname); //customer nickname
            
            $review->setRatingSummary($_rating);
            
            $review->setReviewId($review->getId());//set current review id
            $review->setStores(array($_storeId));//store id's
            $review->setSkipCreatedAtSet(true);
            $review->save();
            $review->aggregate();

            //set review ratings
            if($data[7]){
                $arr_data = explode("@",$data[6]);
                if(!empty($arr_data)) {
                    foreach($arr_data as $each_data) {
                        $arr_rating = explode(":",$each_data);
                        //print_r($arr_rating[0]);
                        if($arr_rating[1] != 0) {
                            Mage::getModel('rating/rating')
                            ->setRatingId($arr_rating[0])
                            ->setReviewId($review->getId())
                            ->setCustomerId($_customerId)
                            ->addOptionVote($arr_rating[1], $_productId);
                        }
                    }
                }
                $review->aggregate();
            }
        }
       // if($count == 5){
       //       die("total $count reviews are imported!");
       //  }
//exit();
        $count++;
    }
//mysql_query('SET foreign_key_checks = 1');
    echo "total $count reviews are imported!";
?>
