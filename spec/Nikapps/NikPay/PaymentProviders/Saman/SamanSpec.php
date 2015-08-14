<?php

namespace spec\Nikapps\NikPay\PaymentProviders\Saman;

use Nikapps\NikPay\Exceptions\DuplicateReferenceException;
use Nikapps\NikPay\Exceptions\FailedPaymentException;
use Nikapps\NikPay\Exceptions\NotEqualAmountException;
use Nikapps\NikPay\Exceptions\NotVerifiedException;
use Nikapps\NikPay\InvoiceVerifier;
use Nikapps\NikPay\PaymentProviders\Saman\SamanConfig;
use Nikapps\NikPay\Purchase;
use Nikapps\NikPay\Soap\SoapService;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;

class SamanSpec extends ObjectBehavior
{

    function let(SoapService $soap, SamanConfig $samanConfig)
    {

        $this->beConstructedWith($soap, $samanConfig);
    }

    function it_is_initializable()
    {
        $this->shouldHaveType('Nikapps\NikPay\PaymentProviders\Saman\Saman');
    }

    function it_should_fetch_token_for_given_purchase_and_return_form_data(Purchase $purchase, $samanConfig, $soap)
    {
        $config = $this->getDefaultConfig();

        $this->mockConfig($samanConfig);
        $this->mockSoapConnection($soap, $config['token_wsdl'], $config['soap_options']);

        $purchase->getAmountInRial()->willReturn(1000);
        $purchase->getInvoice()->willReturn('inv-123-123-123');
        $purchase->getOptions()->willReturn(['Wage' => 800]);

        $params = [
            'TermID'      => $config['merchant'],
            'ResNUM'      => 'inv-123-123-123',
            'TotalAmount' => 1000,
            'Wage'        => 800
        ];

        $soap->call('RequestToken', $params)->willReturn('token-123456');

        $this->prepare($purchase);
        $this->form()->shouldHaveKeyWithValue('token', 'token-123456');
    }

    function it_should_generate_custom_html_form($samanConfig)
    {
        $this->mockConfig($samanConfig);
        $config = $this->getDefaultConfig();

        $form = '<span>{token}, {gateway}, {redirect}</span>';
        $output = "<span>token-123-123, {$config['gateway']}, {$config['redirect_url']}</span>";

        $this->generateForm($form, 'token-123-123')->shouldBe($output);

    }

    function it_should_generate_default_html_form($samanConfig)
    {

        $this->mockConfig($samanConfig);
        $config = $this->getDefaultConfig();

        $output = '<form action="' . $config['gateway'] . '" method="post" id="payment">
    <input type="hidden" name="Token" value="token-123-123"/>
    <input type="hidden" name="RedirectURL" value="' . $config['redirect_url'] . '"/>
</form>

<script>
    document.getElementById("payment").submit();
</script>';

        $this->generateForm(null, 'token-123-123')->shouldBe($output);
    }

    function it_should_verify_payment_and_return_amount($soap, $samanConfig)
    {
        $config = $this->getDefaultConfig();

        $this->mockConfig($samanConfig);
        $this->mockSoapConnection($soap, $config['wsdl'], $config['soap_options']);

        $params = [
            'MerchantID' => $config['merchant'],
            'RefNum'     => 'ref-123-123'
        ];

        $soap->call('verifyTransaction', $params)->willReturn(1000);

        $this->verify('ref-123-123')->shouldBe(1000);
    }

    function it_should_throw_an_exception_when_verifying_is_failed($soap, $samanConfig)
    {
        $config = $this->getDefaultConfig();

        $this->mockConfig($samanConfig);
        $this->mockSoapConnection($soap, $config['wsdl'], $config['soap_options']);

        $params = [
            'MerchantID' => $config['merchant'],
            'RefNum'     => 'ref-123-123'
        ];

        $soap->call('verifyTransaction', $params)->willReturn(-3);

        $this->shouldThrow(
            new NotVerifiedException('Payment is not verified', -3)
        )->duringVerify('ref-123-123');
    }

    function it_should_throw_an_exception_when_creating_soap_client_is_failed($soap, $samanConfig)
    {

        $this->mockConfig($samanConfig, ['wsdl' => 'bad_url', 'soap_options' => null]);

        $soap->wsdl('bad_url')->willReturn($soap);
        $soap->options(null)->willReturn($soap);

        $soap->createClient()->willThrow('\Nikapps\NikPay\Exceptions\SoapException');

        $this->shouldThrow('\Nikapps\NikPay\Exceptions\SoapException')
            ->duringVerify('ref-123-123');
    }

    function it_should_handle_callback_and_generate_result($soap, $samanConfig, InvoiceVerifier $invoiceVerifier)
    {
        $config = $this->getDefaultConfig();

        $this->mockConfig($samanConfig);
        $this->mockSoapConnection($soap, $config['wsdl'], $config['soap_options']);

        $post = [
            'State'   => 'OK',
            'RefNum'  => 'ref-123-123',
            'ResNum'  => 'inv-123-123',
            'TraceNo' => 'trace-123-123'
        ];

        $params = [
            'MerchantID' => $config['merchant'],
            'RefNum'     => 'ref-123-123'
        ];

        $soap->call('verifyTransaction', $params)->willReturn(1000);

        $invoiceVerifier->verifyReference('ref-123-123')->willReturn(true);
        $invoiceVerifier->verifyAmount('inv-123-123', 1000)->willReturn(true);

        $this->invoiceVerifier($invoiceVerifier);
        $this->handleCallback($post);

        $result = $this->getResult();

        $result->merchant()->shouldBe($config['merchant']);
        $result->invoice()->shouldBe('inv-123-123');
        $result->state()->shouldBe('OK');
        $result->reference()->shouldBe('ref-123-123');
        $result->traceNumber()->shouldBe('trace-123-123');
        $result->amount()->shouldBe(1000);
        $result->amountInToman()->shouldBe(100);

    }

