<?php

class Mxm_AllInOne_Model_Api
{
    protected $serverUrl = null;
    protected $username  = null;
    protected $password  = null;
    protected $services  = array();

    /**
     * Construct
     *
     * $config = array(
     *     'serverUrl' => string,
     *     'username'  => string,
     *     'password'  => string,
     * );
     *
     * @param $config
     */
    public function __construct($config) {
        $this->serverUrl = $config['serverUrl'];
        $this->username  = $config['username'];
        $this->password  = $config['password'];
    }

    /**
     * Get Json client for selected service
     *
     * @param string $service
     * @return Mxm_AllInOne_Model_Api_Json
     */
    public function getInstance($service)
    {
        if (!isset($this->services[$service])) {
            $url = "{$this->serverUrl}/api/json/$service";
            $this->services[$service] = Mage::getModel('mxmallinone/api_json', array(
                'url'      => $url,
                'username' => $this->username,
                'password' => $this->password
            ));
        }
        return $this->services[$service];
    }

    /**
     * Magic get for service
     *
     * @param string $name
     * @return Mxm_AllInOne_Model_Api_Json
     */
    public function __get($name)
    {
        return $this->getInstance($name);
    }

}