<?php

/**
 * 
 *
 */
class MDN_ProfitReport_Helper_Tool_Query extends Mage_Core_Helper_Abstract
{	
	private $_select = array();
	private $_from = array();
	private $_where = array();
	private $_groupBy = array();
	
	public function reset()
	{
		$this->_select = array();
		$this->_from = array();
		$this->_where = array();
		$this->_groupBy = array();
	}
	
	public function addSelect($select)
	{
		$this->_select[] = $select;
	}
		
	public function addFrom($from)
	{
		$this->_from[] = $from;
	}
		
	public function addWhere($where)
	{
		$this->_where[] = $where;
	}
		
	public function addGroupBy($groupBy)
	{
		$this->_groupBy[] = $groupBy;
	}
	
	/**
	 * Return sql results
	 */ 
	public function getResults()
	{
		$sql = $this->buildSql();
		$results = mage::getResourceModel('sales/order_item_collection')->getConnection()->fetchAll($sql);
		return $results;
	}
	
	public function getCol()
	{
		$sql = $this->buildSql();
		$results = mage::getResourceModel('sales/order_item_collection')->getConnection()->fetchCol($sql);
		return $results;
	}
	
	public function getOne()
	{
		$sql = $this->buildSql();
		try
		{
			$result = mage::getResourceModel('sales/order_item_collection')->getConnection()->fetchOne($sql);
		}
		catch(Exception $ex)
		{
			echo('<pre>'.$sql.'</pre>');
			echo '<p>'.$ex->getMessage().'</p>';
			echo '<p>'.$ex->getTraceAsString().'</p>';
			die('');
		}
		return $result;
	}
	
	/**
	 * generate SQL query
	 */ 
	public function buildSql()
	{
		$sql = ' select '."\n";		
		$sql .= implode(','."\n", $this->_select);
		
		$sql .= "\n".' from '."\n";
		$sql .= implode(','."\n", $this->_from);
		
		$sql .= "\n".' where '."\n";
		$sql .= implode("\n".' and ', $this->_where);
				
		if (count($this->_groupBy) > 0)
		{
			$sql .= "\n".' group by ';
			$sql .= implode(',', $this->_groupBy);
		}
				
		return $sql;
	}

}