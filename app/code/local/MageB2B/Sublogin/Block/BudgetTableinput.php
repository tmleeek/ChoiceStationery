<?php
/**
 * @category Customer Service Version 3.0
 * @package MageB2B_Sublogin
 * @author AIRBYTES GmbH <info@airbytes.de>
 * @copyright AIRBYTES GmbH
 * @license commercial
 * @date 09.01.2016
 */
class MageB2B_Sublogin_Block_BudgetTableinput extends MageB2B_Sublogin_Block_Tableinput
{
    public function __construct()
    {
        parent::__construct();
        $this->setTemplate('sublogin/budgetTableinput.phtml');
    }
}