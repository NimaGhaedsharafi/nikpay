<?php
namespace Nikapps\NikPay\PaymentProviders\FaraGate;

use Nikapps\NikPay\Purchase;

class FaraGatePurchase extends Purchase
{
    public function setName($name)
    {
        return $this->addOption('name', $name);
    }

    public function getName()
    {
        return $this->getOption('name');
    }

    public function setEmail($email)
    {
        return $this->addOption('email', $email);
    }

    public function getEmail()
    {
        return $this->getOption('email');
    }

    public function setMobile($mobile)
    {
        return $this->addOption('mobile', $mobile);
    }

    public function getMobile()
    {
        return $this->getOption('mobile');
    }

    public function setNote($note)
    {
        return $this->addOption('note', $note);
    }

    public function getNote()
    {
        return $this->getOption('note');
    }

    public function setQueries(array $queries)
    {
        return $this->addOption('queries', $queries);
    }

    public function getQueries()
    {
        return $this->getOption('queries');
    }

    public function setPosts(array $posts)
    {
        return $this->addOption('posts', $posts);
    }

    public function getPosts()
    {
        return $this->getOption('posts');
    }

    public function setAccounts(array $accounts)
    {
        return $this->addOption('accounts', $accounts);
    }

    public function getAccounts()
    {
        return $this->getOption('accounts');
    }

    public function setBank($bank)
    {
        return $this->addOption('bank', $bank);
    }

    public function getBank()
    {
        return $this->getOption('bank');
    }
}