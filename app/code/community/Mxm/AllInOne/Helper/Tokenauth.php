<?php

class Mxm_AllInOne_Helper_Tokenauth
{

    /**
     * Create a token auth object from an identity and secret
     *
     * @param string $identity
     * @param string $secret
     * @return string
     */
    public function createNew($identity, $secret)
    {
        $key = $this->createKey($identity, $secret);
        $identity  = str_replace(':', '', $identity);
        $timestamp = time();
        $token     = md5($key.$timestamp);
        return "$identity:$timestamp:$token";
    }

    /**
     * Create Key
     *
     * @param string $identity
     * @param string $secret
     * @return string
     */
    public function createKey($identity, $secret)
    {
        $identity = str_replace(':', '', $identity);
        return md5($secret.$identity);
    }
}
