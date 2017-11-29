<?php
/**
 * Mirasvit
 *
 * This source file is subject to the Mirasvit Software License, which is available at https://mirasvit.com/license/.
 * Do not edit or add to this file if you wish to upgrade the to newer versions in the future.
 * If you wish to customize this module for your needs.
 * Please refer to http://www.magentocommerce.com for more information.
 *
 * @category  Mirasvit
 * @package   mirasvit/extension_seositemap
 * @version   1.0.10
 * @copyright Copyright (C) 2017 Mirasvit (https://mirasvit.com/)
 */


$filepath = dirname(_FILE_);
require_once($filepath . '/../app/Mage.php'); //Path to Magento
umask(0);
Mage::app();

class Mirasvit_Shell_Sitemap extends Mirasvit_SeoSitemap_Model_Sitemap
{
    public function run()
    {
        $collection = Mage::getModel('sitemap/sitemap')->getCollection();
        foreach ($collection as $sitemap) {
            try {
                $sitemap->generateXml();
            }
            catch (Exception $e) {
                // $errors[] = $e->getMessage();
            }
        }
    }
}

$shell = new Mirasvit_Shell_Sitemap();
$shell->run();