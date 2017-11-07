<?php

/**
 * 
 *
 */
class MDN_ProfitReport_Helper_ProfitReport extends Mage_Core_Model_Abstract
{

	public function createReport($dateStart, $dateEnd, $axeAnalytique, $includeSubAxe)
	{
		$content = array();
	
		//Fields
		$helper = mage::helper('ProfitReport/Custom')->getAxeHelper($axeAnalytique);
		$axes = $helper->getAxes($dateStart, $dateEnd);
		$template = 'axe;subitem;order_count;customer_count;product_count;total_sales;total_percent_sales;margin_total;margin_percent;contribution_point;contribution_percent'."\n";
		
		$debug = '';
		
		//compute totals		
		$total = array();
		$total['name'] = 'Totals';
		$total['ca'] = $helper->getCa(null, $dateStart, $dateEnd);
		$total['marge_ke'] = $helper->getMargeKe(null, $dateStart, $dateEnd);
		$total['order_count'] = $helper->getOrderCount(null, $dateStart, $dateEnd);
		$total['customer_count'] = $helper->getCustomerCount(null, $dateStart, $dateEnd);
		$total['product_count'] = $helper->getProductCount(null, $dateStart, $dateEnd);
		
		//browses axes first time to set values
		for($i=0;$i<count($axes);$i++)
		{
			$axes[$i]['order_count'] = $helper->getOrderCount($axes[$i]['id'], $dateStart, $dateEnd);
			$axes[$i]['customer_count'] = $helper->getCustomerCount($axes[$i]['id'], $dateStart, $dateEnd);
			$axes[$i]['product_count'] = $helper->getProductCount($axes[$i]['id'], $dateStart, $dateEnd);
			$axes[$i]['ca'] = $helper->getCa($axes[$i]['id'], $dateStart, $dateEnd);
			$axes[$i]['marge_ke'] = $helper->getMargeKe($axes[$i]['id'], $dateStart, $dateEnd);
			$axes[$i]['debug'] = $helper->getDebug($axes[$i]['id'], $dateStart, $dateEnd);
			$debug .= $axes[$i]['debug'];
						
			if ($includeSubAxe)
			{
				$axes[$i]['subitems'] = $helper->getSubAxes($axes[$i]['id'], $dateStart, $dateEnd);
			}
			
			$helper->afterAxe($axes[$i]['id'], $dateStart, $dateEnd);

		}
		
		//translate header
		$fields = explode(';', $template);
		$line = '';
		foreach($fields as $field)
		{
			$line .= mage::helper('ProfitReport')->__($field).";";
		}
		$content[] = array('class' => 'rapport_header', 'data' => $line);
		
		//create export file
		foreach($axes as $axe)
		{
			//skip if no CA
			if ($axe['ca'] == 0)
				continue;
			
			//add other information
			$axe['ca_pourcent'] = ($total['ca'] > 0 ? ($axe['ca'] / $total['ca'] * 100) : 0);
			$axe['marge_pourcent'] = ($axe['ca'] > 0 ? ($axe['marge_ke'] / $axe['ca'] * 100) : 0);
			$axe['contribution_point'] = ($axe['ca_pourcent'] > 0 ? $axe['marge_pourcent'] * $axe['ca_pourcent'] * 100  / 10000: 0);
			$axe['contribution_pourcent'] = ($total['marge_ke'] > 0 ? $axe['marge_ke'] / $total['marge_ke'] * 100 : 0);
						
			//add sub axes
			if ($includeSubAxe)
			{
				foreach($axe['subitems'] as $subAxe)
				{
				
					$subAxe['ca'] = $helper->getCa($axe['id'], $dateStart, $dateEnd, $subAxe['id']);
					$subAxe['order_count'] = $helper->getOrderCount($axe['id'], $dateStart, $dateEnd, $subAxe['id']);
					$subAxe['customer_count'] = $helper->getCustomerCount($axe['id'], $dateStart, $dateEnd, $subAxe['id']);
					$subAxe['product_count'] = $helper->getProductCount($axe['id'], $dateStart, $dateEnd, $subAxe['id']);
					$subAxe['ca_pourcent'] = ($axe['ca'] > 0 ? ($subAxe['ca'] / $axe['ca'] * 100) : 0);
					$subAxe['marge_ke'] = $helper->getMargeKe($axe['id'], $dateStart, $dateEnd, $subAxe['id']);
					$subAxe['marge_pourcent'] = ($subAxe['ca'] > 0 ? ($subAxe['marge_ke'] / $subAxe['ca'] * 100) : 0);
					$subAxe['debug'] = $helper->getDebug($axe['id'], $dateStart, $dateEnd, $subAxe['id']);
					
					$subAxe['contribution_point'] = ($subAxe['ca_pourcent'] > 0 ? $subAxe['marge_pourcent'] * $subAxe['ca_pourcent'] * 100 / 10000 : 0);
					$subAxe['contribution_pourcent'] = ($subAxe['marge_ke'] > 0 ? $subAxe['marge_ke'] / $axe['marge_ke'] * 100 : 0);

					if ($subAxe['ca'] == 0)
						continue;
				
					$line = $template;
					$line = str_replace('axe', $axe['name'], $line);
					$line = str_replace('subitem', $subAxe['name'], $line);
					$line = str_replace('order_count', (int)$subAxe['order_count'], $line);
					$line = str_replace('customer_count', (int)$subAxe['customer_count'], $line);
					$line = str_replace('product_count', (int)$subAxe['product_count'], $line);
					$line = str_replace('total_sales', number_format($subAxe['ca'], 2, ',', ''), $line);
					$line = str_replace('total_percent_sales', number_format($subAxe['ca_pourcent'], 2, ',', '').'%', $line);
					$line = str_replace('margin_total', number_format($subAxe['marge_ke'], 2, ',', ''), $line);
					$line = str_replace('margin_percent', number_format($subAxe['marge_pourcent'], 2, ',', '').'%', $line);
					$line = str_replace('contribution_point', ((int)$subAxe['contribution_point']), $line);
					$line = str_replace('contribution_percent', (number_format($subAxe['contribution_pourcent'], 2, ',', '')).'%', $line);
					$line = str_replace('debug', $subAxe['debug'], $line);
					
					$content[] = array('class' => 'rapport_subaxe', 'data' => $line);
					
				}
			}
			
			//add axe line
			$line = $template;
			$line = str_replace('axe', $axe['name'], $line);
			$line = str_replace('subitem', '', $line);
			$line = str_replace('order_count', (int)$axe['order_count'], $line);
			$line = str_replace('customer_count', (int)$axe['customer_count'], $line);
			$line = str_replace('product_count', (int)$axe['product_count'], $line);
			$line = str_replace('total_sales', number_format($axe['ca'], 2, ',', ''), $line);
			$line = str_replace('total_percent_sales', number_format($axe['ca_pourcent'], 2, ',', '').'%', $line);
			$line = str_replace('margin_total', number_format($axe['marge_ke'], 2, ',', ''), $line);
			$line = str_replace('margin_percent', number_format($axe['marge_pourcent'], 2, ',', '').'%', $line);
			$line = str_replace('contribution_point', ((int)$axe['contribution_point']), $line);
			$line = str_replace('contribution_percent', number_format($axe['contribution_pourcent'], 2, ',', '').'%', $line);
			$line = str_replace('debug', $axe['debug'], $line);
			$debug .= $axe['debug'];
			
			//append to file
			$content[] = array('class' => 'rapport_axe', 'data' => $line);

		}
		
		
		//add total
		$line = $template;
		$line = str_replace('axe', $total['name'], $line);
		$line = str_replace('subitem', '', $line);
		$line = str_replace('order_count', (int)$total['order_count'], $line);
		$line = str_replace('customer_count', (int)$total['customer_count'], $line);
		$line = str_replace('product_count', (int)$total['product_count'], $line);
		$line = str_replace('total_sales', number_format($total['ca'], 2, ',', ''), $line);
		$line = str_replace('total_percent_sales', '100%', $line);
		$line = str_replace('margin_total', number_format($total['marge_ke'], 2, ',', ''), $line);
		$line = str_replace('margin_percent', ($total['ca'] > 0 ? number_format($total['marge_ke'] / $total['ca'] * 100, 2, ',', '') : 0).'%', $line);
		$line = str_replace('contribution_point', ($total['ca'] > 0 ? number_format($total['marge_ke'] / $total['ca'] * 100, 2, ',', '') : 0).'%', $line);
		$line = str_replace('contribution_percent', '100%', $line);
		$line = str_replace('debug', '', $line);
		
		$content[] = array('class' => 'rapport_total', 'data' => $line);

		//get missing debug
		$missingDebug = $this->getMissingDebug($debug);
		$content[] = array('class' => 'misc', 'data' => $missingDebug);
		
		return $content;
	}
	
	private function getMissingDebug($debug)
	{
		$debug = str_replace(' ', '', $debug);
		$t = explode(',', $debug);
		$missingDebug = '';
		
		//remove doublon
		$t = array_unique($t);
		
		//sort
		sort($t);
		
		//find missing value
		$root = '';
		$lastRoot = '';
		$lastPostFixValue = 0;
		foreach($t as $invoiceNumber)
		{
			//set root value
			$root = substr($invoiceNumber, 0, 8);
			if ($root == $lastRoot)
			{
				$lastRoot = $root;
				$lastPostFixValue = 0;
			}
			
			//check postfix
			$postFixValue = (int)substr($invoiceNumber, 8, 3);
			$diff = $postFixValue - $lastPostFixValue;
			if ($diff > 1)
			{
				for ($i=$lastPostFixValue+1;$i<$postFixValue;$i++)
				{
					$missingDebug .= $root.str_pad($i, 3, '0', STR_PAD_LEFT).", ";
				}
			}
			$lastPostFixValue = $postFixValue;
		}
		
		return $missingDebug;
	}
	
}