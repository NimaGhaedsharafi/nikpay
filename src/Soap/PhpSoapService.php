<?php
namespace Nikapps\NikPay\Soap;

class PhpSoapService implements SoapService
{

    /**
     * @var string
     */
    protected $wsdl = '';

    /**
     * @var null|array
     */
    protected $options = null;

    /**
     * @var \SoapClient
     */
    protected $client = null;

    /**
     * Set soap options
     *
     * @param null|array $options
     * @return $this
     */
    public function options($options)
    {
        $this->options = $options;

        return $this;
    }

    /**
     * Set wsdl url
     *
     * @param string $wsdl
     * @return $this
     */
    public function wsdl($wsdl)
    {
        $this->wsdl = $wsdl;

        return $this;
    }

    /**
     * Create soap client
     *
     * @return $this
     */
    public function createClient()
    {
        $this->client = new \SoapClient($this->wsdl, $this->options);

        return $this;
    }

    /**
     * refresh connections
     *
     * @return $this
     */
    public function refresh()
    {
        $this->wsdl = '';
        $this->options = null;
        $this->client = null;

        return $this;
    }

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
    public function doRequest($request, $location, $action, $version, $oneWay)
    {
        return $this->client->__doRequest($request, $location, $action, $version, $oneWay);
    }

    /**
     * Get all functions from the service
     *
     * @return mixed
     */
    public function functions()
    {
        return $this->client->__getFunctions();
    }

    /**
     * Get the last request
     *
     * @return mixed
     */
    public function lastRequest()
    {
        return $this->client->__getLastRequest();
    }

    /**
     * Get the last response
     *
     * @return mixed
     */
    public function lastResponse()
    {
        return $this->client->__getLastResponse();
    }

    /**
     * Do a soap call on the webservice client
     *
     * @param string $function
     * @param array $parameters
     * @return mixed
     */
    public function call($function, $parameters)
    {
        return call_user_func_array([$this->client, $function], $parameters);
    }

    /**
     * Do a soap call with magic method!
     *
     * @param string $name
     * @param array $parameters
     * @return mixed
     */
    public function __call($name, $parameters)
    {
        return $this->call($name, $parameters);
    }
} 