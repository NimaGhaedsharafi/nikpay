<?php
namespace Nikapps\NikPay\PaymentProviders\Arianpal;

use Nikapps\NikPay\PaymentProviders\Translator;

class ArianpalTranslator implements Translator
{
    protected $states;
    protected $errors;

    /**
     * Translate/Describe error or state code
     *
     * @param string|integer $code
     * @return string|integer
     */
    public function translate($code)
    {
        $this->load();

        if (is_string($code)) {
            return isset($this->states[$code]) ? $this->states[$code] : $code;
        }

        return isset($this->errors[$code]) ? $this->errors[$code] : $code;
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

    /**
     * Load languages
     */
    protected function load()
    {
        if (!$this->states) {
            $this->states = require __DIR__ . '/lang/states.php';
        }

        if (!$this->errors) {
            $this->errors = require __DIR__ . '/lang/errors.php';
        }
    }
}
