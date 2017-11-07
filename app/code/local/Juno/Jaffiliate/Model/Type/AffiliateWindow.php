<?php
/**
 * Affiliate Window for Magento File.
 *
 */
 
class Juno_Jaffiliate_Model_Type_AffiliateWindow extends Juno_Jaffiliate_Model_Abstract
{	
	/**
	 * Get the tracking code for the footer section.
	 */
	public function getFooterCode()
	{
		$settings = Mage::getStoreConfig('jaffiliate/general');
		$_pr_settings = Mage::getStoreConfig('jaffiliate/affiliatewindow');
		if($settings['footer'] == 0){
			return false;
		}
		return '<script src="https://www.dwin1.com/'.$_pr_settings['program_id'].'.js" type="text/javascript" defer="defer"></script>';
	}
	
	/**
	 * De-duping code.
	 */
	public function getPersistant()
	{	
/* depreciated in 2.5.8
		$_settings = Mage::getStoreConfig('jaffiliate/deduping');
		$_pr_settings = Mage::getStoreConfig('jaffiliate/affiliatewindow');
		
		if($referal_source = Mage::app()->getRequest()->getParam($_settings['key']) && $_settings['enabled'] == 1){
			$cookie = Mage::getSingleton('core/cookie');
			$cookie->set('source', Mage::app()->getRequest()->getParam($_settings['key']), time()+2592000, '/');
		}
		return true;
*/
	}
	
	/**
	 * Get the tracking code for the success page.
	 */
	public function getTrackingCode()
	{
		if($this->getKeyResult()){
			echo '<!-- Licence Error. Please contact Juno Media at support@junowebdesign.com with your order number and domain name. -->';
			return;
		}
		$_settings = Mage::getStoreConfig('jaffiliate/general');
		$_dupe_settings = Mage::getStoreConfig('jaffiliate/deduping');
		$_pr_settings = Mage::getStoreConfig('jaffiliate/affiliatewindow');
		if($_settings['tracking'] == 0){
			echo '<!-- Affiliate tracking code is currently disabled. -->';
			return;
		}
		if($_dupe_settings['enabled'] == 1){
			if(isset($_pr_settings['dedupingvalue'])){
				$val = $_pr_settings['dedupingvalue'];
			} else {
				$val = $_dupe_settings['val'];
			}
			if($_COOKIE[$_dupe_settings['key']] != $val){
				echo '<!-- No Affiliate Window tracking code due to de-duping check. -->';
				return;
			}
		}
		$_orderdata = $this->getFormattedOrderData($_pr_settings);
		
		if(strstr($_settings['event_id'], ',')){
			$event_id = explode(',', $_settings['event_id']);
			$event_id = trim($event_id[0]);
		} else {
			$event_id = trim($_settings['event_id']);
		}
		$groups = array();
		foreach($_orderdata['items'] as $item){
			if($item['event_id']){ $event_id = $item['event_id']; } 
			$item_data[] = 'AW:P|'.$_pr_settings['program_id'].'|'.$_orderdata['increment_id'].'|'.$item['product_id'].'|'.$item['name'].'|'.number_format($item['price_each'], 2, '.', '').'|'.number_format($item['qty'], 0, '.', '').'|'.$item['sku'].'|'.$event_id.'|';
			if(isset($groups[$event_id])){
				$groups[$event_id] += number_format(($item['price_each']*$item['qty']), 2, '.', '');
			} else {
				$groups[$event_id] = number_format(($item['price_each']*$item['qty']), 2, '.', '');
			}
		}
		$parts = array();
		foreach($groups as $group_id=>$group_price){
			$parts[] = $group_id.':'.$group_price;
		}
        $data = array('tt'		=> 'ns',
					  'tv'		=> '2',
					  'merchant'=> $_pr_settings['program_id'],
					  'amount'	=> $_orderdata['item_total_sale_count'],
					  'ref'		=> $_orderdata['increment_id'],
					  'parts'	=> implode('|',$parts),
					  'vc'		=> $_orderdata['coupon_code'],
					  'testmode'=> $_settings['test'],
					  'cr'		=> $_orderdata['global_currency_code']);

        $html = '<!-- affiliate window tracking code -->';
        $noscript_string = array();
        foreach($data as $key=>$value){
		    $noscript_string[] = $key.'='.urlencode($value);
        }
        
        $html .= '<img src="https://www.awin1.com/sread.img?'.implode('&amp;', $noscript_string).'" />
<form style="display:none;" name="aw_basket_form">
<textarea wrap="physical" id="aw_basket">';
 		foreach($item_data as $item){
	 		$html .= "\r".$item;
 		}
		$html .= '
</textarea>
</form>
<script type="text/javascript">
//<![CDATA[
var AWIN = {};
AWIN.Tracking = {};
AWIN.Tracking.Sale = {};
AWIN.Tracking.Sale.amount = \''.number_format($_orderdata['item_total_sale_count'], 2, '.', '').'\';
AWIN.Tracking.Sale.currency = \''.$_orderdata['global_currency_code'].'\';
AWIN.Tracking.Sale.orderRef = \''.$_orderdata['increment_id'].'\';
AWIN.Tracking.Sale.parts = \''.implode('|',$parts).'\';
AWIN.Tracking.Sale.voucher = \''.$_orderdata['coupon_code'].'\';
AWIN.Tracking.Sale.test = \''.$_settings['test'].'\';
//]]>
</script>';

		return $html;
	}
	
