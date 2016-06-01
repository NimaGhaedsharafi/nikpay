<?php
namespace Nikapps\NikPay\Exceptions;

use GuzzleHttp\Exception\RequestException;

class GuzzleException extends PaymentException
{
    /**
     * @var RequestException
     */
    protected $requestException;

    /**
     * @return RequestException
     */
    public function getRequestException()
    {
        return $this->requestException;
    }

    /**
     * @param RequestException $requestException
     * @return GuzzleException
     */
    public function setRequestException(RequestException $requestException)
    {
        $this->requestException = $requestException;

        return $this;
    }


}