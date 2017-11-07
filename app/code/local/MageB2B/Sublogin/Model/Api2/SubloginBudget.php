<?php
/**
 * @category Customer Service Version 3.0
 * @package MageB2B_Sublogin
 * @author AIRBYTES GmbH <info@airbytes.de>
 * @copyright AIRBYTES GmbH
 * @license commercial
 * @date 09.01.2016
 */
class MageB2B_Sublogin_Model_Api2_SubloginBudget extends Mage_Api2_Model_Resource
{

    const RESOURCE_CREATED_SUCCESSFUL = 'Resource created successful.';

    /**
     * retrieve sublogin budget collection
     * @return array
     */
    public function _retrieveCollection()
    {
        $return = array();
        $collection = Mage::getModel('sublogin/budget')->getCollection();
        
        if ($this->getRequest()->getParam('budget_id'))
        {
			$collection->addFieldToFilter('budget_id', $this->getRequest()->getParam('budget_id'));
		}
        
        foreach ($collection as $single)
        {
            $return[$single->getId()] = $single->getData();
        }
        return $return;
    }

    /**
     * retrieve single data item
     * @return array|mixed
     */
    protected function _retrieve()
    {
        $subloginBudgetModel = Mage::getModel('sublogin/budget')->load($this->getRequest()->getParam('id'));
        return $subloginBudgetModel->getData();
    }

    /**
     * creates a single sublogin budget model
     * @param array $data
     * @return string|void
     */
    public function _create($data)
    {
        $subloginBudgetModel = Mage::getModel('sublogin/budget');
        if ($subloginBudgetModel->load($data['id'])->getId())
        {
            $this->_critical(Mage::helper('sublogin')->__('This entity ID %s already exists. Please use PUT method to update.', $data['id']), Mage_Api2_Model_Server::HTTP_INTERNAL_ERROR);
        }
        
        foreach ($data as $key => $value)
        {
            $subloginBudgetModel->setData($key, $value);
        }
        
        // do 'checkIsUnique' after setting data in model
        if (!$subloginBudgetModel->checkIsUnique())
        {
			$msg = Mage::helper('sublogin')->__('Same configuration already exist for this sublogin: Year: "%s", Month: "%s" and Day: "%s"', $subloginBudgetModel->getYear(), $subloginBudgetModel->getMonth(), $subloginBudgetModel->getDay());
			
			$return['message'] = $msg;
			$return['data'] = $data;
			echo json_encode($return);
            return;
        }
        
        try
        {
            $subloginBudgetModel->save();
            $return = array();
            $return['sublogin_budget'] = $subloginBudgetModel->getData();
            
            $this->_successMessage(
                $this::RESOURCE_CREATED_SUCCESSFUL,
                Mage_Api2_Model_Server::HTTP_OK,
                $return
            );
            
            echo json_encode($return);
            return;
        }
        catch (Exception $e)
        {
			Mage::logException($e);
            $this->_critical($e->getMessage());
        }
    }

    /**
     * multi create several items
     * @param array $data
     */
    protected function _multiCreate(array $data)
    {
        $return = array();
        $failedCount = 0;
        foreach ($data as $singleData)
        {
            if (!is_array($singleData))
            {
                $this->_errorMessage(self::RESOURCE_DATA_INVALID, Mage_Api2_Model_Server::HTTP_BAD_REQUEST);
                $this->_critical(self::RESOURCE_DATA_PRE_VALIDATION_ERROR);
            }
            $subloginBudgetModel = Mage::getModel('sublogin/budget');
            if ($subloginBudgetModel->load($singleData['id'])->getId())
            {
                $this->_critical(Mage::helper('sublogin')->__('This entity ID %s already exists. Please use PUT method to update.', $singleData['id']), Mage_Api2_Model_Server::HTTP_INTERNAL_ERROR);
            }
            
            foreach ($singleData as $key => $value)
            {
                $subloginBudgetModel->setData($key, $value);
            }
            
            // do 'checkIsUnique' after setting data in model
			if (!$subloginBudgetModel->checkIsUnique())
			{
				$msg = Mage::helper('sublogin')->__('Same configuration already exist for this sublogin: Year: "%s", Month: "%s" and Day: "%s"', $subloginBudgetModel->getYear(), $subloginBudgetModel->getMonth(), $subloginBudgetModel->getDay());
				
				$return['failed'][$failedCount]['message'] = $msg;
				$return['failed'][$failedCount]['data'] = $singleData;
				$failedCount++;
				continue;
			}
            
            try
            {
                $subloginBudgetModel->save();
                $return['success']['sublogin_budget'][$subloginBudgetModel->getId()] = $subloginBudgetModel->getData();
                
                $this->_successMessage(
                    Mage_Api2_Model_Resource::RESOURCE_UPDATED_SUCCESSFUL,
                    Mage_Api2_Model_Server::HTTP_OK,
                    array(
                        'id' => $subloginBudgetModel->getId(),
                    )
                );
            }
            catch (Exception $e)
            {
                Mage::logException($e);
				$this->_error($e->getMessage());
            }
        }
        
        echo json_encode($return);
        return;
    }

