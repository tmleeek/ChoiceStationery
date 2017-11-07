<?php
class Mage_Adminhtml_Block_Sales_Order_Renderer_Profit extends Mage_Adminhtml_Block_Widget_Grid_Column_Renderer_Abstract
{
    public function render(Varien_Object $row)
    {
        $order_id = $row->getData($this->getColumn()->getIndex());
          
        if(!empty($order_id))
        {   
            $sales_model = Mage::getModel('sales/order')->load($order_id);
            $subtotal = $sales_model->getSubtotal();//get order subtotal (without shipping)          
            $items = $sales_model->getAllItems(); //get all order items
            $base_cost = array();
            if(!empty($items))
            {               
                foreach ($items as $itemId => $item)
                {               
                    $qty = intval($item->getQtyOrdered()); //get items quantity
                    if(empty($qty))
                    {
                        $qty = 1;
                    }
                    $b_cost = $item->getBaseCost();//get item cost                   
                    $base_cost[] = ($b_cost*$qty); //get all items cost     
                }
            }
            $total_order_cost = '';
            if(!empty($base_cost))
            {
                $total_order_cost = array_sum($base_cost); //get sum of all items cost
            }
            $profit = '';
            if(!empty($total_order_cost))
            {
                $profit = ($subtotal-$total_order_cost); //get profit , subtraction of order subtotal 
            }
             
            $_coreHelper = $this->helper('core');
			$perc			=round((($subtotal-$total_order_cost)/$subtotal)*100);
			if($profit<=0) { $profit = "Not Recorded"; }
            else { $profit = $_coreHelper->currency($profit). " (".$perc."%)"; }
     
            return $profit;
        }
         
    }
}
?>