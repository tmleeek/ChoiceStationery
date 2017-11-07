<?php
/**
 * @category Customer Service Version 3.0
 * @package MageB2B_Sublogin
 * @author AIRBYTES GmbH <info@airbytes.de>
 * @copyright AIRBYTES GmbH
 * @license commercial
 * @date 09.01.2016
 */
class MageB2B_Sublogin_Model_Api2_Sublogin extends Mage_Api2_Model_Resource
{

    const RESOURCE_CREATED_SUCCESSFUL = 'Resource created successful.';

    /**
     * retrieve sublogin collection
     * @return array
     */
    public function _retrieveCollection()
    {
        $this->validateAccess();
        $return = array();
        $collection = Mage::getModel('sublogin/sublogin')->getCollection();
        
        if ($this->getRequest()->getParam('id')) {
			$collection->addFieldToFilter('id', $this->getRequest()->getParam('id'));
		}
		
        foreach ($collection as $sublogin)
        {
            $return[$sublogin->getId()] = $sublogin->getData();
        }
        return $return;
    }

    /**
     * retrieve single data item
     * @return array|mixed
     */
    protected function _retrieve()
    {
        $subloginModel = Mage::getModel('sublogin/sublogin')->load($this->getRequest()->getParam('id'));
        return $subloginModel->getData();
    }

    /**
     * creates a single sublogin model
     * @param array $data
     * @return string|void
     */
    public function _create($data)
    {
        $subloginModel = Mage::getModel('sublogin/sublogin');
        if ($subloginModel->load($data['id'])->getId())
        {
            $this->_critical(Mage::helper('sublogin')->__('This entity ID %s already exists. Please use PUT method to update.', $data['id']), Mage_Api2_Model_Server::HTTP_INTERNAL_ERROR);
        }
        if (isset($data['password']))
        {
            $data['password'] = Mage::helper('core')->getHash($data['password'], 2);
        }
        foreach ($data as $key => $value)
        {
            $subloginModel->setData($key, $value);
        }
        try
        {
            $subloginModel->save();
            $this->_successMessage(
                $this::RESOURCE_CREATED_SUCCESSFUL,
                Mage_Api2_Model_Server::HTTP_OK,
                array(
                    'id' => $subloginModel->getId(),
                )
            );
        }
        catch (Exception $e)
        {
            $this->_critical(self::RESOURCE_INTERNAL_ERROR);
        }
    }

    /**
     * multi create several items
     * @param array $data
     */
    protected function _multiCreate(array $data)
    {
        foreach ($data as $singleData)
        {
            if (!is_array($singleData))
            {
                $this->_errorMessage(self::RESOURCE_DATA_INVALID, Mage_Api2_Model_Server::HTTP_BAD_REQUEST);
                $this->_critical(self::RESOURCE_DATA_PRE_VALIDATION_ERROR);
            }
            $subloginModel = Mage::getModel('sublogin/sublogin');
            if ($subloginModel->load($singleData['id'])->getId())
            {
                $this->_critical(Mage::helper('sublogin')->__('This entity ID %s already exists. Please use PUT method to update.', $singleData['id']), Mage_Api2_Model_Server::HTTP_INTERNAL_ERROR);
            }
            if (isset($singleData['password']))
                $singleData['password'] = Mage::helper('core')->getHash($singleData['password'], 2);
            foreach ($singleData as $key => $value)
            {
                $subloginModel->setData($key, $value);
            }
            try
            {
                $subloginModel->save();
                $this->_successMessage(
                    Mage_Api2_Model_Resource::RESOURCE_UPDATED_SUCCESSFUL,
                    Mage_Api2_Model_Server::HTTP_OK,
                    array(
                        'id' => $subloginModel->getId(),
                    )
                );
            }
            catch (Exception $e)
            {
                $this->_critical(self::RESOURCE_INTERNAL_ERROR);
            }
        }
    }

