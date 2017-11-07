<?php

class Potato_Compressor_Model_Compressor_Js extends Potato_Compressor_Model_Compressor_Abstract
{
    const FILE_EXTENSION         = 'js';

    public function compression($content)
    {
        $jsCompressor = new Compressor_Minify_JSMin('');
        $content = $jsCompressor->minify($content);
        return $content;
    }

    public function makeDefer($response)
    {
        $body = $response->getBody();
        //remove cdata
        $body = preg_replace('#//<!\[CDATA\[(.+?)\//]\]>#s', '$1', $body);
        if (Potato_Compressor_Helper_Config::getDeferMethod() == Potato_Compressor_Model_Source_Defer::ATTRIBUTE_VALUE) {
            $body = $this->_addDeferAttribute($body);
        }
        if (Potato_Compressor_Helper_Config::getDeferMethod() == Potato_Compressor_Model_Source_Defer::MOVE_TO_BODY_END_VALUE) {
            $body = $this->_moveScripts($body);
        }
        $response->setBody($body);
        return $this;
    }

    protected function _moveScripts($body)
    {
        //get all script tags
        $matches = $this->_getAllJSTagFromBody($body);
        if (empty($matches)) {
            return $body;
        }
        $resultBody = $body;
        $ifDirectiveData = $this->_getIfDirectiveData($body);

        foreach ($matches as $line) {
            $scriptLine = $line;
            //check ignore
            preg_match('@po_cmp_ignore@', $scriptLine, $match);
            if (!empty($match)) {
                continue;
            }
            //remove script from body
            $resultBody = str_replace($line, '', $resultBody);
            //compress content
            $content = $this->compression($scriptLine);
            //check is if directive needed
            $linePosition = strpos($body, $line);
            foreach ($ifDirectiveData as $ifData) {
                if ($linePosition > $ifData['startPosition']
                    && $linePosition < $ifData['endPosition'])
                {
                    $content = $ifData['startString'] . $content
                        . $ifData['endString']
                    ;
                    break;
                }
            }
            //move script in the end of the body
            $resultBody = str_replace(
                '</body>', $content . '</body>', $resultBody
            );
        }
        return $resultBody;
    }

    protected function _addDeferAttribute($body)
    {
        //get all script tags
        $matches = $this->_getAllJSTagFromBody($body);

        //inline scripts
        if (empty($matches)) {
            return $body;
        }
        foreach ($matches as $line) {
            $scriptLine = $line;
            //check ignore
            preg_match('@po_cmp_ignore@', $scriptLine, $match);
            if (!empty($match)) {
                continue;
            }

            //check src attr
            preg_match('/<script.*src=.*>/', $scriptLine, $match);
            if (!empty($match)) {
                $scriptLine = str_replace('<script ','<script defer ', $scriptLine);
                $body = str_replace($line, $scriptLine, $body);
                continue;
            }
            preg_match('/<script[^>]*>(.*)<\/script>/is', $scriptLine, $match);
            if (empty($match)) {
                continue;
            }
            $content = $match[1];
            //compress content
            $content = $this->compression($content);
            //prepare inline js file
            $fileName = md5($content) . '.js';
            $filePath = Mage::helper('po_compressor')->getRootCachePath() . DS . Mage::app()->getStore()->getId() . DS . 'js';
            if (!file_exists($filePath . DS . $fileName)) {
                file_put_contents($filePath . DS . $fileName, $content);
            }
            $baseMediaUrl = Mage::helper('po_compressor')->getRootCacheUrl() . '/' . Mage::app()->getStore()->getId() . '/' . 'js' . '/';

            //put into body
            $body = str_replace(
                $line,
                '<script defer src="' . $baseMediaUrl . $fileName . '"></script>',
                $body
            );
        }
        return $body;
    }

    private function _getAllJSTagFromBody($body)
    {
        preg_match_all('/<script>(.*?)<\/script>/is', $body, $matches);
        preg_match_all('/<script\b[^\/]*\/javascript[^>]*>(.*?)<\/script>/is', $body, $jsMatches);

        $matches[0] = array_merge($matches[0], $jsMatches[0]);
        return $matches[0];
    }

    private function _getIfDirectiveData($body)
    {
        preg_match_all('/<!-{0,2}\[if[^>]*>/', $body, $ifDirectiveMatch, PREG_OFFSET_CAPTURE);
        preg_match_all('/<!\[endif\]-{0,2}>/', $body, $endifDirectiveMatch, PREG_OFFSET_CAPTURE);
        if (empty($ifDirectiveMatch)) {
            return array();
        }
        $data =array();
        foreach ($ifDirectiveMatch[0] as $key => $if) {
            $data[] = array(
                'startString' => $if[0],
                'endString' => $endifDirectiveMatch[0][$key][0],
                'startPosition' => $if[1],
                'endPosition' => $endifDirectiveMatch[0][$key][1]
            );
        }
        return $data;
    }
}