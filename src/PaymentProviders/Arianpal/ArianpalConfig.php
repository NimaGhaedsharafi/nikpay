<?php
/**
 * Created by PhpStorm.
 * User: nghaedsharafi
 * Date: 6/16/17
 * Time: 23:19
 */

namespace Nikapps\NikPay\PaymentProviders\Arianpal;


use Nikapps\NikPay\PaymentProviders\PaymentConfig;
use Nikapps\NikPay\Utils\Utils;

class ArianpalConfig implements PaymentConfig
{
    /**
     * Username (= merchant id)
     *
     * @var string
     */
    protected $username = '';

    /**
     * Password
     *
     * @var string
     */
    protected $password = '';

    /**
     * Merchant id
     *
     * @var string
     */
    protected $merchantId = '';

    /**
     * Webservice url for verifying and refunding
     *
     * @var string
     */
    protected $webServiceUrl = 'http://merchant.arianpal.com/WebService.asmx?wsdl';

    /**
     * Soap options [optional]
     *
     * @var null
     */
    protected $soapOptions = null;

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
        return (new ArianpalConfig())
            ->setMerchantId(Utils::value($config, 'merchant', ''))
            ->setPassword(Utils::value($config, 'password', ''))
            ->setWebServiceUrl(Utils::value($config, 'webservice'))
            ->setSoapOptions(Utils::value($config, 'soap_options'))
            ->setRedirectUrl(Utils::value($config, 'redirect_url'));
    }

    /**
     * @return string
     */
    public function getGatewayUrl()
    {
        return $this->webServiceUrl;
    }

    /**
     * @param string $gatewayUrl
     * @return $this
     */
    public function setGatewayUrl($gatewayUrl)
    {
        return $this;
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
     * @return $this
     */
    public function setMerchantId($merchantId)
    {
        $this->merchantId = $merchantId;

        return $this;
    }

    /**
     * @return string
     */
    public function getPassword()
    {
        return $this->password;
    }

    /**
     * @param string $password
     * @return $this
     */
    public function setPassword($password)
    {
        $this->password = $password;

        return $this;
    }

    /**
     * @return string
     */
    public function getUsername()
    {
        return $this->username;
    }

    /**
     * @param string $username
     * @return $this
     */
    public function setUsername($username)
    {
        $this->username = $username;

        return $this;
    }

    /**
     * @return string
     */
    public function getWebServiceUrl()
    {
        return $this->webServiceUrl;
    }

    /**
     * @param string $webServiceUrl
     * @return $this
     */
    public function setWebServiceUrl($webServiceUrl)
    {
        if (!is_null($webServiceUrl)) {
            $this->webServiceUrl = $webServiceUrl;
        }

        return $this;
    }

    /**
     * @return array|null
     */
    public function getSoapOptions()
    {
        return $this->soapOptions;
    }

    /**
     * @param array|null $soapOptions
     * @return $this
     */
    public function setSoapOptions($soapOptions)
    {
        $this->soapOptions = $soapOptions;

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
     * @return $this
     */
    public function setRedirectUrl($redirectUrl)
    {
        $this->redirectUrl = $redirectUrl;

        return $this;
    }

    /**
     * @param string $tokenUrl
     * @return $this
     */
    public function setTokenUrl($tokenUrl)
    {
        return $this;
    }

    /**
     * @return string
     */
    public function getTokenUrl()
    {
        return $this->webServiceUrl;
    }
}