<?php
namespace Nikapps\NikPay;

use Nikapps\NikPay\Utils\Utils;

class PaymentResult
{

    /**
     * @var string|integer
     */
    protected $state;

    /**
     * @var string
     */
    protected $reference;

    /**
     * @var string|integer
     */
    protected $invoice;

    /**
     * @var integer
     */
    protected $amount;

    /**
     * @var string|integer
     */
    protected $merchantId;

    /**
     * @var string|integer
     */
    protected $traceNumber;

    function __construct($result)
    {
        $this->state = Utils::value($result['state'], 'OK');
        $this->merchantId = Utils::value($result['merchant'], '');
        $this->reference = Utils::value($result['reference'], '');
        $this->invoice = Utils::value($result['invoice'], '');
        $this->traceNumber = Utils::value($result['trace'], '');
        $this->amount = Utils::value($result['amount'], 0);
    }


    /**
     * Get amount in Rial
     *
     * @return int
     */
    public function amount()
    {
        return $this->amount;
    }

    /**
     * Get amount in Toman
     *
     * @return int
     */
    public function amountInToman()
    {
        return $this->amount / 10;
    }

    /**
     * get Invoice number
     *
     * @return int|string
     */
    public function invoice()
    {
        return $this->invoice;
    }

    /**
     * Get Merchant Id
     *
     * @return int|string
     */
    public function merchantId()
    {
        return $this->merchantId;
    }

    /**
     * Alias of merchantId
     *
     * @return int|string
     */
    public function merchant()
    {
        return $this->merchantId();
    }

    /**
     * Alias of merchantId
     *
     * @return int|string
     */
    public function mid()
    {
        return $this->merchantId();
    }

    /**
     * Alias of merchantId
     *
     * @return int|string
     */
    public function terminal()
    {
        return $this->merchantId();
    }

    /**
     * Alias of merchantId
     *
     * @return int|string
     */
    public function terminalId()
    {
        return $this->merchantId();
    }

    /**
     * Get reference number
     *
     * @return string
     */
    public function reference()
    {
        return $this->reference;
    }

    /**
     * Alias of reference
     *
     * @return string
     */
    public function refNum()
    {
        return $this->reference();
    }

    /**
     * @return int|string
     */
    public function state()
    {
        return $this->state;
    }

    /**
     * @return int|string
     */
    public function traceNumber()
    {
        return $this->traceNumber;
    }

    /**
     * Alias of traceNumber
     *
     * @return int|string
     */
    public function traceNo()
    {
        return $this->traceNumber();
    }


} 