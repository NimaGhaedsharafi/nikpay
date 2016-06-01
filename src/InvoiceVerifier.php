<?php
namespace Nikapps\NikPay;

interface InvoiceVerifier
{
    /**
     * Verify reference is not already exist (checking for double spending)
     *
     * @param string|integer $reference [Given via bank]
     * @return boolean true if invoice is valid
     */
    public function verifyReference($reference); 
    // todo add options
    // todo add bank

    /**
     * @param string|integer $invoice [Given via bank]
     * @param integer $amount [Given via bank]
     * @return boolean true if amount is valid
     */
    public function verifyAmount($invoice, $amount);
}
