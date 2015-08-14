<?php
namespace Nikapps\NikPay;

interface InvoiceVerifier
{
    /**
     * Verify reference is not already exist (checking for double spending)
     *
     * @param string|integer $reference [Given by bank]
     * @return boolean true if invoice is valid
     */
    public function verifyReference($reference);

    /**
     * @param string|integer $invoice [Given by bank]
     * @param integer $amount [Given by bank]
     * @return boolean true if amount is valid
     */
    public function verifyAmount($invoice, $amount);
}
