<?php
namespace Nikapps\NikPay\PaymentProviders\FaraGate;

use GuzzleHttp\Exception\ClientException;
use Nikapps\NikPay\Exceptions\DuplicateReferenceException;
use Nikapps\NikPay\Exceptions\FailedPaymentException;
use Nikapps\NikPay\Exceptions\GuzzleException;
use Nikapps\NikPay\Exceptions\InvalidPostDataException;
use Nikapps\NikPay\Exceptions\NotImplementedException;
use Nikapps\NikPay\Exceptions\NotVerifiedException;
use Nikapps\NikPay\Exceptions\RequestTokenFailedException;
use Nikapps\NikPay\PaymentProviders\AbstractPaymentProvider;
use Nikapps\NikPay\PaymentResult;
use Nikapps\NikPay\Purchase;
use Nikapps\NikPay\Restful\RestClient;

class FaraGate extends AbstractPaymentProvider
{
    /**
     * @var RestClient
     */
    private $client;
    /**
     * @var FaraGateConfig
     */
    private $config;
    /**
     * @var string
     */
    protected $token;

    /**
     * @var Purchase
     */
    protected $purchase;

    /**
     * @var PaymentResult
     */
    protected $result;

    /**
     * FaraGate constructor.
     * @param RestClient $client
     * @param FaraGateConfig $config
     */
    public function __construct(RestClient $client, FaraGateConfig $config)
    {
        $this->client = $client;
        $this->config = $config;
    }

    /**
     * Prepare a payment (i.e. fetching token/refId)
     *
     * @param Purchase $purchase
     * @return static
     * @throws GuzzleException
     * @throws RequestTokenFailedException
     */
    public function prepare(Purchase $purchase)
    {
        $this->purchase = $purchase;

        try {
            $response = $this->client->post(
                $this->config->getTokenEndpoint(),
                array_merge([
                    'json' => $this->createRequestTokenParameters()
                ], $this->config->getClientOptions())
            );

            $status = isset($response['Status']) ? $response['Status'] : -1;
            $token = isset($response['Token']) ? $response['Token'] : null;

            if ($status != 1 || is_null($token)) {
                throw (new RequestTokenFailedException)
                    ->setErrorCode($status);
            }

            $this->token = $token;

        } catch (ClientException $e) {
            throw (new GuzzleException)
                ->setClientException($e);
        }

        return $this;
    }

    /**
     * Generate html form for redirecting user to bank gateway
     *
     * @param null|string|\Closure $form Custom html form
     * @param string $token [Optional] if token is fetched manually
     * @return string
     */
    public function generateForm($form = null, $token = null)
    {
        if ($form instanceof \Closure) {
            return $form($this->form());
        }

        if (is_null($form)) {
            $form = file_get_contents(__DIR__ . DIRECTORY_SEPARATOR . 'form.stub');
        }

        return strtr($form, [
            '{gateway}' => $this->config->createGatewayUrl($this->token)
        ]);
    }

    /**
     * Get form data for redirecting user manually to bank gateway
     *
     * @return array
     */
    public function form()
    {
        return [
            'token' => $this->token,
            'gateway' => $this->config->createGatewayUrl($this->token)
        ];
    }

    public function redirect($redirector = null, $token = null)
    {
        $token = !is_null($token) ?: $this->token;

        $url = $this->config->createGatewayUrl($token);

        if ($redirector instanceof \Closure) {
            return $redirector($url, $token);
        }

        // redirect user to gateway
        header("Location: {$url}");
        exit();
    }

