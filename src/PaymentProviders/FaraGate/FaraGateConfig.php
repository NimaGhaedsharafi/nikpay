<?php
namespace Nikapps\NikPay\PaymentProviders\FaraGate;

use Nikapps\NikPay\Utils\Utils;

class FaraGateConfig
{
    /**
     * Merchant id
     *
     * @var string
     */
    protected $merchantId = '';

    /**
     * Gateway url
     *
     * @var string
     */
    protected $gatewayUrl = 'https://faragate.com/services/payment/{token}';

    /**
     * Sandbox gateway url
     *
     * @var string
     */
    protected $sandboxGatewayUrl = 'https://faragate.com/services/payment_test/{token}';

    /**
     * Token endpoint
     *
     * @var string
     */
    protected $tokenEndpoint = 'http://faragate.com/services/paymentRequest.json';

    /**
     * Verifying endpoint
     *
     * @var string
     */
    protected $verifyEndpoint = 'http://faragate.com/services/paymentVerify.json';

    /**
     * Rest client options [optional]
     *
     * @var null
     */
    protected $clientOptions = [];

    /**
     * Redirect Url for calling back by bank
     *
     * @var string
     */
    protected $redirectUrl = '';

    /**
     * Plugin name
     *
     * @var string
     */
    protected $pluginName = 'Nikapp/Nikpay';

    /**
     * Is sandbox mode enabled?
     *
     * @var bool
     */
    protected $sandbox = false;

    /**
     * Generate config object from array
     *
     * @param array $config
     * @return self
     */
    public static function generateFromArray(array $config)
    {
        return (new FaraGateConfig())
            ->setMerchantId(Utils::value($config, 'merchant', ''))
            ->setGatewayUrl(Utils::value($config, 'gateway'))
            ->setSandboxGatewayUrl(Utils::value($config, 'sandbox_gateway'))
            ->setVerifyEndpoint(Utils::value($config, 'verify_endpoint'))
            ->setTokenEndpoint(Utils::value($config, 'token_endpoint'))
            ->setClientOptions(Utils::value($config, 'client_options', []))
            ->setRedirectUrl(Utils::value($config, 'redirect_url'))
            ->setPluginName(Utils::value($config, 'plugin', 'Nikapp/Nikpay'));
    }

    /**
     * @return string
     */
    public function getMerchantId()
    {
        return $this->merchantId;
    }

    /**
     * @param string $merchantId
     * @return FaraGateConfig
     */
    public function setMerchantId($merchantId)
    {
        $this->merchantId = $merchantId;

        return $this;
    }

    /**
     * @return string
     */
    public function getGatewayUrl()
    {
        return $this->gatewayUrl;
    }

    /**
     * @param string $token
     * @return string
     */
    public function createGatewayUrl($token)
    {
        $url = $this->isSandboxEnabled()
            ? $this->getSandboxGatewayUrl()
            : $this->getGatewayUrl();

        return strtr($url, [
            '{token}' => $token
        ]);
    }

    /**
     * @param string $gatewayUrl
     * @return FaraGateConfig
     */
    public function setGatewayUrl($gatewayUrl)
    {
        if (!is_null($gatewayUrl)) {
            $this->gatewayUrl = $gatewayUrl;
        }

        return $this;
    }

    /**
     * @return string
     */
    public function getSandboxGatewayUrl()
    {
        return $this->sandboxGatewayUrl;
    }

    /**
     * @param string $sandboxGatewayUrl
     * @return FaraGateConfig
     */
    public function setSandboxGatewayUrl($sandboxGatewayUrl)
    {
        if (!is_null($sandboxGatewayUrl)) {
            $this->sandboxGatewayUrl = $sandboxGatewayUrl;
        }

        return $this;
    }

    /**
     * @return string
     */
    public function getTokenEndpoint()
    {
        return $this->tokenEndpoint;
    }

    /**
     * @param string $tokenEndpoint
     * @return FaraGateConfig
     */
    public function setTokenEndpoint($tokenEndpoint)
    {
        if (!is_null($tokenEndpoint)) {
            $this->tokenEndpoint = $tokenEndpoint;
        }

        return $this;
    }

    /**
     * @return string
     */
    public function getVerifyEndpoint()
    {
        return $this->verifyEndpoint;
    }

    /**
     * @param string $verifyEndpoint
     * @return FaraGateConfig
     */
    public function setVerifyEndpoint($verifyEndpoint)
    {
        if (!is_null($verifyEndpoint)) {
            $this->verifyEndpoint = $verifyEndpoint;
        }

        return $this;
    }

    /**
     * @return null
     */
    public function getClientOptions()
    {
        return $this->clientOptions;
    }

    /**
     * @param null $clientOptions
     * @return FaraGateConfig
     */
    public function setClientOptions($clientOptions)
    {
        $this->clientOptions = $clientOptions;

        return $this;
    }

    /**
     * @return string
     */
    public function getRedirectUrl()
    {
        return $this->redirectUrl;
    }

    /**
     * @param string $redirectUrl
     * @return FaraGateConfig
     */
    public function setRedirectUrl($redirectUrl)
    {
        $this->redirectUrl = $redirectUrl;

        return $this;
    }

    /**
     * @return string
     */
    public function getPluginName()
    {
        return $this->pluginName;
    }

    /**
     * @param string $pluginName
     * @return FaraGateConfig
     */
    public function setPluginName($pluginName)
    {
        $this->pluginName = $pluginName;

        return $this;
    }

    /**
     * @return boolean
     */
    public function isSandboxEnabled()
    {
        return $this->sandbox;
    }

    /**
     * @return FaraGateConfig
     */
    public function enableSandbox()
    {
        $this->sandbox = true;

        return $this;
    }

    /**
     * @return FaraGateConfig
     */
    public function disableSandbox()
    {
        $this->sandbox = false;

        return $this;
    }

}