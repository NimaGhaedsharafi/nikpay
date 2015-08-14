<?php
namespace Nikapps\NikPay;

use Nikapps\NikPay\Exceptions\BankNotFoundException;
use Nikapps\NikPay\Exceptions\NotFoundConfigurationException;
use Nikapps\NikPay\PaymentProviders\PaymentConfig;
use Nikapps\NikPay\PaymentProviders\PaymentProvider;
use Nikapps\NikPay\PaymentProviders\Saman\Saman;
use Nikapps\NikPay\PaymentProviders\Saman\SamanConfig;
use Nikapps\NikPay\PaymentProviders\Saman\SamanTranslator;
use Nikapps\NikPay\PaymentProviders\Translator;
use Nikapps\NikPay\Soap\PhpSoapService;

class NikPay
{

    protected $configs = [];

    protected $translators = [];

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
     * Set default error/state translator for bank
     *
     * @param string $bank
     * @param Translator $translator
     */
    public function useTranslator($bank, Translator $translator)
    {
        $this->translators[$bank] = $translator;
    }

    /**
     * Get error/state translator for bank
     *
     * @param string $bank
     * @return Translator
     */
    public function translator($bank)
    {
        if (!isset($this->translators[$bank])) {
            $this->generateDefaultTranslator($bank);
        }

        return $this->translators[$bank];
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
    protected function generatePayment($bank, PaymentConfig $config = null)
    {

        $this->guardAgainstNoConfiguration($bank, $config);

        $config = is_null($config) ? $this->configs[$bank] : $config;

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

    /**
     * verify there is a configuration for given bank
     *
     * @param string $bank
     * @param PaymentConfig $config
     * @throws NotFoundConfigurationException
     */
    protected function guardAgainstNoConfiguration($bank, PaymentConfig $config = null)
    {
        if (is_null($config) && !isset($this->configs[$bank])) {
            throw new NotFoundConfigurationException;
        }
    }

    /**
     * Generate default error/state translator for bank
     *
     * @param string $bank
     */
    protected function generateDefaultTranslator($bank)
    {
        switch ($bank) {
            case Bank::SAMAN:
                $this->translators[$bank] = new SamanTranslator();
        }
    }

}