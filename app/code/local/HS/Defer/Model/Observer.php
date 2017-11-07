<?php

class HS_Defer_Model_Observer
{
    /**
     * @var HS_Defer_Helper_Data
     */
    protected $_helper = null;

    /**
     * Retrieve Helper instance.
     *
     * @return HS_Defer_Helper_Data
     */
    protected function _getHelper()
    {
        if( ! $this->_helper) {
            $this->_helper = Mage::helper('defer');
        }

        return $this->_helper;
    }

    /**
     * Match pattern provided with subject.
     *
     * @param string $pattern
     * @param string $string
     * @param string $type
     *
     * @return string
     */
    public function matchPattern(
        $pattern, &$string, $checkHtmlComment = false)
    {
        $count = 1;
        preg_match_all($pattern, $string, $matches);
        $matches = $matches[0];
        foreach ($matches as $key => $match) {
            if(false !== strpos($match, 'nodefer')
                || ($checkHtmlComment && 0 === strpos(trim($match), '<!--'))
            ) {
                unset($matches[$key]);
                continue;
            }

            $string = str_replace($match, '', $string, $count);
        }
        return implode('', $matches);
    }

    /**
     * Defer javascript before rendering html.
     *
     * @param Varien_Event_Observer $observer
     *
     * @return HS_Defer_Model_Observer
     */
    public function defer(Varien_Event_Observer $observer)
    {
        $response = $observer->getResponse();
        if( ! $response) {
            return $this;
        }

        $html = $response->getBody();
        if($this->_getHelper()->canDefer(HS_Defer_Helper_Data::DEFER_TYPE_JS)) {
            $conditionalJsPattern = '/<\!--\[if[^\>]*>\s*<script.*<\/script>\s*<\!\[endif\]-->/siU';
            $conditionalJs = $this->matchPattern($conditionalJsPattern, $html);

            $jsPattern = '/(<\!--)?\s*<script.*<\/script>/siU';
            $js = $this->matchPattern($jsPattern, $html, true);

            $html = str_replace('</body>', $conditionalJs . $js . '</body>', $html);
        }

        $response->setBody($html);
        return $this;
    }
}
