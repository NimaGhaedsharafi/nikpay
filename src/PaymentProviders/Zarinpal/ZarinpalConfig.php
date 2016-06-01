<?php
namespace Nikapps\NikPay\PaymentProviders\Zarinpal;

use Nikapps\NikPay\Utils\Utils;

class ZarinpalConfig
{
    /**
     * Merchant id
     *
     * @var string
     */
    protected $merchantId = '';

    protected $tokenEndpoint = 'https://www.zarinpal.com/pg/rest/WebGate/PaymentRequest.json';

    protected $verifyEndpoint = 'https://www.zarinpal.com/pg/rest/WebGate/PaymentVerification.json';

    protected $gatewayUrl = 'https://www.zarinpal.com/pg/StartPay/{token}';

    /**
     * Rest client options [optional]
     *
     * @var array
     */
    protected $clientOptions = [];

    /**
     * Redirect Url for calling back by bank
     *
     * @var string
     */
    protected $redirectUrl = '';

    /**
     * Generate config object from array
     *
     * @param array $config
     * @return self
     */
    public static function generateFromArray(array $config)
    {
        return (new ZarinpalConfig())
            ->setMerchantId(Utils::value($config, 'merchant', ''))
            ->setGatewayUrl(Utils::value($config, 'gateway'))
            ->setVerifyEndpoint(Utils::value($config, 'verify_endpoint'))
            ->setTokenEndpoint(Utils::value($config, 'token_endpoint'))
            ->setClientOptions(Utils::value($config, 'client_options', []))
            ->setRedirectUrl(Utils::value($config, 'redirect_url'));
    }

    /**
     * @param string $token
     * @return string
     */
    public function createGatewayUrl($token)
    {
        return strtr($this->gatewayUrl, [
            '{token}' => $token
        ]);
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
     * @return ZarinpalConfig
     */
    public function setMerchantId($merchantId)
    {
        $this->merchantId = $merchantId;

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
     * @return ZarinpalConfig
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
     * @return ZarinpalConfig
     */
    public function setVerifyEndpoint($verifyEndpoint)
    {
        if (!is_null($verifyEndpoint)) {
            $this->verifyEndpoint = $verifyEndpoint;
        }

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
     * @param string $gatewayUrl
     * @return ZarinpalConfig
     */
    public function setGatewayUrl($gatewayUrl)
    {
        if (!is_null($gatewayUrl)) {
            $this->gatewayUrl = $gatewayUrl;
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
     * @param array $clientOptions
     * @return ZarinpalConfig
     */
    public function setClientOptions(array $clientOptions = [])
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
     * @return ZarinpalConfig
     */
    public function setRedirectUrl($redirectUrl)
    {
        $this->redirectUrl = $redirectUrl;

        return $this;
    }

}