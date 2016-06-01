<?php
namespace Nikapps\NikPay\PaymentProviders\Zarinpal;

use Nikapps\NikPay\Purchase;

class ZarinpalPurchase extends Purchase
{
    public function setEmail($email)
    {
        return $this->addOption('email', $email);
    }

    public function getEmail()
    {
        return $this->getOption('email');
    }

    public function setMobile($mobile)
    {
        return $this->addOption('mobile', $mobile);
    }

    public function getMobile()
    {
        return $this->getOption('mobile');
    }

    public function setDescription($description)
    {
        return $this->addOption('description', $description);
    }

    public function getDescription()
    {
        return $this->getOption('description');
    }
}