<?php

class MDN_ProfitReport_AdminController extends Mage_Adminhtml_Controller_Action
{
	 /**
     * 
     *
     */
	public function IndexAction()
	{
		$this->loadLayout();
		$this->renderLayout();
	}

	public function ReportAction()
	{
		//get settings
		$dateStart = $this->getRequest()->getPost('date_debut');
		$dateEnd = $this->getRequest()->getPost('date_fin');
		$affichageWeb = $this->getRequest()->getPost('affichage_web');
		$includeSku = $this->getRequest()->getPost('inclure_sku');
		$axe = $this->getRequest()->getPost('axe');
		
		//create report
		$report = mage::helper('ProfitReport/ProfitReport')->createReport($dateStart, 
															$dateEnd, 
															$axe,
															$includeSku);
															
		//add settings to report
		$settings = '';
		$settings .= 'Date debut;'.$dateStart."\n";
		$settings .= 'Date fin;'.$dateEnd."\n";
		$settings .= 'Axe ;'.$axe."\n\n\n\n";
															
		if ($affichageWeb)
			$this->showCsvInHtmlTable($report);
		else		
		{
			$csv = '';
			foreach($report as $item)
			{
				$csv .= $item['data']."\n";
			}
		
			$csv = $settings.$csv;
			$file_name = mage::helper('ProfitReport')->__('profit_report_%s_from_%s_to_%s', $axe, $dateStart, $dateEnd);
			$this->_prepareDownloadResponse($file_name.'.csv', $csv, 'text/csv'); 
		}
			
	}
	
/**
	 * Display csv file in html
	 */
	protected function showCsvInHtmlTable($lines)
	{
		echo '<div class="grid">';
		echo '<table border="1" cellspacing="0" align="center">';
		
		foreach($lines as $line)
		{
				
			echo '<tr>';
			$fields = explode(';', $line['data']);
			foreach($fields as $field)
				echo '<td align="center" class="'.$line['class'].'">'.$field.'</td>';
			echo '</tr>';
		}
		
		echo '</table>';
		echo '</div>';
	}
	
}