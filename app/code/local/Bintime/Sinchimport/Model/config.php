<?php
  error_reporting(E_ALL);
  ini_set('display_errors','On');

  define('BASE_PATH', 'DIR_FS_ADMIN' . 'StockInTheChannel_import');
  define('LOG_PATH', 'BASE_PATH' . '/logs/');	
  define('INCL_PATH',  'BASE_PATH' . '/includes/');
  define('CLASS_PATH', 'INCL_PATH' . '/class/');	
  	
  define('TMP_PATH',  'BASE_PATH' . '/tmp/');
  define('LOG_FILE',  'LOG_PATH'.'import'.date("_Y_m").'.log');

  define('FILE_CATEGORIES', 'Categories.csv');
  define('FILE_CATEGORIES_TEST', 'Categories_Test.csv');
  define('FILE_CATEGORY_TYPES', 'CategoryTypes.csv');
  define('FILE_CATEGORIES_FEATURES', 'CategoryFeatures.csv');
  define('FILE_DISTRIBUTORS', 'Distributors.csv');
  define('FILE_DISTRIBUTORS_STOCK_AND_PRICES', 'DistributorStockAndPrices.csv');
  define('FILE_EANCODES', 'EANCodes.csv');
  define('FILE_MANUFACTURERS', 'Manufacturers.csv');
  define('FILE_PRODUCT_FEATURES', 'ProductFeatures.csv');
  define('FILE_PRODUCT_CATEGORIES', 'ProductCategories.csv');
  define('FILE_PRODUCTS', 'Products.csv');
  define('FILE_PRODUCTS_TEST', 'Products_Test.csv');
  define('FILE_RELATED_PRODUCTS', 'RelatedProducts.csv');
  define('FILE_RESTRICTED_VALUES', 'RestrictedValues.csv');
  define('FILE_STOCK_AND_PRICES', 'StockAndPrices.csv');
  define('FILE_PRODUCTS_PICTURES_GALLERY', 'ProductPictures.csv');
  define('FILE_PRODUCT_CONTRACTS', 'ProductContracts.csv');
  define('FILE_PRICE_RULES', 'contractprices.csv');
  define('FILE_URL_AND_DIR', "ftp://%%%login%%%:%%%password%%%@%%%server%%%/");
  define('DEFAULT_FILE_TERMINATED_CHAR', "|");

  define('LANG_CODE', 'en');	
  define('REWRITE_CATEGORIES_ORDER_ID', 'FALSE');
  define('PRICE_BREAKS', "
  0-25;
  25-50;
  50-100;
  100-200;
  200-500;
  500-1000;
  1000-2000;
  2000-5000;
  5000-*;
  ");
  define("UPDATE_CATEGORY_DATA", false);	
  define('PHP_RUN_STRINGS', 'php5;php');
  if( exec("which php") ){
      define('PHP_RUN_STRING', 'php ');
  }elseif( exec("which php5") ){
      define('PHP_RUN_STRING', 'php5 ');
  }
