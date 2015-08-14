<?php
namespace Nikapps\NikPay\Exceptions;

class BankNotFoundException extends PaymentException
{

    protected $message = "The given bank doesn't exist";
} 