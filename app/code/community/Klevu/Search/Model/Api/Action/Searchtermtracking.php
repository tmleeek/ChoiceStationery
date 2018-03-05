<?php

class Klevu_Search_Model_Api_Action_Searchtermtracking extends Klevu_Search_Model_Api_Action
{

    const ENDPOINT = "/analytics/n-search/search";
    const METHOD   = "POST";

    const DEFAULT_REQUEST_MODEL  = "klevu_search/api_request_get";
    const DEFAULT_RESPONSE_MODEL = "klevu_search/api_response_data";

    protected function validate($parameters) 
    {
        $errors = array();

        if (!isset($parameters["klevu_apiKey"]) || empty($parameters["klevu_apiKey"])) {
            $errors["klevu_apiKey"] = "Missing JS API key.";
        }

        if (!isset($parameters["klevu_term"]) || empty($parameters["klevu_term"])) {
            $errors["klevu_term"] = "Missing klevu term.";
        }

        if (!isset($parameters["klevu_totalResults"]) || empty($parameters["klevu_totalResults"])) {
            $errors["klevu_type"] = "Missing Total Results.";
        }

        if (!isset($parameters["klevu_shopperIP"]) || empty($parameters["klevu_shopperIP"])) {
            $errors["klevu_shopperIP"] = "Missing klevu shopperIP.";
        }

        if (!isset($parameters["klevu_typeOfQuery"]) || empty($parameters["klevu_typeOfQuery"])) {
            $errors["klevu_unit"] = "Missing Type of Query.";
        }

        if (count($errors) == 0) {
            return true;
        }

        return $errors;
    }


    /**
     * Execute the API action with the given parameters.
     *
     * @param array $parameters
     *
     * @return Klevu_Search_Model_Api_Response
     */
    public function execute($parameters = array()) 
    {
        $validation_result = $this->validate($parameters);
        if ($validation_result !== true) {
            return Mage::getModel('klevu_search/api_response_invalid')->setErrors($validation_result);
        }

        $request = $this->getRequest();

        $endpoint = Mage::helper('klevu_search/api')->buildEndpoint(
            static::ENDPOINT,
            $this->getStore(),
            Mage::helper('klevu_search/config')->getAnalyticsUrl()
        );

        $request
            ->setResponseModel($this->getResponse())
            ->setEndpoint($endpoint)
            ->setMethod(static::METHOD)
            ->setData($parameters);

        return $request->send();
    }
}
