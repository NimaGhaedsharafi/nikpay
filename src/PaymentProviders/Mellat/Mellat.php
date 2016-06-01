<?php
namespace Nikapps\NikPay\PaymentProviders\Mellat;

use Nikapps\NikPay\Exceptions\DuplicateReferenceException;
use Nikapps\NikPay\Exceptions\FailedPaymentException;
use Nikapps\NikPay\Exceptions\InvalidPostDataException;
use Nikapps\NikPay\Exceptions\NotEqualAmountException;
use Nikapps\NikPay\Exceptions\NotFoundInvoiceException;
use Nikapps\NikPay\Exceptions\NotVerifiedException;
use Nikapps\NikPay\Exceptions\RequestTokenFailedException;
use Nikapps\NikPay\Exceptions\SoapException;
use Nikapps\NikPay\InvoiceVerifier;
use Nikapps\NikPay\PaymentProviders\AbstractPaymentProvider;
use Nikapps\NikPay\PaymentResult;
use Nikapps\NikPay\Purchase;
use Nikapps\NikPay\Soap\SoapService;

class Mellat extends AbstractPaymentProvider
{

    /**
     * Soap client
     *
     * @var SoapService
     */
    private $client;

    /**
     * @var MellatConfig
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
     * @var MellatInvoiceFinder
     */
    private $invoiceFinder;

    /**
     * @var PaymentResult
     */
    protected $result;

    /**
     * Constructor for Saman Payment
     *
     * @param SoapService $client
     * @param MellatConfig $config
     */
    public function __construct(SoapService $client, MellatConfig $config)
    {
        $this->client = $client;
        $this->config = $config;
    }

    /**
     * @param MellatInvoiceFinder $invoiceFinder
     * @return $this
     */
    public function invoiceFinder(MellatInvoiceFinder $invoiceFinder)
    {
        $this->invoiceFinder = $invoiceFinder;

        return $this;
    }