	/**
	 * Generate the product feed on the fly.
	 */
	public function getFeed($full_file = false)
	{
		// -- Get the Settings
		$_settings = Mage::getStoreConfig('jaffiliate/general');
		$_pr_settings = Mage::getStoreConfig('jaffiliate/affiliatewindow');
		set_time_limit(1200);
		ini_set('max_execution_time',1200);
		// -- if its disabled, quit here -- 
		if($_settings['feed'] == 0)
			die('Affiliate feed is currently disabled.');
			
		// -- Set the limits
		if(isset($_GET['start'])){ $start = $_GET['start']; } else { $start = 0; }
		if(isset($_GET['count'])){ $count = $_GET['count']; } else { $count = 0; }
		$limit = 50;
		$max = $this->getMax();
		$pages = ceil(($max/$limit));
		// -- --
		$xml = '';
		$delete = array(' &copy;', '©', 'Â', '&nbsp;');

		//while($pages>=$count){
			// -- if this is the start, put the header in.
			if($count == 0){
				Mage::getModel('core/session')->setUsedPids(array());
				$xml .= '<?xml version="1.0" encoding="UTF-8"?><!DOCTYPE merchant SYSTEM "http://www.affiliatewindow.com/DTD/merchant/datafeedupload.1.3.dtd"><merchant>'."\n";
			}
			echo 'Pages: '.$pages.' Count: '.$count.' Start: '.$start.' Limit: '.$limit.'<Br />';
			$products = $this->getProductsData($limit, $start);
			foreach($products as $product){
			
				//echo '<pre>'; print_r($product); echo '</pre>'; exit();
			
				if(!in_array($product['entity_id'], Mage::getModel('core/session')->getUsedPids())){
					$xml .= '<product weboffer="no" preorder="no" instock="yes" forsale="yes">
								<pid>'.$product['entity_id'].'</pid>
								<name><![CDATA['.$product['name'].']]></name>
								<desc><![CDATA['.iconv("UTF-8", "ASCII//TRANSLIT", str_replace($delete, '', $product['description'])).']]></desc>
								<spec><![CDATA['.iconv("UTF-8", "ASCII//TRANSLIT", str_replace($delete, '', $product['short_description'])).']]></spec>
								<category><![CDATA['.$_pr_settings['category'].']]></category>
								<lang><![CDATA['.$_settings['language'].']]></lang>
								<brand><![CDATA['.$manufacturer.']]></brand>
								<purl><![CDATA['.Mage::getBaseUrl().$product['url_path'].']]></purl>
								<imgurl><![CDATA['.$product['image'].']]></imgurl>
								<thumburl><![CDATA['.$product['thumbnail'].']]></thumburl>
								<deltime><![CDATA['.$_settings['delivery_period'].']]></deltime>
								<currency><![CDATA['.$_settings['currency'].']]></currency>
								<price>
									<actualp>'.$product['final_price'].'</actualp>
									<rrpp>'.$product['price'].'</rrpp>
								</price>
								<delcost><![CDATA['.$_settings['delivery_cost'].']]></delcost>
							</product>'."\n";
					if(!$used = Mage::getModel('core/session')->getUsedPids()){		
						$used = array();
					}
					$used[] = $product['entity_id'];
					Mage::getModel('core/session')->setUsedPids($used);
				}
			}
			if($count==0){ $start_file = true; } else { $start_file = false; }
			$count++;
			//$start = $start+$limit;
			$file = $this->toFile('xml', 'AffiliateWindow', $xml, $start_file);
			$xml = '';
		//}
		
		if($pages>=$count){
			echo '<script type="text/javascript">window.location.replace("'.Mage::getBaseUrl().'jaffiliate/feed/?count='.$count.'&start='.($start+$limit).(($_GET['type']) ? '&type='.$_GET['type']  : '').'");</script>';
			exit();
	    }
		
	    $xml = '</merchant>';
	    $file = $this->toFile('xml', 'AffiliateWindow', $xml, false);
		die('Export completed: '.$file);
	}
	
}