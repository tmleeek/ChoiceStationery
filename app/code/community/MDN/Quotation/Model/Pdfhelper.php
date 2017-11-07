<?php

abstract class MDN_Quotation_Model_Pdfhelper extends Mage_Sales_Model_Order_Pdf_Abstract {

    protected $_BLOC_ENTETE_HAUTEUR = 50;
    protected $_BLOC_ENTETE_LARGEUR = 585;
    protected $_BLOC_FOOTER_HAUTEUR = 40;
    protected $_BLOC_FOOTER_LARGEUR = 585;
    protected $_LOGO_HAUTEUR = 50;
    protected $_LOGO_LARGEUR = 585;
    protected $_PAGE_HEIGHT = 820;
    protected $_PAGE_WIDTH = 700;
    protected $_ITEM_HEIGHT = 25;
    public $pdf;
    protected $firstPageIndex = 0;

    /**
     * Draw logo
     *
     * @param unknown_type $page
     */
    protected function insertLogo(&$page, $StoreId = null) {
        $image = Mage::getStoreConfig('quotation/pdf/logo', $StoreId);
        if ($image) {
            $image = Mage::getBaseDir('media') . DS . 'upload' . DS . 'image' . DS . $image;
            if (is_file($image)) {
                try {
                    $image = Zend_Pdf_Image::imageWithPath($image);
                } catch (Exception $ex) {
                    throw new Exception('Logo file for PDF is not supported, please use jpeg ou png file');
                }
                $page->drawImage($image, 10, 780, $this->_LOGO_LARGEUR, 780 + $this->_LOGO_HAUTEUR);
            }
        }

        return $this;
    }

    /**
     * Calculate multiline text height
     *
     */
    public function getMultilineTextHeight($page, $Text, $Size, $LineHeight) {
        $retour = -$LineHeight;
        $page->setFont(Zend_Pdf_Font::fontWithName(Zend_Pdf_Font::FONT_HELVETICA), $Size);
        foreach (explode("\n", $Text) as $value) {
            if ($value !== '') {
                $retour += $LineHeight;
            }
        }
        return $retour;
    }

    /**
     * Draw multiline text and return total height
     */
    protected function DrawMultilineText($page, $Text, $x, $y, $Size, $GrayScale, $LineHeight) {
        $retour = -$LineHeight;
        $page->setFillColor(new Zend_Pdf_Color_GrayScale($GrayScale));
        $page->setFont(Zend_Pdf_Font::fontWithName(Zend_Pdf_Font::FONT_HELVETICA), $Size);
        foreach (explode("\n", $Text) as $value) {
            if ($value !== '') {
                $page->drawText(trim(strip_tags($value)), $x, $y, 'UTF-8');
                $y -=$LineHeight;
                $retour += $LineHeight;

                if (($y < $this->_BLOC_FOOTER_HAUTEUR)) {
                    $savedFont = $page->getFont();
                    $savedFontSize = $page->getFontSize();
                    $this->drawFooter($page, $this->_settings['store_id']);
                    $page = $this->NewPage($this->_settings);
                    $this->drawTableHeader($page);
                    $y = $this->y;
                    $retour = 0;

                    //re apply font (because new page can change font settings
                    $page->setFont($savedFont, $savedFontSize);
                }
            }
        }
        return $retour;
    }

    /**
     * Return text width (considering size & font)
     */
    public function widthForStringUsingFontSize($string, $font, $fontSize) {
        try {
            $drawingString = iconv('UTF-8', 'UTF-16BE//IGNORE', $string);
            $characters = array();
            for ($i = 0; $i < strlen($drawingString); $i++) {
                $characters[] = (ord($drawingString[$i++]) << 8 ) | ord($drawingString[$i]);
            }
            $glyphs = $font->glyphNumbersForCharacters($characters);
            $widths = $font->widthsForGlyphs($glyphs);
            $stringWidth = (array_sum($widths) / $font->getUnitsPerEm()) * $fontSize;
            return $stringWidth;
        } catch (Exception $ex) {
            throw new Exception('Unable to calculate widyj for string ' . $string);
        }
    }

    /**
     * Draw text in a specific box
     *
     */
    public function drawTextInBlock($page, $text, $x, $y, $width, $height, $alignment = 'c', $encoding = 'UTF-8') {
        $text_width = $this->widthForStringUsingFontSize($text, $page->getFont(), $page->getFontSize());
        switch ($alignment) {
            case 'c': //center text
                $x = $x + ($width / 2) - $text_width / 2;
                break;
            case 'r': //right align
                $x = $x + $width - $text_width;
        }

        $page->drawText(trim(strip_tags($text)), $x, $y, $encoding);
    }

