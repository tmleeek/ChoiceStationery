<?php
/**
 * Magento
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 *
 * @copyright  Copyright (c) 2013 BoostMyshop (http://www.boostmyshop.com)
 * @license    http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 * @package MDN_CrmTicket
 * @version 1.2
 */
class MDN_CrmTicket_Helper_Hashtag extends Mage_Core_Helper_Abstract
{
    const kPrefix = '(Ticket';
    const kSuffix = ')';
 
    /**
     * 
     * @param type $ticket
     */
    public function getHashtag($ticket)
    {
        return self::kPrefix.$ticket->getId().self::kSuffix;
    }
    
    /**
     * Try to find hashtag & ticket id in a string
     * @param type $content
     */
    public function getTicketIdFromContent($content)
    {
        $pattern = preg_quote(self::kPrefix).'(\d*)'.preg_quote(self::kSuffix);
        $pattern = str_replace('#', '\#', $pattern);
        $pattern = '#'.$pattern.'#';
        preg_match($pattern, $content, $match);
        if (isset($match[1]))
            return $match[1];
        else
            return null;
    }
    
}