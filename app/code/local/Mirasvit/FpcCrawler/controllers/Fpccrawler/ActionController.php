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



/**
 * Контроллер для запуска кравлера (что бы все действия производились под apache пользьзователем).
 *
 * @category Mirasvit
 */
class Mirasvit_FpcCrawler_Fpccrawler_ActionController extends Mage_Core_Controller_Front_Action
{
    public function runAction()
    {
        Mage::getSingleton('fpccrawler/crawler_crawl')->doRun();
    }
}
