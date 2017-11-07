<?php

class Mxm_AllInOne_Model_Api_Json
{
    protected $userAgent    = 'MxmJsonClient/2.0';
    protected $url          = null;
    protected $username     = null;
    protected $password     = null;
    protected $lastResponse = null;

    /**
     * Construct
     *
     * $config = array(
     *    'url'      => string,
     *    'username' => string,
     *    'password' => string
     * )
     *
     * @param array $config
     */
    public function __construct($config)
    {
        $this->url      = $config['url'];
        $this->username = $config['username'];
        $this->password = $config['password'];
    }

    /**
     * Post request
     *
     * @param array $data
     * @return string
     */
    protected function postRequest(array $data)
    {
        $ch = curl_init($this->url);

        $useragent = "{$this->userAgent} PHP/" . phpversion();

        $options = array(
            CURLOPT_POST           => true,
            CURLOPT_HTTPHEADER     => array(
                'Connection: close',
                "User-Agent: $useragent"
            ),
            CURLOPT_POSTFIELDS     => $data,
            CURLOPT_RETURNTRANSFER => true,
        );

        if (($auth = $this->getAuthentication())) {
            $options[CURLOPT_USERPWD] = $auth;
        }

        curl_setopt_array($ch, $options);

        $this->lastResponse = curl_exec($ch);

        $code = curl_getinfo($ch, CURLINFO_HTTP_CODE);

        if ($this->lastResponse === false || $code !== 200) {
            $error = "API request to url {$this->url} failed.";
            try {
                $message = $this->decodeJson($this->lastResponse);
                if (isset($message['msg']) && $message['msg']) {
                    $error = $message['msg'];
                }
            } catch (Exception $e) {}
            throw new Exception($error, $code);
        }

        curl_close($ch);

        return $this->lastResponse;
    }

    /**
     * Last response
     *
     * @return string
     */
    public function getLastResponse()
    {
        return $this->lastResponse;
    }

    /**
     * Decode JSON
     *
     * @param string $json
     * @return array
     */
    protected function decodeJson($json)
    {
        $result = json_decode($json, true);
        $ver = version_compare(PHP_VERSION, '5.3');
        if ($ver == '-1') {
            // Less than php5.3
            $error = '';
            if ($result === null) {
                $error = 'JSON was not able to be decoded';
            }
        } else {
            // At least php5.3
            switch (json_last_error()) {
                case JSON_ERROR_DEPTH:
                    $error = 'Maximum stack depth exceeded';
                    break;
                case JSON_ERROR_CTRL_CHAR:
                    $error = 'Unexpected control character found';
                    break;
                case JSON_ERROR_SYNTAX:
                    $error = 'Syntax error, malformed JSON';
                    break;
                case JSON_ERROR_NONE:
                default:
                    $error = '';
                    break;
            }
        }

        if (!empty($error)) {
            throw new Exception("Problem decoding json ($json), {$error}");
        }
        return $result;
    }

    /**
     * Get the string used for authentication
     *
     * @return string|null
     */
    protected function getAuthentication()
    {
        return $this->username . ':' . $this->password;
    }

    /**
     * Magic call for API method
     *
     * @param string $name
     * @param array $params
     * @return mixed
     */
    public function __call($name, $params)
    {
        $data = array(
            'method' => $name
        );
        for ($i = 0; $i < count($params); $i++) {
            if (is_array($params[$i])) {
                $params[$i] = json_encode($params[$i]);
            }
            $data['arg' . $i] = $params[$i];
        }
        $json = $this->postRequest($data);
        if ($json == 'null') {
            return null;
        }
        return $this->decodeJson($json);
    }

}