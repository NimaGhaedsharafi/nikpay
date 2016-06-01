<?php
namespace Nikapps\NikPay;

use Nikapps\NikPay\Utils\Utils;

class Purchase
{
    /**
     * @var integer
     */
    protected $amount;

    /**
     * Is amount in rial?
     *
     * @var boolean
     */
    protected $rial;

    /**
     * @var string|integer
     */
    protected $invoice;

    /**
     * Other options
     *
     * @var array
     */
    protected $options;

    /**
     * Constructor for purchase
     *
     * @param array $purchase
     */
    public function __construct(array $purchase = [])
    {
        $this->amount = Utils::value($purchase, 'amount', 0);
        $this->rial = Utils::value($purchase, 'rial', true);
        $this->invoice = Utils::value($purchase, 'invoice', '');
        $this->options = Utils::value($purchase, 'options', []);
    }

    /**
     * @param int $amount
     * @return $this
     */
    public function setAmount($amount)
    {
        $this->amount = $amount;

        return $this;
    }

    /**
     * @param int|string $invoice
     * @return $this
     */
    public function setInvoice($invoice)
    {
        $this->invoice = $invoice;

        return $this;
    }

    /**
     * @param array $options
     * @return $this
     */
    public function setOptions($options)
    {
        $this->options = $options;

        return $this;
    }

    /**
     * Amount is in Rial
     *
     * @return $this
     */
    public function rial()
    {
        $this->rial = true;

        return $this;
    }

    /**
     * Amount is in Toman
     *
     * @return $this
     */
    public function toman()
    {
        $this->rial = false;

        return $this;
    }

    /**
     * @return int
     */
    public function getAmount()
    {
        return $this->amount;
    }

    /**
     * @return int|string
     */
    public function getInvoice()
    {
        return $this->invoice;
    }

    /**
     * @return array
     */
    public function getOptions()
    {
        return $this->options;
    }

    /**
     * @param string $key
     * @param mixed|null $default
     * @return mixed|null
     */
    public function getOption($key, $default = null)
    {
        return isset($this->options[$key])
            ? $this->options
            : $default;
    }

    /**
     * @param string $key
     * @return bool
     */
    public function hasOption($key)
    {
        return isset($this->options[$key]) && !is_null($this->options[$key]);
    }

    /**
     * @param string $key
     * @param mixed $value
     * @return $this
     */
    public function addOption($key, $value)
    {
        $this->options[$key] = $value;

        return $this;
    }

    /**
     * @return boolean
     */
    public function isRial()
    {
        return $this->rial;
    }

    /**
     * Convert amount to rial if needed
     *
     * @return int
     */
    public function getAmountInRial()
    {
        return $this->isRial() ? $this->amount : $this->amount * 10;
    }
}