    /**
     * Draw footer
     *
     * @param unknown_type $page
     */
    public function drawFooter($page, $StoreId = null) {
        //Background
        $page->setFont(Zend_Pdf_Font::fontWithName(Zend_Pdf_Font::FONT_HELVETICA), 10);
        $page->setFillColor(new Zend_Pdf_Color_GrayScale(0.7));
        $page->drawRectangle(10, $this->_BLOC_FOOTER_HAUTEUR + 15, $this->_BLOC_FOOTER_LARGEUR, 15, Zend_Pdf_Page::SHAPE_DRAW_FILL);

        //text
        $page->setFillColor(new Zend_Pdf_Color_GrayScale(0.2));
        $this->DrawFooterMultilineText($page, Mage::getStoreConfig('quotation/pdf/pdf_footer', $StoreId), 20, $this->_BLOC_FOOTER_HAUTEUR, 10, 0, 15);
    }

    /**
     * Draw footer text
     */
    public function DrawFooterMultilineText($page, $Text, $x, $y, $Size, $GrayScale, $LineHeight) {

        $retour = -$LineHeight;
        $page->setFillColor(new Zend_Pdf_Color_GrayScale($GrayScale));
        $page->setFont(Zend_Pdf_Font::fontWithName(Zend_Pdf_Font::FONT_HELVETICA), $Size);
        foreach (explode("\n", $Text) as $value) {
            if ($value !== '') {
                $page->drawText(trim(strip_tags($value)), $x, $y, 'UTF-8');
                $y -=$LineHeight;
                $retour += $LineHeight;
            }
        }
        return $retour;
    }

    /**
     * Draw header
     */
    public function drawHeader($page, $title, $StoreId = null) {

        //background
        $page->setFillColor(new Zend_Pdf_Color_GrayScale(0.7));
        $page->setFillColor(Zend_Pdf_Color_Html::color('#FFFFFF'));
        $page->drawRectangle(10, $this->y, $this->_BLOC_ENTETE_LARGEUR, $this->y - $this->_BLOC_ENTETE_HAUTEUR, Zend_Pdf_Page::SHAPE_DRAW_FILL);

        // insert le logo
        $this->insertLogo($page, $StoreId);

        $this->y -= $this->_BLOC_ENTETE_HAUTEUR + 5;
        $page->setLineWidth(1.5);
        $page->setLineColor(new Zend_Pdf_Color_GrayScale(0.1));
        $page->drawLine(10, $this->y, $this->_BLOC_ENTETE_LARGEUR, $this->y);

        //Title
        $this->y -= 25;
        $name = $title;
        $page->setFillColor(new Zend_Pdf_Color_GrayScale(0.3));
        if ($title != '') {
            $page->setFont(Zend_Pdf_Font::fontWithName(Zend_Pdf_Font::FONT_HELVETICA_BOLD), 24);
            $this->drawTextInBlock($page, $name, 0, $this->y, $this->_PAGE_WIDTH - 80, 50, 'c');
            $this->y -= 10;
            $page->drawLine(10, $this->y, $this->_BLOC_ENTETE_LARGEUR, $this->y);
        }
    }

    /**
     * Add new page (draw header / footer)
     *
     */
    public function NewPage(array $settings = array()) {
        $page = $this->pdf->newPage(Zend_Pdf_Page::SIZE_A4);
        $this->pdf->pages[] = $page;

        //on place Y tout en haut
        $this->y = 830;

        //dessine l'entete
        $this->drawHeader($page, $settings['title'], $settings['store_id']);

        //retourne la page
        return $page;
    }

    /**
     * Truncate text to fit width
     * 
     */
    public function TruncateTextToWidth($page, $text, $width) {
        $current_width = $this->widthForStringUsingFontSize($text, $page->getFont(), $page->getFontSize());
        while ($current_width > $width) {
            $text = substr($text, 0, strlen($text) - 1);
            $current_width = $this->widthForStringUsingFontSize($text, $page->getFont(), $page->getFontSize());
        }
        return $text;
    }