    function it_should_throw_an_exception_when_post_data_is_invalid()
    {
        $post = [
            'invalid_state_key' => 'OK',
            'RefNum'            => 'ref-123-123',
            'ResNum'            => 'inv-123-123',
            'TraceNo'           => 'trace-123-123'
        ];

        $this->shouldThrow('\Nikapps\NikPay\Exceptions\InvalidPostDataException')
            ->duringHandleCallback($post);
    }

    function it_should_throw_an_exception_when_state_is_not_ok()
    {
        $post = [
            'State'   => 'Canceled By User',
            'RefNum'  => 'ref-123-123',
            'ResNum'  => 'inv-123-123',
            'TraceNo' => 'trace-123-123'
        ];

        $this->shouldThrow((new FailedPaymentException)->setState('Canceled By User'))
            ->duringHandleCallback($post);

    }

    function it_should_throw_an_exception_when_amount_is_not_equal(
        $soap,
        $samanConfig,
        InvoiceVerifier $invoiceVerifier
    ) {
        $config = $this->getDefaultConfig();

        $this->mockConfig($samanConfig);
        $this->mockSoapConnection($soap, $config['wsdl'], $config['soap_options']);

        $post = [
            'State'   => 'OK',
            'RefNum'  => 'ref-123-123',
            'ResNum'  => 'inv-123-123',
            'TraceNo' => 'trace-123-123'
        ];

        $params = [
            'MerchantID' => $config['merchant'],
            'RefNum'     => 'ref-123-123'
        ];

        $soap->call('verifyTransaction', $params)->willReturn(1200);

        $invoiceVerifier->verifyReference('ref-123-123')->willReturn(true);
        $invoiceVerifier->verifyAmount('inv-123-123', 1200)->willReturn(false);

        $this->invoiceVerifier($invoiceVerifier);

        $exception = (new NotEqualAmountException)
            ->setBankAmount(1200)
            ->setInvoice('inv-123-123')
            ->setReference('ref-123-123');

        $this->shouldThrow($exception)->duringHandleCallback($post);
    }

    function it_should_throw_an_exception_when_reference_already_exists($samanConfig, InvoiceVerifier $invoiceVerifier)
    {

        $this->mockConfig($samanConfig);

        $post = [
            'State'   => 'OK',
            'RefNum'  => 'ref-123-123',
            'ResNum'  => 'inv-123-123',
            'TraceNo' => 'trace-123-123'
        ];

        $invoiceVerifier->verifyReference('ref-123-123')->willReturn(false);

        $this->invoiceVerifier($invoiceVerifier);

        $this->shouldThrow((new DuplicateReferenceException)->setReference('ref-123-123'))
            ->duringHandleCallback($post);
    }

    function it_should_successfully_refund_payment($soap, $samanConfig)
    {

        $config = $this->getDefaultConfig();

        $this->mockConfig($samanConfig);
        $this->mockSoapConnection($soap, $config['wsdl'], $config['soap_options']);

        $params = [
            'MID'      => $config['merchant'],
            'RefNum'   => 'ref-123-123',
            'Username' => $config['username'],
            'Password' => $config['password']
        ];

        $soap->call('reverseTransaction', $params)->willReturn(1);

        $this->refund('ref-123-123')->shouldBe(true);

    }

    /**
     * Mock saman config
     *
     * @param SamanConfig $config
     * @param array $override
     */
    protected function mockConfig($config, $override = [])
    {
        $data = $this->getDefaultConfig($override);

        $config->getUsername()->willReturn($data['username']);
        $config->getPassword()->willReturn($data['password']);
        $config->getMerchantId()->willReturn($data['merchant']);
        $config->getWebServiceUrl()->willReturn($data['wsdl']);
        $config->getTokenUrl()->willReturn($data['token_wsdl']);
        $config->getGatewayUrl()->willReturn($data['gateway']);
        $config->getRedirectUrl()->willReturn($data['redirect_url']);
        $config->getSoapOptions()->willReturn($data['soap_options']);

    }

    /**
     * Get default config
     *
     * @param array $override [optional] it overrides default configs
     * @return array
     */
    protected function getDefaultConfig($override = [])
    {
        return array_merge(
            [
                'username'     => 'user',
                'password'     => 'pass',
                'merchant'     => 'mid',
                'wsdl'         => 'https://acquirer.samanepay.com/payments/referencepayment.asmx?WSDL',
                'token_wsdl'   => 'https://sep.shaparak.ir/Payments/InitPayment.asmx',
                'gateway'      => 'https://sep.shaparak.ir/Payment.aspx',
                'redirect_url' => 'http://example.com/callback',
                'soap_options' => null

            ],
            $override
        );
    }

    /**
     * Mock creating soap client
     *
     * @param SoapService $soap
     * @param string $wsdl
     * @param null|array $options
     */
    protected function mockSoapConnection($soap, $wsdl, $options = null)
    {
        $soap->wsdl($wsdl)->willReturn($soap);
        $soap->options($options)->willReturn($soap);
        $soap->createClient()->willReturn($soap);
    }
}