    /**
     * verify payment
     *
     * @param string $reference
     * @param array $options [optional]
     * @return void
     * @throws GuzzleException
     * @throws NotVerifiedException
     */
    public function verify($reference, array $options = [])
    {
        $parameters = [
            'MerchantCode' => $this->config->getMerchantId(),
            'Token' => $reference,
            'SandBox' => $this->config->isSandboxEnabled()
        ];

        try {
            $response = $this->client->post(
                $this->config->getVerifyEndpoint(),
                array_merge([
                    'json' => $parameters
                ], $this->config->getClientOptions())
            );

            $status = isset($response['Status']) ? $response['Status'] : -1;

            if ($status != 1) {
                throw (new NotVerifiedException)->setErrorCode($status);
            }
        } catch (ClientException $e) {
            throw (new GuzzleException)
                ->setClientException($e);
        }

    }

    /**
     * handle bank callback & verify it automatically
     *
     * @param array $data Post data, if empty it uses $_POST
     * @return static
     * @throws DuplicateReferenceException
     * @throws FailedPaymentException
     * @throws InvalidPostDataException
     */
    public function handleCallback(array $data = [])
    {
        $data = empty($data) ? $_POST : $data;

        $this->guardAgainstInvalidPostData($data);

        $status = $data['Status'];
        $reference = $data['Token'];

        // Check status is ok
        if ($status != 1) {
            throw (new FailedPaymentException)->setState($status);
        }

        // Verify reference number for double spending
        if (!$this->invoiceVerifier->verifyReference($reference)) {
            throw (new DuplicateReferenceException)->setReference($reference);
        }

        $this->verify($reference);

        return $this->generateResult($data);

    }

    /**
     * Refund payment
     *
     * @param string $reference
     * @param array $options [optional]
     * @return bool
     * @throws NotImplementedException
     */
    public function refund($reference, array $options = [])
    {
        // FaraGate does not provide refund endpoint
        throw new NotImplementedException;
    }

    /**
     * Get result of payment
     *
     * @return PaymentResult
     */
    public function result()
    {
        return $this->result;
    }

    /**
     * @return array
     */
    protected function createRequestTokenParameters()
    {
        $parameters = [
            'SandBox' => $this->config->getMerchantId(),
            'MerchantCode' => $this->config->getMerchantId(),
            'PriceValue' => $this->purchase->getAmountInRial(),
            'ReturnUrl' => $this->config->getRedirectUrl(),
            'PluginName' => $this->config->getPluginName(),
            'InvoiceNumber' => $this->purchase->getInvoice()
        ];

        if ($this->purchase->hasOption('name')) {
            $parameters['PaymenterName'] = $this->purchase->getOption('name');
        }

        if ($this->purchase->hasOption('email')) {
            $parameters['PaymenterEmail'] = $this->purchase->getOption('email');
        }

        if ($this->purchase->hasOption('mobile')) {
            $parameters['PaymenterMobile'] = $this->purchase->getOption('mobile');
        }

        if ($this->purchase->hasOption('note')) {
            $parameters['PaymentNote'] = $this->purchase->getOption('note');
        }

        if ($this->purchase->hasOption('queries')) {
            $parameters['CustomQuery'] = $this->purchase->getOption('queries');
        }

        if ($this->purchase->hasOption('posts')) {
            $parameters['CustomPost'] = $this->purchase->getOption('posts');
        }

        if ($this->purchase->hasOption('accounts')) {
            $parameters['ExtraAccountNumbers'] = $this->purchase->getOption('accounts');
        }

        if ($this->purchase->hasOption('bank')) {
            $parameters['Bank'] = $this->purchase->getOption('bank');
        }

        return $parameters;
    }

    /**
     * @param array $data
     * @throws InvalidPostDataException
     */
    protected function guardAgainstInvalidPostData(array $data)
    {
        $ok = isset(
            $data['InvoiceNumber'],
            $data['Token'],
            $data['Status']
        );

        if (!$ok) {
            throw new InvalidPostDataException;
        }
    }

    protected function generateResult($data)
    {
        $state = $data['Status'];
        $reference = $data['Token'];
        $invoice = $data['InvoiceNumber'];
        $merchant = $this->config->getMerchantId();

        $this->result = new PaymentResult(compact(
            'state',
            'merchant',
            'reference',
            'invoice'
        ));

        return $this;
    }
}