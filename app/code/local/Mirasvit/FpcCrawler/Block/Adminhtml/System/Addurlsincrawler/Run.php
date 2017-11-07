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


class Mirasvit_FpcCrawler_Block_Adminhtml_System_Addurlsincrawler_Run extends Mage_Adminhtml_Block_Abstract
{
    public function getWorker()
    {
        return Mage::getSingleton('fpccrawler/system_addurlsincrawler_worker');
    }

}
