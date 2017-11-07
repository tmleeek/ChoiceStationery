<?php

class MDN_Quotation_Helper_Proposal extends Mage_Core_Helper_Abstract {

    //constants
    const kProposalNode = 'proposal';
    const kSectionNode = 'section';
    const kTitleNode = 'title';
    const kContentNode = 'content';
    const kModeNode = 'mode';
    const kModeList = "list";
    const kModeText = "text";

    private $_cache = '';

    /**
     * Get business proposal from quote
     * @param <type> $quote_id
     * @return <type>
     */
    public function getCache($quote_id) {

        if ($this->_cache == '') {
            $quote = Mage::getModel('Quotation/Quotation')->load($quote_id);
            $this->_cache = $quote->getbusiness_proposal();
        }

        return $this->_cache;
    }

    /**
     * Initialisation
     */
    public function initCache() {
        $this->_cache = '';
    }

    /**
     * Save proposal to quote
     */
    public function save($proposal, $quote) {

        $key_title = 'title_section_';
        $key_content = 'content_section_';
        $key_mode = 'mode_section_';
        $j = 0;

        $xml = new DomDocument();
        $root = $xml->createElement(self::kProposalNode, '');

        while (array_key_exists($key_title . $j, $proposal)) {

            if ($proposal[$key_title . $j] != '' && $proposal[$key_content . $j] != '') {

                $section = $xml->createElement(self::kSectionNode, '');

                $mode = $proposal[$key_mode . $j];
                $title = $proposal[$key_title . $j];
                $content = $proposal[$key_content . $j];

                $title = $xml->createElement(self::kTitleNode, '');
                $title->appendChild($xml->createTextNode(strip_tags($proposal[$key_title . $j])));
                $section->appendChild($title);

                $content = $xml->createElement(self::kContentNode, '');
                $content->appendChild($xml->createTextNode(strip_tags($proposal[$key_content . $j])));
                $section->appendChild($content);

                $mode = $xml->createElement(self::kModeNode, '');
                $mode->appendChild($xml->createTextNode($proposal[$key_mode . $j]));
                $section->appendChild($mode);

                $root->appendChild($section);
            }

            $j++;
        }

        $xml->appendChild($root);

        $quote->setbusiness_proposal($xml->saveXML());

        return $quote;
    }

    /**
     *
     * @param <type> $quoteId 
     */
    protected function loadXml($quoteId)
    {
        try
        {
            $xmlDoc = new DomDocument();
            $xml = $this->getCache($quoteId);
            $xmlDoc->loadXML($xml);

            return $xmlDoc;
        }
        catch(Exception $ex)
        {
            return null;
        }
    }

    /**
     * return business proposal form
     */
    public function getBusinessProposalForm($quote_id) {

        $i = 0;
        $retour = '';
        $retour .= '<ul>';

        $xml = $this->loadXml($quote_id);
        if ($xml == null)
            return '';

        $root = $xml->getElementsByTagName(self::kProposalNode)->item(0);

        if ($root->nodeType == XML_ELEMENT_NODE) {

            foreach ($root->getElementsByTagName(self::kSectionNode) as $sectionNode) {

                $titleNode = $sectionNode->getElementsByTagName(self::kTitleNode)->item(0);
                $contentNode = $sectionNode->getElementsByTagName(self::kContentNode)->item(0);
                $modeNode = $sectionNode->getElementsByTagName(self::kModeNode)->item(0);

                $isCheckedText = ($modeNode->nodeValue == 'text') ? 'checked' : '';
                $isCheckedList = ($modeNode->nodeValue == 'list') ? 'checked' : '';

                $retour .= '<li><input type="text" id="title_section_' . $i . '" name="myform[proposal][title_section_' . $i . ']" value="' . $titleNode->nodeValue . '"/></li>';
                $retour .= '<li><textarea id="content_section_' . $i . '" name="myform[proposal][content_section_' . $i . ']">' . $contentNode->nodeValue . '</textarea></li>';
                $retour .= '<li><input type="radio" ' . $isCheckedText . ' value="text" id="mode_section_' . $i . '_text" name="myform[proposal][mode_section_' . $i . ']"/><label for="mode_section_' . $i . '_text">Text</label>';
                $retour .= '<input type="radio" ' . $isCheckedList . ' value="list" id="mode_section_' . $i . '_list" name="myform[proposal][mode_section_' . $i . ']"/><label for="mode_section_' . $i . '_list">List</label></li>';

                $i++;
            }
        }

        $retour .= '<li><input type="hidden" id="indice_section" value="' . $i . '"/></li>';
        $retour .= '</ul>';

        return $retour;
    }

    /**
     * Return sections as array
     */
    public function asArray($quote_id) {

        $retour = array();

        $xml = $this->loadXml($quote_id);
        if ($xml == null)
            return json_encode(array());

        $root = $xml->getElementsByTagName(self::kProposalNode)->item(0);

        if ($root->nodeType == XML_ELEMENT_NODE) {
            foreach ($root->getElementsBytagName(self::kSectionNode) as $section) {

                $retour[] = array(
                    'title' => $section->getElementsByTagName(self::kTitleNode)->item(0)->nodeValue,
                    'content' => $section->getElementsByTagName(self::kContentNode)->item(0)->nodeValue,
                    'mode' => $section->getElementsByTagName(self::kModeNode)->item(0)->nodeValue
                );
            }
        }


        return json_encode($retour);
    }

    /**
     * Rturn as html
     */
    public function asHtml($quote_id) {

        $html = '';

        $xml = $this->loadXml($quote_id);
        if ($xml == null)
            return '';

        $root = $xml->getElementsByTagName(self::kProposalNode)->item(0);

        if ($root->nodeType == XML_ELEMENT_NODE) {

            foreach ($root->getElementsByTagName(self::kSectionNode) as $section) {

                $html .= '<h3>' . $section->getElementsByTagName(self::kTitleNode)->item(0)->nodeValue . '</h3>';

                $content = $section->getElementsByTagName(self::kContentNode)->item(0)->nodeValue;

                switch ($section->getElementsByTagName(self::kModeNode)->item(0)->nodeValue) {

                    case self::kModeList:
                        $tmp = explode("\n", $content);
                        $html .= '<ul>';
                        foreach ($tmp as $k => $v) {
                            $html .= '<li>' . $v . '</li>';
                        }
                        $html .= '</ul>';
                        break;

                    case self::kModeText:
                        $html .= '<p>' . str_replace("\n", '<br/>', $content) . '</p>';
                        break;
                }
            }
        }

        return $html;
    }

}
