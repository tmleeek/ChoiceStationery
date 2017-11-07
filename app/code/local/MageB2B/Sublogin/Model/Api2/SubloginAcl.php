<?php
/**
 * @category Customer Service Version 3.0
 * @package MageB2B_Sublogin
 * @author AIRBYTES GmbH <info@airbytes.de>
 * @copyright AIRBYTES GmbH
 * @license commercial
 * @date 09.01.2016
 */
class MageB2B_Sublogin_Model_Api2_SubloginAcl extends Mage_Api2_Model_Resource
{

    const RESOURCE_CREATED_SUCCESSFUL = 'Resource created successful.';

    /**
     * retrieve sublogin acl collection
     * @return array
     */
    public function _retrieveCollection()
    {	
        $return = array();
        $collection = Mage::getModel('sublogin/acl')->getCollection();
        
        if ($this->getRequest()->getParam('acl_id'))
        {
			$collection->addFieldToFilter('acl_id', $this->getRequest()->getParam('acl_id'));
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
        $this->validateAccess();
        $subloginAclModel = Mage::getModel('sublogin/acl')->load($this->getRequest()->getParam('id'));
        return $subloginAclModel->getData();
    }

    /**
     * creates a single sublogin acl model
     * @param array $data
     * @return string|void
     */
    public function _create($data)
    {
        $subloginAclModel = Mage::getModel('sublogin/acl');
        if ($subloginAclModel->load($data['id'])->getId())
        {
            $this->_critical(Mage::helper('sublogin')->__('This entity ID %s already exists. Please use PUT method to update.', $data['id']), Mage_Api2_Model_Server::HTTP_INTERNAL_ERROR);
        }
        
        foreach ($data as $key => $value)
        {
            $subloginAclModel->setData($key, $value);
        }
        try
        {
            $subloginAclModel->save();
            $this->_successMessage(
                $this::RESOURCE_CREATED_SUCCESSFUL,
                Mage_Api2_Model_Server::HTTP_OK,
                array(
                    'id' => $subloginAclModel->getId(),
                )
            );
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
        foreach ($data as $singleData)
        {
            if (!is_array($singleData))
            {
                $this->_errorMessage(self::RESOURCE_DATA_INVALID, Mage_Api2_Model_Server::HTTP_BAD_REQUEST);
                $this->_critical(self::RESOURCE_DATA_PRE_VALIDATION_ERROR);
            }
            $subloginAclModel = Mage::getModel('sublogin/acl');
            if ($subloginAclModel->load($singleData['id'])->getId())
            {
                $this->_critical(Mage::helper('sublogin')->__('This entity ID %s already exists. Please use PUT method to update.', $singleData['id']), Mage_Api2_Model_Server::HTTP_INTERNAL_ERROR);
            }
            
            foreach ($singleData as $key => $value)
            {
                $subloginAclModel->setData($key, $value);
            }
            try
            {
                $subloginAclModel->save();
                $this->_successMessage(
                    Mage_Api2_Model_Resource::RESOURCE_UPDATED_SUCCESSFUL,
                    Mage_Api2_Model_Server::HTTP_OK,
                    array(
                        'id' => $subloginAclModel->getId(),
                    )
                );
            }
            catch (Exception $e)
            {
                Mage::logException($e);
				$this->_error($e->getMessage());
            }
        }
    }

    /**
     * Update a single sublogin acl item
     * @param array $data
     * @throws Mage_Api2_Exception
     */
    protected function _update(array $data)
    {
        $subloginAclModel = Mage::getModel('sublogin/acl')->load($this->getRequest()->getParam('id'));
        
        if (!$subloginAclModel->getId())
        {
            $this->_critical(Mage::helper('sublogin')->__('This entity ID doesn\'t exist'), Mage_Api2_Model_Server::HTTP_INTERNAL_ERROR);
        }
        
        foreach($data as $key=>$value)
        {
			$subloginAclModel->setData($key, $value);
		}
        try
        {
            $subloginAclModel->save();
            $this->_successMessage(
                self::RESOURCE_UPDATED_SUCCESSFUL,
                Mage_Api2_Model_Server::HTTP_OK,
                array(
                    'id' => $subloginAclModel->getId(),
                )
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
     * multi update sublogin acl
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
            $subloginAclModel = Mage::getModel('sublogin/acl')->load($singleData['id']);
            if (!$subloginAclModel->getId())
            {
                $this->_critical(Mage::helper('sublogin')->__('This entity ID doesn\'t exist'), Mage_Api2_Model_Server::HTTP_INTERNAL_ERROR);
            }
            
            foreach($singleData as $key=>$value)
			{
				$subloginAclModel->setData($key, $value);
			}            
            
            try
            {
                $subloginAclModel->save();
                $this->_successMessage(
                    self::RESOURCE_UPDATED_SUCCESSFUL,
                    Mage_Api2_Model_Server::HTTP_OK,
                    array('acl_id' => $subloginAclModel->getId())
                );
            }
            catch (Mage_Core_Exception $e)
            {
                $this->_error($e->getMessage(), Mage_Api2_Model_Server::HTTP_INTERNAL_ERROR);
            }
            catch (Exception $e)
            {
                Mage::logException($e);
				$this->_critical($e->getMessage());
            }
        }

    }

    /**
     * Delete a single sublogin item
     */
    protected function _delete()
    {
        $subloginAclModel = Mage::getModel('sublogin/acl')->load($this->getRequest()->getParam('id'));
        if (!$subloginAclModel->getId())
        {
             $this->_critical(Mage::helper('sublogin')->__('This entity ID doesn\'t exist'), Mage_Api2_Model_Server::HTTP_INTERNAL_ERROR);
        }
        try
        {
            $subloginAclModel->delete();
            echo Mage::helper('sublogin')->__('Data set with entity ID %s successfully deleted.', $subloginAclModel->getId());
        }
        catch (Mage_Core_Exception $e)
        {
            $this->_critical($e->getMessage(), Mage_Api2_Model_Server::HTTP_INTERNAL_ERROR);
        }
        catch (Exception $e)
        {
            Mage::logException($e);
            //$this->_critical(self::RESOURCE_INTERNAL_ERROR);
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
            $subloginAclModel = Mage::getModel('sublogin/acl')->load($singleData['id']);
            if (!$subloginAclModel->getId())
            {
                $this->_critical(Mage::helper('sublogin')->__('This entity ID doesn\'t exist'), Mage_Api2_Model_Server::HTTP_INTERNAL_ERROR);
            }
            try
            {
                $subloginAclModel->delete();
                $entityIds[] = $subloginAclModel->getId();
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
