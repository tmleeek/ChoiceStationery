<?php 

require_once 'abstract.php';

class Resave_Products extends Mage_Shell_Abstract
{
    public function run()
    {
      

       
            $product = Mage::getModel('catalog/product')->load(12262);
            $product->save();
            echo $product->getSku(); echo '--';
            Mage::dispatchEvent('catalog_product_prepare_save', array('product' => $product));
           
    }
}

$shell = new Resave_Products();
$shell->run();
echo "exit"; 
