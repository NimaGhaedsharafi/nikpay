<?php
namespace Nikapps\NikPay\PaymentProviders\Saman;

use Nikapps\NikPay\PaymentProviders\Translator;

class SamanTranslator implements Translator
{


    protected $states;
    protected $errors;

    function __construct()
    {
        $this->states = require_once __DIR__ . '/lang/states.php';
        $this->errors = require_once __DIR__ . '/lang/errors.php';
    }

    /**
     * Translate/Describe error or state code
     *
     * @param string|integer $code
     * @return string|integer
     */
    public function translate($code)
    {
        if ((is_string($code))) {
            return isset($this->states[$code]) ? $this->states[$code] : $code;
        }

        if (is_int($code) && $code < 0) {
            return isset($this->errors[$code]) ? $this->errors[$code] : $code;
        }

        return $code;
    }

    /**
     * Alias of translate
     *
     * @see $this::translate()
     * @param string|integer $code
     * @return string|integer
     */
    public function trans($code)
    {
        return $this->translate($code);
    }

    /**
     * Alias of translate
     *
     * @see $this::translate()
     * @param string|integer $code
     * @return string|integer
     */
    public function describe($code)
    {
        return $this->translate($code);
    }
}