    /**
     * Update a single sublogin item
     * @param array $data
     * @throws Mage_Api2_Exception
     */
    protected function _update(array $data)
    {
        $subloginModel = Mage::getModel('sublogin/sublogin')->load($this->getRequest()->getParam('id'));
        if (!$subloginModel->getId())
        {
            $this->_critical(Mage::helper('sublogin')->__('This entity ID doesn\'t exist'), Mage_Api2_Model_Server::HTTP_INTERNAL_ERROR);
        }
        if (isset($data['password']))
            $data['password'] = Mage::helper('core')->getHash($data['password'], 2);
        
        foreach($data as $key=>$value)
        {
			$subloginModel->setData($key, $value);
		}
		
        try
        {
            $subloginModel->save();
            $this->_successMessage(
                self::RESOURCE_UPDATED_SUCCESSFUL,
                Mage_Api2_Model_Server::HTTP_OK,
                array(
                    'id' => $subloginModel->getId(),
                )
            );
        }
        catch (Mage_Core_Exception $e)
        {
            $this->_error($e->getMessage(), Mage_Api2_Model_Server::HTTP_INTERNAL_ERROR);
        }
        catch (Exception $e)
        {
            $this->_critical(self::RESOURCE_INTERNAL_ERROR);
        }
    }

    /**
     * multi update sublogins
     * @param array $data
     */
    protected function _multiUpdate(array $data)
    {
        foreach ($data as $singleData)
        {
            if (!is_array($singleData))
            {
                $this->_errorMessage(self::RESOURCE_DATA_INVALID, Mage_Api2_Model_Server::HTTP_BAD_REQUEST);
                $this->_critical(self::RESOURCE_DATA_PRE_VALIDATION_ERROR);
            }
            $subloginModel = Mage::getModel('sublogin/sublogin')->load($singleData['id']);
            if (!$subloginModel->getId())
            {
                $this->_critical(Mage::helper('sublogin')->__('This entity ID doesn\'t exist'), Mage_Api2_Model_Server::HTTP_INTERNAL_ERROR);
            }
            if (isset($singleData['password']))
                $singleData['password'] = Mage::helper('core')->getHash($singleData['password'], 2);
            
            foreach($singleData as $key=>$value)
			{
				$subloginModel->setData($key, $value);
			}
            
            try
            {
                $subloginModel->save();
                $this->_successMessage(
                    self::RESOURCE_UPDATED_SUCCESSFUL,
                    Mage_Api2_Model_Server::HTTP_OK,
                    array('entity_id' => $subloginModel->getId())
                );
            }
            catch (Mage_Core_Exception $e)
            {
                $this->_error($e->getMessage(), Mage_Api2_Model_Server::HTTP_INTERNAL_ERROR);
            }
            catch (Exception $e)
            {
                $this->_critical(self::RESOURCE_INTERNAL_ERROR);
            }
        }

    }

    /**
     * Delete a single sublogin item
     */
    protected function _delete()
    {
        $subloginModel = Mage::getModel('sublogin/sublogin')->load($this->getRequest()->getParam('id'));
        if (!$subloginModel->getId())
        {
             $this->_critical(Mage::helper('sublogin')->__('This entity ID doesn\'t exist'), Mage_Api2_Model_Server::HTTP_INTERNAL_ERROR);
        }
        try
        {
            $subloginModel->delete();
            echo Mage::helper('sublogin')->__('Data set with entity ID %s successfully deleted.', $subloginModel->getId());
        }
        catch (Mage_Core_Exception $e)
        {
            $this->_critical($e->getMessage(), Mage_Api2_Model_Server::HTTP_INTERNAL_ERROR);
        }
        catch (Exception $e)
        {
            $this->_critical(self::RESOURCE_INTERNAL_ERROR);
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
            $subloginModel = Mage::getModel('sublogin/sublogin')->load($singleData['id']);
            if (!$subloginModel->getId())
            {
                $this->_critical(Mage::helper('sublogin')->__('This entity ID doesn\'t exist'), Mage_Api2_Model_Server::HTTP_INTERNAL_ERROR);
            }
            try
            {
                $subloginModel->delete();
                $entityIds[] = $subloginModel->getId();
            }
            catch (Mage_Core_Exception $e)
            {
                $this->_critical($e->getMessage(), Mage_Api2_Model_Server::HTTP_INTERNAL_ERROR);
            }
            catch (Exception $e)
            {
                $this->_critical(self::RESOURCE_INTERNAL_ERROR);
            }
        }
        echo Mage::helper('sublogin')->__('Data set with entity IDs %s successfully deleted.', implode(',',$entityIds));
    }
}
