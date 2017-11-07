<?php
/**
 * Mirasvit
 *
 * This source file is subject to the Mirasvit Software License, which is available at http://mirasvit.com/license/.
 * Do not edit or add to this file if you wish to upgrade the to newer versions in the future.
 * If you wish to customize this module for your needs.
 * Please refer to http://www.magentocommerce.com for more information.
 *
 * @category  Mirasvit
 * @package   Full Page Cache
 * @version   1.0.5.3
 * @build     520
 * @copyright Copyright (C) 2016 Mirasvit (http://mirasvit.com/)
 */



class Mirasvit_FpcCrawler_Model_Observer
{
    public function onFpcImportFilelog($observer)
    {
        $line = $observer->getLine();
        Mage::getModel('fpccrawler/crawler_url')->saveUrl($line);
    }

    public function onFpcImportLoggedFilelog($observer)
    {
        $line = $observer->getLine();
        Mage::getModel('fpccrawler/crawlerlogged_url')->saveUrl($line);
    }
}
