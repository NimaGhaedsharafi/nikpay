<?php
namespace Nikapps\NikPay\Exceptions;

class SoapException extends PaymentException
{

    /**
     * SoapFault exception
     *
     * @var \SoapFault
     */
    protected $soapFault;

    /**
     * @return mixed
     */
    public function getSoapFault()
    {
        return $this->soapFault;
    }

    /**
     * @param  \SoapFault $soapFault
     * @return $this
     */
    public function setSoapFault(\SoapFault $soapFault)
    {
        $this->soapFault = $soapFault;

        return $this;
    }


} 