    /**
     * Add line return to fit multiline text to width
     *
     * @param unknown_type $text
     * @param unknown_type $width
     */
    public function WrapTextToWidth($page, $text, $width) {
        $t_words = explode(' ', $text);
        $retour = "";
        $current_line = "";
        for ($i = 0; $i < count($t_words); $i++) {
            if ($this->widthForStringUsingFontSize($current_line . ' ' . $t_words[$i], $page->getFont(), $page->getFontSize()) < $width) {
                $current_line .= ' ' . $t_words[$i];
            } else {
                if (($current_line != '') && (strlen($current_line) > 2))
                    $retour .= $current_line . "\n";
                $current_line = $t_words[$i];
            }

            if (strpos($t_words[$i], "\n") === false) {
                
            } else {
                if (($current_line != '') && (strlen($current_line) > 2))
                    $retour .= $current_line;
                $current_line = '';
            }
        }
        $retour .= $current_line;

        return $retour;
    }

    /**
     * Draw page number
     *
     */
    public function AddPagination($pdf) {
        $page_count = count($pdf->pages);
        for ($i = 0; $i < $page_count; $i++) {
            if ($i >= $this->firstPageIndex) {
                $page = $pdf->pages[$i];
                $pagination = ($i + 1 - $this->firstPageIndex) . ' / ' . ($page_count - $this->firstPageIndex);
                $page->setFillColor(new Zend_Pdf_Color_GrayScale(0.3));
                $page->setFont(Zend_Pdf_Font::fontWithName(Zend_Pdf_Font::FONT_HELVETICA), 10);
                $this->drawTextInBlock($page, $pagination, 0, 25, $this->_PAGE_WIDTH - 20, 40, 'r');
            }
        }
    }

    /**
     * Draw addresses & main text
     *
     */
    public function AddAddressesBlock($page, $LeftAddress, $RightAddress, $TxtDate, $TxtInfo) {
        $page->drawLine($this->_PAGE_WIDTH / 2 - 50, $this->y, $this->_PAGE_WIDTH / 2 - 50, $this->y - 160);

        $this->y -= 20;
        $page->setFont(Zend_Pdf_Font::fontWithName(Zend_Pdf_Font::FONT_HELVETICA), 12);
        $page->drawText($TxtDate, 25, $this->y, 'UTF-8');
        $page->drawText($TxtInfo, $this->_PAGE_WIDTH / 2 - 30, $this->y, 'UTF-8');

        $this->y -= 10;
        $page->drawLine(10, 710, $this->_BLOC_ENTETE_LARGEUR, $this->y);

        $this->y -= 20;
        $this->DrawMultilineText($page, $LeftAddress, 25, $this->y, 14, 0.4, 16);
        $this->DrawMultilineText($page, $RightAddress, $this->_PAGE_WIDTH / 2 - 30, $this->y, 14, 0.4, 16);

        $this->y -= 110;
        $page->setLineWidth(1.5);
        $page->drawLine(10, $this->y, $this->_BLOC_ENTETE_LARGEUR, $this->y);
    }

    /**
     * Format address
     */
    public function FormatAddress($adress, $caption = '', $show_details = false, $NoTvaIntraco = '') {
        if ($NoTvaIntraco == 'taxvat')
            $NoTvaIntraco = '';
        $FormatedAddress = "";
        if ($caption != '')
            $FormatedAddress = $caption . "\n ";
        if ($adress != null) {
            if ($adress->getcompany() != '')
                $FormatedAddress .= $adress->getcompany() . "\n ";
            if ($adress->getPrefix() == '')
                $FormatedAddress .= 'M. ';
            $FormatedAddress .= $adress->getName() . "\n ";
            $FormatedAddress .= $adress->getStreet(1) . "\n ";
            if ($adress->getStreet(2) != '')
                $FormatedAddress .= $adress->getStreet(2) . "\n ";
            if ($show_details) {
                if ($adress->getbuilding() != '')
                    $FormatedAddress .= ' Bat ' . $adress->getbuilding();
                if ($adress->getfloor() != '')
                    $FormatedAddress .= ' Etage ' . $adress->getfloor();
                if ($adress->getdoor_code() != '')
                    $FormatedAddress .= ' Code ' . $adress->getdoor_code();
                if ($adress->getappartment() != '')
                    $FormatedAddress .= ' Appt ' . $adress->getappartment();
                $FormatedAddress .= "\n ";
            }
            $FormatedAddress .= $adress->getPostcode() . ' ' . $adress->getCity() . "\n ";
            $FormatedAddress .= strtoupper(Mage::getModel('directory/country')->load($adress->getCountry())->getName()) . "\n ";
            if ($show_details)
                $FormatedAddress .= $adress->getcomments() . "\n ";
            if ($NoTvaIntraco != '')
                $FormatedAddress .= 'No TVA : ' . $NoTvaIntraco;
        }
        return $FormatedAddress;
    }

}
