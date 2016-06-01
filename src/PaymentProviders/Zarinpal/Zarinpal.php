<?php
namespace Nikapps\NikPay\PaymentProviders\Zarinpal;

use GuzzleHttp\Exception\RequestException;
use Nikapps\NikPay\Exceptions\DuplicateReferenceException;
use Nikapps\NikPay\Exceptions\FailedPaymentException;
use Nikapps\NikPay\Exceptions\GuzzleException;
use Nikapps\NikPay\Exceptions\InvalidPostDataException;
use Nikapps\NikPay\Exceptions\NotFoundAmountException;
use Nikapps\NikPay\Exceptions\NotImplementedException;
use Nikapps\NikPay\Exceptions\NotVerifiedException;
use Nikapps\NikPay\Exceptions\RequestTokenFailedException;
use Nikapps\NikPay\PaymentProviders\AbstractPaymentProvider;
use Nikapps\NikPay\PaymentResult;
use Nikapps\NikPay\Purchase;
use Nikapps\NikPay\Restful\RestClient;

class Zarinpal extends AbstractPaymentProvider
{
    /**
     * @var Purchase
     */
    protected $purchase;

    /**
     * Token/Authority
     *
     * @var string
     */
    protected $token;

    /**
     * @var PaymentResult
     */
    protected $result;

    /**
     * @var RestClient
     */
    private $client;
    /**
     * @var ZarinpalConfig
     */
    private $config;

    /**
     * @var ZarinpalAmountFinder
     */
    private $amountFinder;

    /**
     * Zarinpal constructor.
     * @param RestClient $client
     * @param ZarinpalConfig $config
     */
    public function __construct(RestClient $client, ZarinpalConfig $config)
    {
        $this->client = $client;
        $this->config = $config;
    }

    /**
     * @param ZarinpalAmountFinder $amountFinder
     * @return Zarinpal
     */
    public function setAmountFinder($amountFinder)
    {
        $this->amountFinder = $amountFinder;

        return $this;
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

            $status = $response['Status'];
            $token = $response['Authority'];

            if ($status != 100) {
                throw (new RequestTokenFailedException)->setErrorCode($status);
            }

            $this->token = $token;

        } catch (RequestException $e) {
            throw (new GuzzleException)->setRequestException($e);
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
            'authority' => $this->token,
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
     * @return int|string
     * @throws GuzzleException
     * @throws NotVerifiedException
     */
    public function verify($reference, array $options = [])
    {
        $parameters = [
            'MerchantID' => $this->config->getMerchantId(),
            'Authority' => $reference,
            'Amount' => $options['amount']
        ];

        try {
            $response = $this->client->post(
                $this->config->getVerifyEndpoint(),
                array_merge([
                    'json' => $parameters
                ], $this->config->getClientOptions())
            );

            $status = isset($response['Status']) ? $response['Status'] : -99;
            $traceNumber = isset($response['RefID']) ? $response['RefID'] : null;

            if ($status != 100) {
                throw (new NotVerifiedException)
                    ->setErrorCode($status);
            }

            return $traceNumber;

        } catch (RequestException $e) {
            throw (new GuzzleException)->setRequestException($e);
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
     * @throws NotFoundAmountException
     */
    public function handleCallback(array $data = [])
    {
        $data = empty($data) ? $_POST : $data;

        $this->guardAgainstInvalidPostData($data);

        $status = $data['Status'];
        $reference = $data['Authority'];

        // Check status is ok
        if ($status != 'OK') {
            throw (new FailedPaymentException)->setState($status);
        }

        // Verify reference number for double spending
        if (!$this->invoiceVerifier->verifyReference($reference)) {
            throw (new DuplicateReferenceException)->setReference($reference);
        }

        $amount = $this->amountFinder->find($reference, [
            'authority' => $reference,
        ]);

        if ($amount === false || is_null($amount)) {
            throw (new NotFoundAmountException)
                ->setReference($reference);
        }

        $traceNumber = $this->verify($reference, compact('amount'));

        return $this->generateResult($data, $traceNumber, $amount);

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
        // Zarinpal does not provide refund endpoint
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


    protected function createRequestTokenParameters()
    {
        $parameters = [
            'MerchantID' => $this->config->getMerchantId(),
            'CallbackURL' => $this->config->getRedirectUrl(),
            'Amount' => $this->purchase->getAmountInRial(),
            'Description' => $this->purchase->getOption(
                'note', $this->purchase->getOption('description', '')
            ),
        ];

        if ($this->purchase->hasOption('email')) {
            $parameters['Email'] = $this->purchase->getOption('email');
        }

        if ($this->purchase->hasOption('mobile')) {
            $parameters['Mobile'] = $this->purchase->getOption('mobile');
        }

        return $parameters;

    }

    protected function guardAgainstInvalidPostData(array $data)
    {
        $ok = isset(
            $data['Authority'],
            $data['Status']
        );

        if (!$ok) {
            throw new InvalidPostDataException;
        }
    }

    /**
     * @param array $data
     * @param string|int $trace
     * @param int $amount
     * @return $this
     */
    protected function generateResult(array $data, $trace, $amount)
    {
        $state = $data['Status'];
        $reference = $data['Authority'];
        $merchant = $this->config->getMerchantId();

        $this->result = new PaymentResult(compact(
            'state',
            'merchant',
            'reference',
            'trace',
            'amount'
        ));

        return $this;
    }
}