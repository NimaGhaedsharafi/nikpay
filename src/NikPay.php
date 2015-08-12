<?php
namespace Nikapps\NikPay;

use Nikapps\NikPay\Exceptions\BankNotFoundException;
use Nikapps\NikPay\PaymentProviders\PaymentConfig;
use Nikapps\NikPay\PaymentProviders\PaymentProvider;
use Nikapps\NikPay\PaymentProviders\Saman\Saman;
use Nikapps\NikPay\PaymentProviders\Saman\SamanConfig;
use Nikapps\NikPay\Soap\PhpSoapService;
use Nikapps\NikPay\Soap\SoapService;

class NikPay
{

    protected $configs = [];

    /**
     * Set default config for bank
     *
     * @param string $bank
     * @param PaymentConfig $config
     */
    public function useConfig($bank, PaymentConfig $config)
    {
        $this->configs[$bank] = $config;
    }

    /**
     *
     *
     * @param string $bank
     * @param PaymentConfig $config
     * @return PaymentProvider
     * @throws BankNotFoundException
     */
    public function bank($bank, PaymentConfig $config = null)
    {
        return $this->generatePayment($bank, $config);
    }

    /**
     * Generate payment provider class
     *
     * @param String $bank
     * @param PaymentConfig $config
     * @return PaymentProvider
     * @throws BankNotFoundException
     */
    protected function generatePayment($bank, PaymentConfig $config)
    {

        if (is_null($config)) {
            $config = $this->configs[$bank];
        }

        switch ($bank) {

            case Bank::SAMAN:

                /** @var SamanConfig $config */
                return $this->generateSamanPayment($config);

            default:
                throw new BankNotFoundException();

        }
    }

    /**
     * generate Saman payment class
     *
     * @param SamanConfig $config
     * @return Saman
     */
    protected function generateSamanPayment(SamanConfig $config)
    {
        return new Saman(new PhpSoapService(), $config);
    }
} 