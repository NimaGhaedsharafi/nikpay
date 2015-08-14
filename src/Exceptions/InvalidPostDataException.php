<?php
namespace Nikapps\NikPay\Exceptions;

class InvalidPostDataException extends PaymentException
{
    protected $message = "Post data is invalid";
}
