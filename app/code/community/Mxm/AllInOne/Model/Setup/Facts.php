<?php

/**
 * Facts model
 *
 * @method int findFolder(string $fullPath)
 * @method int getFolder(string $fullPath)
 * @method setFolder(string $fullPath, int $value)
 * @method hasFolder(string $fullPath)
 * @method int findEmailTemplate(string $fullPath)
 * @method int getEmailTemplate(string $fullPath)
 * @method setEmailTemplate(string $fullPath, int $value)
 * @method hasEmailTemplate(string $fullPath)
 * @method int findProfile(string $fullPath)
 * @method int getProfile(string $fullPath)
 * @method setProfile(string $fullPath, int $value)
 * @method hasProfile(string $fullPath)
 * @method int findDatatable(string $fullPath)
 * @method int getDatatable(string $fullPath)
 * @method setDatatable(string $fullPath, int $value)
 * @method hasDatatable(string $fullPath)
 * @method int findFieldType(string $fullPath)
 * @method int getFieldType(string $fullPath)
 * @method setFieldType(string $fullPath, int $value)
 * @method hasFieldType(string $fullPath)
 * @method int findProfileField(string $fullPath)
 * @method int getProfileField(string $fullPath)
 * @method setProfileField(string $fullPath, int $value)
 * @method hasProfileField(string $fullPath)
 * @method int getDatatableField(string $fullPath)
 * @method setDatatableField(string $fullPath, int $value)
 * @method hasDatatableField(string $fullPath)
 * @method int findTriggerEmail(string $fullPath)
 * @method int getTriggerEmail(string $fullPath)
 * @method setTriggerEmail(string $fullPath, int $value)
 * @method hasTriggerEmail(string $fullPath)
 * @method int getBasketType(string $name)
 * @method setBasketType(string $name, int $value)
 * @method hasBasketType(string $name)
 */
class Mxm_AllInOne_Model_Setup_Facts
{
    /**
     * @var array
     */
    protected $facts = array();

    /**
     * @var Mxm_AllInOne_Model_Setup
     */
    protected $setup = null;

    /**
     * @var array
     */
    protected $itemTypes = array(
        'Folder' => array('api' => 'folder', 'id'  => 'folder_id'),
        'Profile' => array('api' => 'profile', 'id'  => 'profile_id'),
        'Datatable' => array('api' => 'datatable', 'id'  => 'datatable_id'),
        'EmailTemplate' => array('api' => 'email_template', 'id'  => 'template_id'),
        'FieldType' => array('api' => 'field_type', 'id'  => 'type_id'),
        'ProfileField' => array('api' => 'profile_field', 'id'  => 'field_id'),
        'TriggerEmail' => array('api' => 'email_triggered', 'id'  => 'email_id'),
    );

    /**
     *
     * @param Mxm_AllInOne_Model_Setup $setup
     */
    public function __construct($setup)
    {
        $this->setup = $setup;
    }

    /**
     * Get the facts for this type
     *
     * @param string $type
     * @return array
     */
    protected function &getFactArray($type)
    {
        if (!isset($this->facts[$type])) {
            $this->facts[$type] = array();
        }
        return $this->facts[$type];
    }

    /**
     * Remove all facts
     */
    public function clearFacts()
    {
        $this->facts = array();
    }

    /**
     * Set the id for the item with the full path
     *
     * @param string $type
     * @param string $fullPath
     * @param int $id
     */
    public function setFact($type, $fullPath, $id)
    {
        $facts =& $this->getFactArray($type);
        $facts[$fullPath] = $id;
    }

    /**
     * Get the id for this path
     *
     * @param string $type
     * @param string $fullPath
     * @return int
     */
    public function getFact($type, $fullPath)
    {
        $facts = $this->getFactArray($type);
        return $facts[$fullPath];
    }

    /**
     * Check if we have the id for this path
     *
     * @param string $type
     * @param string $fullPath
     * @return boolean
     */
    public function hasFact($type, $fullPath)
    {
        $facts = $this->getFactArray($type);
        return isset($facts[$fullPath]);
    }

    /**
     * Try to find the ID for an item of the type using the meta data for the
     * item type
     *
     * @param string $type
     * @param string $fullPath
     * @return int
     * @throws Exception
     */
    public function findFact($type, $fullPath)
    {
        if (!isset($this->itemTypes[$type])) {
            throw new Exception("Unknown item type $type");
        }
        $typeMeta = $this->itemTypes[$type];
        if (!$this->hasFact($type, $fullPath)) {
            $item = $this->setup->getApi($typeMeta['api'])->find($fullPath);
            $itemId = $item[$typeMeta['id']];
            $this->setFact($type, $fullPath, $itemId);
        } else {
            $itemId = $this->getFact($type, $fullPath);
        }
        return $itemId;
    }

    /**
     * Find a datatable field id for this path, use fact if present, otherwise
     * retrieve the id and store for next time
     *
     * @param string $fullPath
     * @return int
     */
    public function findDatatableField($fullPath)
    {
        if (!$this->hasDatatableField($fullPath)) {
            list($datatable, $field) = explode('.', $fullPath, 2);
            $fields = $this->setup->getApi('datatable_field')->fetchAll($datatable);
            foreach ($fields as $record) {
                $path = "{$datatable}.{$record['name']}";
                $this->setDatatableField($path, $record['field_id']);
                if ($record['name'] === $field) {
                    $fieldId = $record['field_id'];
                }
            }
            if (!$fieldId) {
                throw new Exception("Could not find datatable field $fullPath");
            }

        } else {
            $fieldId = $this->getDatatableField($fullPath);
        }
        return $fieldId;
    }

    /**
     * Find a basket type id for this path, use fact if present, otherwise
     * retrieve the id and store for next time
     *
     * @param string $name
     * @return int
     */
    public function findBasketType($name)
    {
        if (!$this->hasBasketType($name)) {
            $types  = $this->setup->getApi('basket_type')->fetchAll();
            $typeId = null;
            foreach ($types as $type) {
                $this->setBasketType($type['basket_name'], $type['basket_type_id']);
                if ($type['basket_name'] === $name) {
                    $typeId = $type['basket_type_id'];
                }
            }
            if (!$typeId) {
                throw new Exception("Could not find basket type $name");
            }
        } else {
            $typeId = $this->getBasketType($name);
        }
        return $typeId;
    }

    public function __call($name, $arguments)
    {

        if (substr($name, 0, 4) === 'find') {
            $type = substr($name, 4);
            $arguments = array_merge(array($type), $arguments);
            return call_user_func_array(array($this, 'findFact'), $arguments);
        }
        $type = substr($name, 3);
        switch (substr($name, 0, 3)) {
            case 'get':
                $arguments = array_merge(array($type), $arguments);
                return call_user_func_array(array($this, 'getFact'), $arguments);
            case 'set':
                $arguments = array_merge(array($type), $arguments);
                return call_user_func_array(array($this, 'setFact'), $arguments);
            case 'has':
                $arguments = array_merge(array($type), $arguments);
                return call_user_func_array(array($this, 'hasFact'), $arguments);
        }
        throw new Exception("Invalid method ".get_class($this)."::".$name."(".print_r($arguments,1).")");
    }
}