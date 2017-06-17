<?php
/**
 * Created by PhpStorm.
 * User: nghaedsharafi
 * Date: 6/16/17
 * Time: 23:18
 */

namespace Nikapps\NikPay\PaymentProviders\Arianpal;


use Nikapps\NikPay\Exceptions\DuplicateReferenceException;
use Nikapps\NikPay\Exceptions\FailedPaymentException;
use Nikapps\NikPay\Exceptions\InvalidPostDataException;
use Nikapps\NikPay\Exceptions\NotEqualAmountException;
use Nikapps\NikPay\Exceptions\NotVerifiedException;
use Nikapps\NikPay\Exceptions\SoapException;
use Nikapps\NikPay\InvoiceVerifier;
use Nikapps\NikPay\PaymentProviders\PaymentProvider;
use Nikapps\NikPay\PaymentResult;
use Nikapps\NikPay\Purchase;
use Nikapps\NikPay\Soap\SoapService;

class Arianpal implements PaymentProvider
{
    /**
     * Soap client
     *
     * @var SoapService
     */
    private $client;

    /**
     * @var ArianpalConfig
     */
    private $config;

    /**
     * @var string
     */
    private $token = '';

    /**
     * @var InvoiceVerifier
     */
    private $invoiceVerifier;

    /**
     * @var PaymentResult
     */
    protected $result;

    /**
     * Constructor for Arianpal Payment
     *
     * @param SoapService $client
     * @param ArianpalConfig $config
     */
    public function __construct(SoapService $client, ArianpalConfig $config)
    {
        $this->client = $client;
        $this->config = $config;
    }

    /**
     * Prepare a payment (i.e. fetching token/refId)
     *
     * @param Purchase $purchase
     * @return $this
     * @throws SoapException
     */
    public function prepare(Purchase $purchase)
    {
        // Create soap client for requesting token
        $client = $this->client
            ->wsdl($this->config->getTokenUrl())
            ->options($this->config->getSoapOptions())
            ->createClient();

        // Prepare parameters for soap call
        $parameters = [
            'MerchentID' => $this->config->getMerchantId(),
            'Password' => $this->config->getPassword(),
            'ResNumber' => $purchase->getInvoice(),
            'Price' => $purchase->getAmountInRial(),
            'ReturnPath' => $this->config->getRedirectUrl()
        ];

        $parameters = array_merge($parameters, $purchase->getOptions());

        try {
            $this->token = $client->call('RequestPayment', $parameters);
        } catch (\SoapFault $e) {
            throw (new SoapException())->setSoapFault($e);
        }

        return $this;
    }

    /**
     * Alias of prepare
     *
     * @see $this::prepare()
     * @param Purchase $purchase
     * @return self
     */
    public function fetchToken(Purchase $purchase)
    {
        return $this->prepare($purchase);
    }

    /**
     * Alias of prepare
     *
     * @see $this::prepare()
     * @param Purchase $purchase
     * @return self
     */
    public function token(Purchase $purchase)
    {
        return $this->prepare($purchase);
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
        return '';
    }

    /**
     * Alias of generateForm
     *
     * @see $this::generateForm()
     * @param null|string|\Closure $form Custom html form
     * @param string $token [Optional] if token is fetched manually
     * @return string
     */
    public function redirect($form = null, $token = null)
    {
        return $this->generateForm($form, $token);
    }

    /**
     * Get form data for redirecting user manually to bank gateway
     *
     * @return array
     */
    public function form()
    {
        return [];
    }

    /**
     * Set invoice verifier
     *
     * @param InvoiceVerifier $invoiceVerifier
     * @return $this
     */
    public function invoiceVerifier(InvoiceVerifier $invoiceVerifier)
    {
        $this->invoiceVerifier = $invoiceVerifier;

        return $this;
    }

