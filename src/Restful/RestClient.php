<?php
namespace Nikapps\NikPay\Restful;

interface RestClient
{
    /**
     * Send a get request
     *
     * @param string $url
     * @param array $options
     *
     * @return array
     */
    public function get($url, array $options = []);

    /**
     * Send a post request
     *
     * @param string $url
     * @param array $options
     *
     * @return array
     */
    public function post($url, array $options = []);
}
