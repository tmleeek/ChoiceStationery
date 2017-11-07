<?php
/**
 * Generic SCA handling class for Maxemail's SCA solution
 *
 * @author martinp
 */

class Mxm_AllInOne_Model_Sca_Api
{
    /**
     * ID of customer space
     *
     * @var int
     */
    protected $customerId;

    /**
     * URL of Maxemail server
     *
     * @var string
     */
    protected $serverUrl;

    /**
     * ID of the basket type to use
     *
     * @var int
     */
    protected $basketTypeId;

    /**
     * Security salt for the specified basket type
     *
     * @var string
     */
    protected $securitySalt;

    /**
     * Valid keys to send for a basket request
     *
     * @var array
     */
    protected $sendKeys = array(
        'recipient'      => true,
        'customer_id'    => true,
        'basket_type_id' => true,
        'token'          => true,
        'items'          => true,
        'stage'          => true,
        'total_value'    => true,
        'custom_fields'  => true
    );

    /**
     * Valid keys for each item in a basket
     *
     * @var array
     */
    protected $itemKeys =  array(
        'product_code'  => true,
        'description'   => true,
        'name'          => true,
        'value'         => true,
        'quantity'      => true,
        'custom_fields' => true
    );

    /**
     * Set up the preliminary values
     *
     * $config = array(
     *    'customer_id'    => int,
     *    'server_url'     => string,
     *    'basket_type_id' => int,
     *    'security_salt'  => string
     * )
     *
     * @param array $config
     */
    public function __construct($config)
    {
        $this->customerId   = (int)$config['customer_id'];
        $this->serverUrl    = $this->validateUrl($config['server_url']);
        $this->basketTypeId = (int)$config['basket_type_id'];
        $this->securitySalt = $config['security_salt'];
    }

    /**
     * Set the items on the basket
     *
     * @param string $customerEmail
     * @param array $items
     * @param array $additional
     */
    public function setItems($customerEmail, array $items, array $additional = array())
    {
        $data = array(
            'recipient'      => $customerEmail,
            'customer_id'    => $this->customerId,
            'basket_type_id' => $this->basketTypeId,
            'token'          => $this->getToken($customerEmail),
            'items'          => $items
        );
        $data = $this->validateData(array_merge($additional, $data));
        $this->sendData("{$this->serverUrl}/behav/basket/setItems", $data);
    }

    /**
     * Set the stage of the basket
     *
     * @param string $customerEmail
     * @param string $stage
     * @param array $additional
     */
    public function setStage($customerEmail, $stage, array $additional = array())
    {
        $data = array(
            'recipient'      => $customerEmail,
            'customer_id'    => $this->customerId,
            'basket_type_id' => $this->basketTypeId,
            'token'          => $this->getToken($customerEmail),
            'stage'          => $stage
        );
        $data = $this->validateData(array_merge($additional, $data));
        $this->sendData("{$this->serverUrl}/behav/basket/setStage", $data);
    }

    /**
     * Perform the basket request
     *
     * @param string $url
     * @param array $data
     * @throws Exception
     */
    protected function sendData($url, $data)
    {
        $ch = curl_init($url);

        $options = array(
            CURLOPT_POST           => true,
            CURLOPT_HTTPHEADER     => array(
                'Connection: close',
                'User-Agent: MxmExt'
            ),
            CURLOPT_POSTFIELDS     => array(
                'mxm-form-json' => json_encode($data)
            )
        );

        curl_setopt_array($ch, $options);

        curl_exec($ch);

        curl_close($ch);
    }

    /**
     * Create the security token for the request
     *
     * @param string $email
     * @return string
     */
    protected function getToken($email)
    {
        return md5("{$this->customerId}-{$email}-{$this->securitySalt}");
    }

    /**
     * Validate the data array before sending
     *
     * @param array $data
     * @return array
     * @throws Exception
     */
    protected function validateData($data)
    {
        $data = array_intersect_key($data, $this->sendKeys);
        if (isset($data['items'])) {
            if (!is_array($data['items'])) {
                throw new Exception('Items must be an array of items');
            }
            $data['items'] = array_values($data['items']);
            foreach ($data['items'] as $i => $item) {
                $data['items'][$i] = array_intersect_key($item, $this->itemKeys);
            }
        }

        return $data;
    }

    /**
     * Validate the url provided to send to
     *
     * @param string $url
     * @return string
     * @throws Exception
     */
    protected function validateUrl($url)
    {
        $url = trim(($pos = strpos($url, '://')) ? substr($url, $pos + 3) : $url, '/');
        if ($url && strlen($url)) {
            return $url;
        }
        throw new Exception('Invalid server URL supplied');
    }
}
