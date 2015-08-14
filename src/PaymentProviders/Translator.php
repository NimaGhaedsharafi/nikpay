<?php
namespace Nikapps\NikPay\PaymentProviders;

interface Translator
{
    /**
     * Translate/Describe error or state code
     *
     * @param string|integer $code
     * @return string|integer
     */
    public function translate($code);

    /**
     * Alias of translate
     *
     * @see $this::translate()
     * @param string|integer $code
     * @return string|integer
     */
    public function trans($code);

    /**
     * Alias of translate
     *
     * @see $this::translate()
     * @param string|integer $code
     * @return string|integer
     */
    public function describe($code);
}
