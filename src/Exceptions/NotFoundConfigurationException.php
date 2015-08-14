<?php
namespace Nikapps\NikPay\Exceptions;

class NotFoundConfigurationException extends PaymentException
{
    protected $message = "Configuration is not found for given bank";
}
