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



class Mirasvit_Fpc_Helper_Response extends Mage_Core_Helper_Abstract
{
    /**
     * @param string $content
     * @return void
     */
    public function cleanExtraMarkup(&$content, $isSid = true)
    {
        $content = preg_replace('/<\[!--\{(.*?)\}--\]>/', '', $content);
        $content = preg_replace('/<\[!--\/\{(.*?)\}--\]>/', '', $content);
        if ($isSid) {
            $sid = array('___SID=U&amp;','___SID=U&','?___SID=U');
            $content = str_replace($sid, '', $content);
        }
    }

    /**
     * @param string $content
     * @return void
     */
    public function updateFormKey(&$content)
    {
        if ($formKey = Mage::getSingleton('core/session')->getFormKey()) {
            $content = preg_replace(
                '/<input type="hidden" name="form_key" value="(.*?)" \\/>/i',
                '<input type="hidden" name="form_key" value="' . $formKey . '" />',
                $content
            );
            $content = preg_replace(
                '/name="form_key" type="hidden" value="(.*?)" \\/>/i',
                'name="form_key" type="hidden" value="' . $formKey . '" />',
                $content
            );

            $content = preg_replace(
                '/\\/form_key\\/(.*?)\\//i',
                '/form_key/' . $formKey . '/',
                $content
            );

            $content = preg_replace(
                '/\\/form_key' . '\\\\' . '\\/(.*?)' . '\\\\' . '\\//i',
                '/form_key\/' . $formKey . '\/',
                $content
            );
        }
    }
}