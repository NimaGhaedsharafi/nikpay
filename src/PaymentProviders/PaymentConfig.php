<?php
namespace Nikapps\NikPay\PaymentProviders;

interface PaymentConfig
{
    /**
     * Generate config object from array
     *
     * @param array $config
     * @return self
     */
    public static function generateFromArray(array $config);
}
