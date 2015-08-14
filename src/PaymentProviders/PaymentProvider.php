<?php
namespace Nikapps\NikPay\PaymentProviders;

use Nikapps\NikPay\Exceptions\NotVerifiedException;
use Nikapps\NikPay\PaymentResult;
use Nikapps\NikPay\Purchase;

interface PaymentProvider
{
    /**
     * Prepare a payment (i.e. fetching token/refId)
     *
     * @param Purchase $purchase
     * @return self
     */
    public function prepare(Purchase $purchase);

    /**
     * Alias of prepare
     *
     * @see $this::prepare()
     * @param Purchase $purchase
     * @return self
     */
    public function fetchToken(Purchase $purchase);

    /**
     * Alias of prepare
     *
     * @see $this::prepare()
     * @param Purchase $purchase
     * @return self
     */
    public function token(Purchase $purchase);

    /**
     * Generate html form for redirecting user to bank gateway
     *
     * @param null|string $form Custom html form
     * @param string $token [Optional] if token is fetched manually
     * @return string
     */
    public function generateForm($form = null, $token = null);

    /**
     * Alias of generateForm
     *
     * @see $this::generateForm()
     * @param null|string $form Custom html form
     * @param string $token [Optional] if token is fetched manually
     * @return string
     */
    public function redirect($form = null, $token = null);

    /**
     * Get form data for redirecting user manually to bank gateway
     *
     * @return array
     */
    public function form();

    /**
     * verify payment
     *
     * @param string $reference
     * @param array $options [optional]
     * @throws NotVerifiedException
     * @return integer
     */
    public function verify($reference, array $options = []);

    /**
     * handle bank callback & verify it automatically
     *
     * @param array $data Post data, if empty it uses $_POST
     * @return self
     */
    public function handleCallback(array $data = []);

    /**
     * Refund payment
     *
     * @param string $reference
     * @param array $options [optional]
     * @return boolean
     */
    public function refund($reference, array $options = []);

    /**
     * Alias of refund
     *
     * @see $this::refund()
     * @param string $reference
     * @param array $options [optional]
     * @return boolean
     */
    public function reverse($reference, array $options = []);

    /**
     * Alias of refund
     *
     * @see $this::refund()
     * @param string $reference
     * @param array $options [optional]
     * @return boolean
     */
    public function reverseTransaction($reference, array $options = []);

    /**
     * Alias of refund
     *
     * @see $this::refund()
     * @param string $reference
     * @param array $options [optional]
     * @return boolean
     */
    public function recur($reference, array $options = []);

    /**
     * Get result of payment
     *
     * @return PaymentResult
     */
    public function result();

    /**
     * Alias of result
     *
     * @see $this::result()
     * @return mixed
     */
    public function getResult();
}
