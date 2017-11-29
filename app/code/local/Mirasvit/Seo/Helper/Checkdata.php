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
 * @package   mirasvit/extension_seo
 * @version   1.3.18
 * @copyright Copyright (C) 2017 Mirasvit (https://mirasvit.com/)
 */


class Mirasvit_Seo_Helper_Checkdata extends Mage_Core_Helper_Abstract
{
    public function prepareSaveActionData($data) {
        if (isset($data['store_ids'])
            && count($data['store_ids']) > 1
            && in_array(0, $data['store_ids'])) {
            $data['store_ids'] = array(0);
        }

        return $data;
    }

    public function isMassEnableActionDataPrepare($data) {
        if (is_array($data)
            && count($data) > 1
            && in_array(0, $data)) {
                return true;
        }

        return false;
    }
}
