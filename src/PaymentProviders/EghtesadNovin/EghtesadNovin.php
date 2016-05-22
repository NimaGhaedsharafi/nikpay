<?php
namespace Nikapps\NikPay\PaymentProviders\EghtesadNovin;


use Nikapps\NikPay\Exceptions\DuplicateReferenceException;
use Nikapps\NikPay\Exceptions\FailedPaymentException;
use Nikapps\NikPay\Exceptions\InvalidPostDataException;
use Nikapps\NikPay\Exceptions\NotEqualAmountException;
use Nikapps\NikPay\Exceptions\NotVerifiedException;
use Nikapps\NikPay\Exceptions\SoapException;
use Nikapps\NikPay\PaymentProviders\AbstractPaymentProvider;
use Nikapps\NikPay\PaymentResult;
use Nikapps\NikPay\Purchase;
use Nikapps\NikPay\Soap\SoapService;
use SoapFault;

class EghtesadNovin extends AbstractPaymentProvider
{

    /**
     * Soap client
     *
     * @var SoapService
     */
    private $client;

    /**
     * @var EghtesadNovinConfig
     */
    private $config;

    /**
     * @var PaymentResult
     */
    protected $result;

    /**
     * @var Purchase
     */
    private $purchase;

    /**
     * @var bool
     */
    protected $connected = false;

    /**
     * Constructor for Saman Payment
     *
     * @param SoapService $client
     * @param EghtesadNovinConfig $config
     */
    public function __construct(SoapService $client, EghtesadNovinConfig $config)
    {
        $this->client = $client;
        $this->config = $config;
    }

    /**
     * Prepare a payment (i.e. fetching token/refId)
     *
     * @param Purchase $purchase
     * @return self
     */
    public function prepare(Purchase $purchase)
    {
        $this->purchase = $purchase;
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

        return strtr(
            $form,
            [
                '{amount}' => $this->purchase->getAmountInRial(),
                '{invoice}' => $this->purchase->getInvoice(),
                '{merchant}' => $this->config->getMerchantId(),
                '{language}' => $this->config->getLanguage(),
                '{redirect}' => $this->config->getRedirectUrl(),
                '{gateway}' => $this->config->getGatewayUrl()
            ]
        );
    }

    /**
     * Get form data for redirecting user manually to bank gateway
     *
     * @return array
     */
    public function form()
    {
        return [
            'amount' => $this->purchase->getAmountInRial(),
            'invoice' => $this->purchase->getInvoice(),
            'merchant' => $this->config->getMerchantId(),
            'redirect' => $this->config->getRedirectUrl(),
            'language' => $this->config->getLanguage()
        ];
    }

    public function login()
    {
        // When someone who don't know anything about designing API, works at a bank as a developer :|

        $client = $this->connect();

        $parameters = [
            'loginRequest' => [
                'password' => $this->config->getPassword(),
                'username' => $this->config->getUsername(),
            ]
        ];

        try {
            return $client->call('login', $parameters)->return;
        } catch (SoapFault $e) {
            throw (new SoapException())->setSoapFault($e);
        }

    }

    public function logout($login)
    {
        // Of course, we also have logout! He/She really don't know anything about designing API!

        $client = $this->connect();

        $parameters = [
            'context' => $this->context($login)
        ];

        try {
            $client->call('logout', $parameters);
        } catch (SoapFault $e) {
            throw (new SoapException())->setSoapFault($e);
        }

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
        $client = $this->connect();
        $login = $this->login();

        // Prepare parameters for soap call
        $parameters = [
            'context' => $this->context($login),
            'verifyRequest' => [
                'refNumList' => [$reference]
            ]
        ];

        try {
            $result = $client->call('verifyTransaction', $parameters);

            $amount = isset($result->return->verifyResponseResults->amount)
                ? $result->return->verifyResponseResults->amount
                : -1;

            $error = isset($result->return->verifyResponseResults->verificationError)
                ? $result->return->verifyResponseResults->verificationError
                : null;

            if ($result < 0 || !is_null($error)) {
                throw (new NotVerifiedException)->setErrorCode($error);
            }

            return $amount;
        } catch (SoapFault $e) {
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
     * Refund payment
     *
     * @param string $reference
     * @param array $options
     * @return bool
     * @throws SoapException
     */
    public function refund($reference, array $options = [])
    {
        $client = $this->connect();
        $login = $this->login();

        $amount = $options['amount'];
        $invoice = $options['invoice'];

        // Prepare parameters for soap call
        $parameters = [
            'context' => $this->context($login),
            'ReverseRequest' => [
                'mainTransactionRefNum' => $reference,
                'mainTransactionResNum' => $invoice,
                'amount' => $amount
            ]
        ];

        try {
            return $client->call('verifyTransaction', $parameters)->return;
        } catch (SoapFault $e) {
            throw (new SoapException())->setSoapFault($e);
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
            $data['redirectURL'],
            $data['MID'],
            $data['ResNum'],
            $data['RefNum'],
            $data['State'],
            $data['Language'],
            $data['CardPanHash']
        );

        if (!$ok) {
            throw new InvalidPostDataException;
        }
    }

    protected function generateResult(array $data, $amount)
    {
        $state = $data['State'];
        $reference = $data['RefNum'];
        $invoice = $data['ResNum'];
        $merchant = isset($data['MID']) ? $data['MID'] : $this->config->getMerchantId();

        $this->result = new PaymentResult(compact(
            'state',
            'merchant',
            'reference',
            'invoice',
            'amount'
        ));

        return $this;
    }

    /**
     * Create a SOAP client
     *
     * @return SoapService
     */
    public function connect()
    {
        if (!$this->connected) {
            $this->client
                ->wsdl($this->config->getWebServiceUrl())
                ->options($this->config->getSoapOptions())
                ->createClient();

            $this->connected = true;
        }

        return $this->client;
    }

    protected function context($login)
    {
        return [
            'data' => [
                'entry' => [
                    'key' => 'SESSION_ID', // Really?!
                    'value' => $login
                ]
            ]
        ];
    }
}