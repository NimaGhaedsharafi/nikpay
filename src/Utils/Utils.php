<?php
namespace Nikapps\NikPay\Utils;

class Utils
{

    /**
     * Return value if it's set otherwise return default value
     *
     * @param mixed $value
     * @param mixed $default
     * @return mixed
     */
    public static function value($value, $default = null)
    {
        return isset($value) ? $value : $default;
    }
} 