    /**
     * Update a single sublogin budget item
     * @param array $data
     * @throws Mage_Api2_Exception
     */
    protected function _update(array $data)
    {
        $subloginBudgetModel = Mage::getModel('sublogin/budget')->load($this->getRequest()->getParam('id'));
        
        if (!$subloginBudgetModel->getId())
        {
            $this->_critical(Mage::helper('sublogin')->__('This entity ID doesn\'t exist'), Mage_Api2_Model_Server::HTTP_INTERNAL_ERROR);
        }
        
        foreach($data as $key=>$value)
        {
			$subloginBudgetModel->setData($key, $value);
		}
		
		// do 'checkIsUnique' after setting data in model
        if (!$subloginBudgetModel->checkIsUnique())
        {
			$msg = Mage::helper('sublogin')->__('Same configuration already exist for this sublogin: Year: "%s", Month: "%s" and Day: "%s"', $subloginBudgetModel->getYear(), $subloginBudgetModel->getMonth(), $subloginBudgetModel->getDay());
			
			$return['message'] = $msg;
			$return['data'] = $data;
			echo json_encode($return);
            return;
        }
		
        try
        {
            $subloginBudgetModel->save();
            
            $return = array();
            $return['sublogin_budget'] = $subloginBudgetModel->getData();            
            echo json_encode($return);
            return;
            
            $this->_successMessage(
                self::RESOURCE_UPDATED_SUCCESSFUL,
                Mage_Api2_Model_Server::HTTP_OK,
                $return
            );
        }
        catch (Mage_Core_Exception $e)
        {
            Mage::logException($e);
            $this->_error($e->getMessage(), Mage_Api2_Model_Server::HTTP_INTERNAL_ERROR);
        }
        catch (Exception $e)
        {
            Mage::logException($e);
            $this->_critical($e->getMessage());
        }
    }

    /**
     * multi update sublogin budget
     * @param array $data
     */
    protected function _multiUpdate(array $data)
    {
        $return = array();
        $failedCount = 0;
        foreach ($data as $singleData)
        {
            if (!is_array($singleData))
            {
                $this->_errorMessage(self::RESOURCE_DATA_INVALID, Mage_Api2_Model_Server::HTTP_BAD_REQUEST);
                $this->_critical(self::RESOURCE_DATA_PRE_VALIDATION_ERROR);
            }
            $subloginBudgetModel = Mage::getModel('sublogin/budget')->load($singleData['id']);
            if (!$subloginBudgetModel->getId())
            {
                $this->_critical(Mage::helper('sublogin')->__('This entity ID doesn\'t exist'), Mage_Api2_Model_Server::HTTP_INTERNAL_ERROR);
            }
            
            foreach($singleData as $key=>$value)
			{
				$subloginBudgetModel->setData($key, $value);
			}            
            
            // do 'checkIsUnique' after setting data in model
			if (!$subloginBudgetModel->checkIsUnique())
			{
				$msg = Mage::helper('sublogin')->__('Same configuration already exist for this sublogin: Year: "%s", Month: "%s" and Day: "%s"', $subloginBudgetModel->getYear(), $subloginBudgetModel->getMonth(), $subloginBudgetModel->getDay());
				
				$return[$failedCount]['message'] = $msg;
				$return[$failedCount]['data'] = $singleData;
				$failedCount++;
				continue;
			}
            
            try
            {
                $subloginBudgetModel->save();
                $return['sublogin_budget'][$subloginBudgetModel->getId()] = $subloginBudgetModel->getData();
                
                $this->_successMessage(
                    self::RESOURCE_UPDATED_SUCCESSFUL,
                    Mage_Api2_Model_Server::HTTP_OK,
                    array('budget_id' => $subloginBudgetModel->getId())
                );
            }
            catch (Mage_Core_Exception $e)
            {
                $this->_error($e->getMessage(), Mage_Api2_Model_Server::HTTP_INTERNAL_ERROR);
            }
            catch (Exception $e)
            {
                Mage::logException($e);
				//$this->_critical(self::RESOURCE_INTERNAL_ERROR);
				$this->_critical($e->getMessage());
            }
        }
		
		echo json_encode($return);
        return;
    }

    /**
     * Delete a single sublogin item
     */
    protected function _delete()
    {
        $subloginBudgetModel = Mage::getModel('sublogin/budget')->load($this->getRequest()->getParam('id'));
        if (!$subloginBudgetModel->getId())
        {
             $this->_critical(Mage::helper('sublogin')->__('This entity ID doesn\'t exist'), Mage_Api2_Model_Server::HTTP_INTERNAL_ERROR);
        }
        try
        {
            $subloginBudgetModel->delete();
            echo Mage::helper('sublogin')->__('Data set with entity ID %s successfully deleted.', $subloginBudgetModel->getId());
        }
        catch (Mage_Core_Exception $e)
        {
            $this->_critical($e->getMessage(), Mage_Api2_Model_Server::HTTP_INTERNAL_ERROR);
        }
        catch (Exception $e)
        {
            Mage::logException($e);
            $this->_critical($e->getMessage());
        }
    }

    /**
     * multi delete sublogins
     *
     * @param array $requestData
     */
    protected function _multiDelete(array $data)
    {
        $entityIds = array();
        foreach ($data as $singleData)
        {
            $subloginBudgetModel = Mage::getModel('sublogin/budget')->load($singleData['id']);
            if (!$subloginBudgetModel->getId())
            {
                $this->_critical(Mage::helper('sublogin')->__('This entity ID doesn\'t exist'), Mage_Api2_Model_Server::HTTP_INTERNAL_ERROR);
            }
            try
            {
                $subloginBudgetModel->delete();
                $entityIds[] = $subloginBudgetModel->getId();
            }
            catch (Mage_Core_Exception $e)
            {
                $this->_critical($e->getMessage(), Mage_Api2_Model_Server::HTTP_INTERNAL_ERROR);
            }
            catch (Exception $e)
            {
                Mage::logException($e);
				$this->_critical($e->getMessage());
            }
        }
        echo Mage::helper('sublogin')->__('Data set with entity IDs %s successfully deleted.', implode(',',$entityIds));
    }
}
