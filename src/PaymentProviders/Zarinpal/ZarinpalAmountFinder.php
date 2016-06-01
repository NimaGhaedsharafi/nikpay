<?php
namespace Nikapps\NikPay\PaymentProviders\Zarinpal;

interface ZarinpalAmountFinder
{
    /**
     * Find amount/price of an order in Toman
     *
     * @param $reference
     * @param array $options
     * @return int|bool|null
     */
    public function find($reference, array $options = []);
}