    /**
     * verify payment
     *
     * @param string $reference
     * @param array $options [optional]
     * @param int $amount
     * @return int
     * @throws NotEqualAmountException
     * @throws NotVerifiedException
     * @throws SoapException
     */
    public function verify($reference, array $options = [], $amount = 0)
    {
        // Create soap client for verifying payment
        $client = $this->client
            ->wsdl($this->config->getWebServiceUrl())
            ->options($this->config->getSoapOptions())
            ->createClient();

        // Prepare parameters for soap call
        $parameters = [
            'MerchantID' => $this->config->getMerchantId(),
            'Password' => $this->config->getPassword(),
            'Price' => $amount,
            'RefNamber' => $reference
        ];

        try {
            $result = $client->call('VerifyPayment', $parameters);

            if ($result->verifyPaymentResult->ResultStatus == 'NotMatchMoney') {
                throw (new NotEqualAmountException())
                    ->setBankAmount($amount)
                    ->setReference($reference);
            } elseif ($result->verifyPaymentResult->ResultStatus == 'Success') {
                return $result->verifyPaymentResult->PayementedPrice;
            }

            throw (new NotVerifiedException)->setErrorCode(-1);
        } catch (\SoapFault $e) {
            throw (new SoapException())->setSoapFault($e);
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
     * @throws NotVerifiedException
     * @throws SoapException
     */
    public function handleCallback(array $data = [])
    {
        $data = empty($data) ? $_POST : $data;

        $this->guardAgainstInvalidPostData($data);

        $state = $data['status'];
        $reference = $data['refnumber'];
        $amount = $data['amount'];

        // Check status is ok
        if ($state !== 100) {
            throw (new FailedPaymentException())->setState($state);
        }

        // Verify reference number for double spending
        if (!$this->invoiceVerifier->verifyReference($reference)) {
            throw (new DuplicateReferenceException())->setReference($reference);
        }

        // Verify request
        $this->verify($reference, [], $amount);

        return $this->generateResult($data, $amount);
    }

    /**
     * Refund payment
     *
     * @param string $reference
     * @param array $options [optional]
     * @return boolean
     */
    public function refund($reference, array $options = [])
    {
        // sry Arianpal doesn't support auto-refund
        return false;
    }

    /**
     * Alias of refund
     *
     * @see $this::refund()
     * @param string $reference
     * @param array $options [optional]
     * @return boolean
     */
    public function reverse($reference, array $options = [])
    {
        $this->refund($reference, $options);
    }

    /**
     * Alias of refund
     *
     * @see $this::refund()
     * @param string $reference
     * @param array $options [optional]
     * @return boolean
     */
    public function reverseTransaction($reference, array $options = [])
    {
        $this->refund($reference, $options);
    }

    /**
     * Alias of refund
     *
     * @see $this::refund()
     * @param string $reference
     * @param array $options [optional]
     * @return boolean
     */
    public function recur($reference, array $options = [])
    {
        $this->refund($reference, $options);
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
     * Alias of result
     *
     * @see $this::result()
     * @return mixed
     */
    public function getResult()
    {
        return $this->result();
    }

    /**
     * Guard against invalid post data
     *
     * @param array $data
     * @throws InvalidPostDataException
     * @return bool
     */
    protected function guardAgainstInvalidPostData(array $data)
    {
        $dataAvailable = isset(
            $data['amount'],
            $data['status'],
            $data['refnumber'],
            $data['resnumber']
        );

        if ($dataAvailable === false) {
            throw new InvalidPostDataException();
        }
    }

    /**
     * Generate payment result object
     *
     * @param array $data
     * @param integer $amount
     * @return $this
     */
    protected function generateResult(array $data, $amount)
    {
        $state = $data['status'];
        $reference = $data['refnum'];
        $invoice = $data['resnum'];
        $merchant = $this->config->getMerchantId();

        $this->result = new PaymentResult(compact(
            'state',
            'merchant',
            'reference',
            'invoice',
            'amount'
        ));

        return $this;
    }

}