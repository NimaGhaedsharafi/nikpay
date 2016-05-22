<?php
namespace Nikapps\NikPay\PaymentProviders\Mellat;

use Nikapps\NikPay\Purchase;

class MellatPurchase extends Purchase
{

    /**
     * Set local date (optional)
     *
     * @param string $date
     * @return $this
     */
    public function setDate($date)
    {
        return $this->addOption('date', $date);
    }

    /**
     * Get local date
     *
     * @return string
     */
    public function getDate()
    {
        return $this->getOption('date');
    }

    /**
     * Set local time (optional)
     *
     * @param string $time
     * @return $this
     */
    public function setTime($time)
    {
        return $this->addOption('time', $time);
    }

    /**
     * Get local time
     *
     * @return string
     */
    public function getTime()
    {
        return $this->getOption('time');
    }

    /**
     * Set additional/custom data (optional)
     *
     * @param string $data
     * @return $this
     */
    public function setCustomDate($data)
    {
        return $this->addOption('custom', $data);
    }

    /**
     * Get additional/custom id
     *
     * @return string
     */
    public function getCustomData()
    {
        return $this->getOption('custom');
    }

    /**
     * Set payer id (optional)
     *
     * @param int $payerId
     * @return $this
     */
    public function setPayerId($payerId)
    {
        return $this->addOption('payerId', $payerId);
    }

    /**
     * Get payer id
     *
     * @return int
     */
    public function getPayerId()
    {
        return $this->getOption('payerId');
    }

}