    /**
     * Prepare a payment (i.e. fetching token/refId)
     *
     * @param Purchase $purchase
     * @return static
     * @throws RequestTokenFailedException
     * @throws SoapException
     */
    public function prepare(Purchase $purchase)
    {
        // Create soap client for requesting token
        $client = $this->client
            ->wsdl($this->config->getWebServiceUrl())
            ->options($this->config->getSoapOptions())
            ->createClient();

        $parameters = $this->createRequestTokenParameters($purchase);

        try {
            $result = $client->call('bpPayRequest', $parameters);

            list($error, $token) = explode(',', $result);

            if ($error !== '0') {
                throw (new RequestTokenFailedException)
                    ->setErrorCode($error);
            }

            // Mellat Bank named it `refId`, but here we use `token` instead.
            $this->token = $token;

        } catch (\SoapFault $e) {
            throw (new SoapException())->setSoapFault($e);
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
        // TODO: Implement generateForm() method.
    }

    /**
     * Get form data for redirecting user manually to bank gateway
     *
     * @return array
     */
    public function form()
    {
        // TODO: Implement form() method.
    }

    /**
     * verify payment
     *
     * @param string $reference
     * @param array $options [optional]
     * @return int
     * @throws NotVerifiedException
     * @throws SoapException
     */
    public function verify($reference, array $options = [])
    {
        // Create soap client for verifying payment
        $client = $this->client
            ->wsdl($this->config->getWebServiceUrl())
            ->options($this->config->getSoapOptions())
            ->createClient();

        // Prepare parameters for soap call
        $parameters = [
            'terminalId' => $this->config->getMerchantId(),
            'userName' => $this->config->getUsername(),
            'userPassword' => $this->config->getPassword(),
            'orderId' => $options['ourInvoice'],
            'saleOrderId' => $options['invoice'],
            'saleReferenceId' => $reference
        ];

        try {
            $result = $client->call('bpVerifyRequest', $parameters);

            if ($result !== '0') {
                throw (new NotVerifiedException)->setErrorCode($result);
            }

            return $result;
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
     * @throws NotEqualAmountException
     */
    public function handleCallback(array $data = [])
    {
        $data = empty($data) ? $_POST : $data;

        $this->guardAgainstInvalidPostData($data);

        $token = $data['RefId'];
        $state = intval($data['ResCode']);
        $invoice = $data['saleOrderId'];
        $reference = $data['SaleReferenceId'];

        // Check status is ok
        if ($state > 0) {
            throw (new FailedPaymentException)->setState($state);
        }

        // Verify reference number for double spending
        if (!$this->invoiceVerifier->verifyReference($reference)) {
            throw (new DuplicateReferenceException)->setReference($reference);
        }

        $ourInvoice = $this->findOurInvoice($token, $invoice);

        // Verify request
        $this->verify($reference, compact('token', 'invoice', 'ourInvoice'));

        // Mellat Bank does not return amount
        return $this->generateResult($data, null);
    }

    /**
     * Refund payment
     *
     * @param string $reference
     * @param array $options [optional]
     * @return bool
     * @throws SoapException
     */
    public function refund($reference, array $options = [])
    {
        // Create soap client for refunding payment
        $client = $this->client
            ->wsdl($this->config->getWebServiceUrl())
            ->options($this->config->getSoapOptions())
            ->createClient();

        $ourInvoice = isset($options['ourInvoice'])
            ? $options['ourInvoice']
            : $options['invoice'];

        // Prepare parameters for soap call
        $parameters = [
            'terminalId' => $this->config->getMerchantId(),
            'userName' => $this->config->getUsername(),
            'userPassword' => $this->config->getPassword(),
            'orderId' => $ourInvoice,
            'saleOrderId' => $options['invoice'],
            'saleReferenceId' => $reference
        ];

        try {
            $result = $client->call('bpSettleRequest', $parameters);
            return $this->isRefundSuccessful($result);

        } catch (\SoapFault $e) {
            throw (new SoapException)->setSoapFault($e);
        }
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
     * Guard against invalid post data
     *
     * @param array $data
     * @throws InvalidPostDataException
     * @return bool
     */
    protected function guardAgainstInvalidPostData(array $data)
    {
        $ok = isset(
            $data['RefId'],
            $data['ResCode'],
            $data['SaleOrderId'],
            $data['SaleReferenceId']
        );

        if (!$ok) {
            throw new InvalidPostDataException;
        }
    }

    /**
     * Generate payment result object
     *
     * @param array $data
     * @param integer $amount
     * @return $this
     */
    protected function generateResult(array $data, $amount = null)
    {
        $state = $data['ResCode'];
        $reference = $data['SaleReferenceId'];
        $invoice = $data['SaleOrderId'];
        $merchant = $this->config->getMerchantId();

        $this->result = new PaymentResult(compact(
            'state',
            'merchant',
            'reference',
            'invoice'
        ));

        return $this;
    }

    /**
     * @param Purchase $purchase
     * @return array
     */
    protected function createRequestTokenParameters(Purchase $purchase)
    {
        return [
            'terminalId' => $this->config->getMerchantId(),
            'userName' => $this->config->getUsername(),
            'userPassword' => $this->config->getPassword(),
            'orderId' => $purchase->getInvoice(),
            'amount' => $purchase->getAmountInRial(),
            'localDate' => $purchase->getOption('date', date('Ymd')),
            'localTime' => $purchase->getOption('time', date('His')),
            'additionalData' => $purchase->getOption('custom', ''),
            'callBackUrl' => $this->config->getRedirectUrl(),
            'payerId' => $purchase->getOption('payerId', 0)
        ];
    }

    /**
     * @param $token
     * @param $invoice
     * @return int|null|string
     * @throws NotFoundInvoiceException
     */
    public function findOurInvoice($token, $invoice)
    {
        if (is_null($this->invoiceFinder)) {
            // simplicity vs security!
            return $invoice;
        }

        $ourInvoice = $this->invoiceFinder->find($token);

        if (is_null($ourInvoice)) {
            throw (new NotFoundInvoiceException)
                ->setInvoice($invoice)
                ->setToken($token);
        }

        return $ourInvoice;
    }

    protected function isRefundSuccessful($result)
    {
        return $result == '0' || $result == '45';
    }
}