<?php
namespace Nikapps\NikPay\Utils;

class Utils
{

    /**
     * Return value if it's set otherwise return default value
     *
     * @param array $data
     * @param mixed $key
     * @param mixed $default
     * @return mixed
     */
    public static function value(array $data, $key, $default = null)
    {
        return isset($data[$key]) ? $data[$key] : $default;
    }
} 