<?php
 /**
  *
  *
  **/

class Juno_Jaffiliate_FeedController extends Mage_Core_Controller_Front_Action
{
	/**
	 * Show the feed.
	 */
	public function indexAction()
	{
		echo Mage::getSingleton('jaffiliate/affiliate')->getProductFeed(false);
		exit();
	}
	
	/**
	 * Save the feed to  a file.
	 */
	public function tofileAction()
	{
		Mage::getSingleton('jaffiliate/affiliate')->getProductFeed(true);
		exit();
	}
	
	/**
	 * CSV Generator
	 */
	public function csvAction()
	{
		Mage::getSingleton('jaffiliate/abstract')->printCsv(true);
		exit();
	}
	
	/**
	 * Test tracking code.
	 */
	public function testAction()
	{
		if(!$_GET['orderid']){ die('No order number.'); }
		
		header('Content-type: text/plain;');
		echo Mage::getSingleton('jaffiliate/affiliate')->getTrackingCode();
		exit();
	}

}