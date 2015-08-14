<?php
namespace Nikapps\NikPay\Soap;

use Nikapps\NikPay\Exceptions\SoapException;

interface SoapService
{
    /**
     * Set soap options
     *
     * @param null|array $options
     * @return $this
     */
    public function options($options);

    /**
     * Set wsdl url
     *
     * @param string $wsdl
     * @return $this
     */
    public function wsdl($wsdl);

    /**
     * Create soap client
     *
     * @throws SoapException
     * @return $this
     */
    public function createClient();

    /**
     * refresh connections
     *
     * @return $this
     */
    public function refresh();

    /**
     * Do a soap request
     *
     * @param string $request
     * @param string $location
     * @param string $action
     * @param string $version
     * @param string $oneWay
     * @return mixed
     */
    public function doRequest($request, $location, $action, $version, $oneWay);

    /**
     * Get all functions from the service
     *
     * @return mixed
     */
    public function functions();

    /**
     * Get the last request
     *
     * @return mixed
     */
    public function lastRequest();

    /**
     * Get the last response
     *
     * @return mixed
     */
    public function lastResponse();

    /**
     * Do a soap call on the webservice client
     *
     * @param string $function
     * @param array $parameters
     * @return mixed
     */
    public function call($function, $parameters);
}
