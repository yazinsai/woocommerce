<?php


class PayfortTest extends \PHPUnit_Framework_TestCase
{
  function testApiKey()
  {
    $testKey = 'test_sec_k_25dd497d7e657bb761ad6';
    Payfort::setApiKey($testKey);
    $this->assertEquals($testKey, Payfort::getApiKey());
  }

  function testSimpleMethods()
  {
    $this->assertEquals('https://api.whitepayments.com/', Payfort::getBaseURL());
  }

  function testEndPoints()
  {
    $this->assertEquals('https://api.whitepayments.com/charges/', Payfort::getEndPoint('charge'));
    $this->assertEquals('https://api.whitepayments.com/charges/', Payfort::getEndPoint('charge_list'));
  }
}
