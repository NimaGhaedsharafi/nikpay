<?php
namespace Nikapps\NikPay\Exceptions;

use GuzzleHttp\Exception\ClientException;

class GuzzleException extends PaymentException
{
    /**
     * @var ClientException
     */
    protected $clientException;

    /**
     * @return ClientException
     */
    public function getClientException()
    {
        return $this->clientException;
    }

    /**
     * @param ClientException $clientException
     * @return GuzzleException
     */
    public function setClientException(ClientException $clientException)
    {
        $this->clientException = $clientException;

        return $this;
    }
    
}