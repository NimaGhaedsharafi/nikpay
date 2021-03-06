<?php

namespace spec\Nikapps\NikPay;

use Nikapps\NikPay\PaymentProviders\Saman\SamanConfig;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;

class NikPaySpec extends ObjectBehavior
{
    public function it_is_initializable()
    {
        $this->shouldHaveType('Nikapps\NikPay\NikPay');
    }

    public function it_should_return_an_instance_of_saman_class(SamanConfig $config)
    {
        $this->bank('saman', $config)
            ->shouldBeAnInstanceOf('Nikapps\NikPay\PaymentProviders\Saman\Saman');
    }

    public function it_should_throw_an_exception_when_no_config_is_provided()
    {
        $this->shouldThrow('\Nikapps\NikPay\Exceptions\NotFoundConfigurationException')
            ->duringBank('saman');
    }

    public function is_should_use_default_config_when_no_config_is_provided(SamanConfig $config)
    {
        $this->useConfig('saman', $config);

        $this->bank('saman')
            ->shouldBeAnInstanceOf('Nikapps\NikPay\PaymentProviders\Saman\Saman');
    }

    public function it_should_return_an_instance_of_saman_translator()
    {
        $this->translator('saman')
            ->shouldBeAnInstanceOf('\Nikapps\NikPay\PaymentProviders\Saman\SamanTranslator');
    }
}
