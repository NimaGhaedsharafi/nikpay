<?php
namespace Nikapps\NikPay\PaymentProviders\Saman;

use Nikapps\NikPay\Exceptions\DuplicateReferenceException;
use Nikapps\NikPay\Exceptions\FailedPaymentException;
use Nikapps\NikPay\Exceptions\NotEqualAmountException;
use Nikapps\NikPay\Exceptions\InvalidPostDataException;
use Nikapps\NikPay\Exceptions\NotVerifiedException;
use Nikapps\NikPay\Exceptions\SoapException;
use Nikapps\NikPay\InvoiceVerifier;
use Nikapps\NikPay\PaymentProviders\PaymentProvider;
use Nikapps\NikPay\PaymentResult;
use Nikapps\NikPay\Purchase;
use Nikapps\NikPay\Soap\SoapService;

class Saman implements PaymentProvider
{

    /**
     * Soap client
     *
     * @var SoapService
     */
    private $client;

    /**
     * @var SamanConfig
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
     * Constructor for Saman Payment
     *
     * @param SoapService $client
     * @param SamanConfig $config
     */
    public function __construct(SoapService $client, SamanConfig $config)
    {
        $this->client = $client;
        $this->config = $config;
    }

    /**
     * Prepare a payment (i.e. fetching token/refId)
     *
     * @param Purchase $purchase
     * @throws SoapException
     * @return self
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
            'TermID'      => $this->config->getMerchantId(),
            'ResNUM'      => $purchase->getInvoice(),
            'TotalAmount' => $purchase->getAmountInRial()
        ];

        $parameters = array_merge($parameters, $purchase->getOptions());

        try {
            $this->token = $client->call('RequestToken', $parameters);
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
        if ($form instanceof \Closure) {
            return $form([
                'token'    => $token,
                'redirect' => $this->config->getRedirectUrl(),
                'gateway'  => $this->config->getGatewayUrl()
            ]);
        }

        if (is_null($form)) {
            $form = file_get_contents(__DIR__ . DIRECTORY_SEPARATOR . 'form.stub');
        }

        $token = is_null($token) ? $this->token : $token;

        return strtr(
            $form,
            [
                '{token}'    => $token,
                '{redirect}' => $this->config->getRedirectUrl(),
                '{gateway}'  => $this->config->getGatewayUrl()
            ]
        );
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
        return [
            'token'    => $this->token,
            'redirect' => $this->config->getRedirectUrl(),
            'gateway'  => $this->config->getGatewayUrl()
        ];
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
     *  This method only send verify request to bank
     * and check result code is not negative!
     *  In order to check status code or amount value you should verify it manually
     * or you can use handleCallback() instead and provide InvoiceVerifier
     *
     * @param string $reference
     * @param array $options [optional]
     * @throws NotVerifiedException
     * @throws SoapException
     * @return integer
     */
    public function verify($reference, array $options = [])
    {
        // Create soap client for verifying payment
        $client = $this->client
            ->wsdl($this->config->getWebServiceUrl())
            ->options($this->config->getSoapOptions())
            ->createClient();

        // Prepare parameters for soap call
        $parameters = [$reference, $this->config->getMerchantId()];

        try {
            $result = $client->call('verifyTransaction', $parameters);

            if ($result < 0) {
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
     *
     * @throws DuplicateReferenceException
     * @throws FailedPaymentException
     * @throws NotEqualAmountException
     * @throws InvalidPostDataException
     * @throws NotVerifiedException
     * @throws SoapException
     *
     * @return self
     */
    public function handleCallback(array $data = [])
    {
        $data = empty($data) ? $_POST : $data;

        $this->guardAgainstInvalidPostData($data);

        $state = $data['State'];
        $reference = $data['RefNum'];
        $invoice = $data['ResNum'];

        // Check status is ok
        if ($state !== 'OK') {
            throw (new FailedPaymentException)->setState($state);
        }

        // Verify reference number for double spending
        if (!$this->invoiceVerifier->verifyReference($reference)) {
            throw (new DuplicateReferenceException)->setReference($reference);
        }

        // Verify request
        $amount = $this->verify($reference);

        // Verify amount is the same
        if (!$this->invoiceVerifier->verifyAmount($invoice, $amount)) {
            throw (new NotEqualAmountException)
                ->setBankAmount($amount)
                ->setInvoice($invoice)
                ->setReference($reference);
        }

        return $this->generateResult($data, $amount);
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
            $data['ResNum'],
            $data['RefNum'],
            $data['State']
        );

        if (!$ok) {
            throw new InvalidPostDataException;
        }
    }

    /**
     * Refund payment
     *
     * @param string $reference
     * @param array $options [optional]
     * @throws SoapException
     * @return boolean
     */
    public function refund($reference, array $options = [])
    {
        // Create soap client for refunding payment
        $client = $this->client
            ->wsdl($this->config->getWebServiceUrl())
            ->options($this->config->getSoapOptions())
            ->createClient();

        // Prepare parameters for soap call
        $parameters = [
            $reference,
            $this->config->getMerchantId(),
            $this->config->getUsername(),
            $this->config->getPassword()
        ];

        try {
            $result = $client->call('reverseTransaction', $parameters);

            return $result > 0 ? true : false;
        } catch (\SoapFault $e) {
            throw (new SoapException)->setSoapFault($e);
        }
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
        return $this->refund($reference, $options);
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
        return $this->refund($reference, $options);
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
        return $this->refund($reference, $options);
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
        $state = $data['State'];
        $reference = $data['RefNum'];
        $invoice = $data['ResNum'];
        $trace = isset($data['TRACENO']) ? $data['TRACENO'] : 'no-trace';
        $merchant = $this->config->getMerchantId();

        $this->result = new PaymentResult(compact(
            'state',
            'merchant',
            'reference',
            'invoice',
            'trace',
            'amount'
        ));

        return $this;
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
}
