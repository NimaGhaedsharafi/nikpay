<?php

namespace spec\Nikapps\NikPay\Utils;

use PhpSpec\ObjectBehavior;
use Prophecy\Argument;

class UtilsSpec extends ObjectBehavior
{
    public function it_is_initializable()
    {
        $this->shouldHaveType('Nikapps\NikPay\Utils\Utils');
    }

    public function it_should_return_the_value_of_given_key()
    {
        $array = [
            'some'  => 'thing',
            'hello' => 'world'
        ];

        $this->value($array, 'hello')->shouldBe('world');
    }

    public function it_should_return_default_value_when_key_is_not_exist()
    {
        $array = [
            'some'  => 'thing',
            'hello' => 'world'
        ];

        $this->value($array, 'foo', 'bar')->shouldBe('bar');
    }

    public function it_should_return_null_when_both_key_and_default_are_not_exist()
    {
        $array = [
            'some'  => 'thing',
            'hello' => 'world'
        ];

        $this->value($array, 'foo')->shouldBeNull();
    }
}
