<?php

class Payfort_ExceptionsTest extends \PHPUnit_Framework_TestCase
{

  /**
  * @expectedException Payfort_Error_Authentication
  */
  function testListAuthenticationException()
  {
    Payfort::setApiKey('invalid_token');
    Payfort_Charge::all();
  }

  /**
  * @expectedException Payfort_Error_Authentication
  */
  function testAuthenticationException()
  {
    Payfort::setApiKey('invalid_token');

    $data = array(
      "amount" => 1050,
      "currency" => "usd",
      "card" => array(
        "number" => "4242424242424242",
        "exp_month" => 11,
        "exp_year" => 2016,
        "cvc" => "123"
      ),
      "description" => "Charge for test@example.com"
    );

    Payfort_Charge::create($data);
  }

  /**
  * @expectedException Payfort_Error_Request
  */
  function testCardException()
  {
    Payfort::setApiKey('test_sec_k_25dd497d7e657bb761ad6');

    $data = array(
      "amount" => 1050,
      "currency" => "usd",
      "card" => array(
        "number" => "4141414141414141",
        "exp_month" => 11,
        "exp_year" => 2016,
        "cvc" => "123"
      ),
      "description" => "Charge for test@example.com"
    );

    Payfort_Charge::create($data);
  }

  // This test should raise an exception but doesn't. Raised issue:
  //
  // /**
  // * @expectedException Payfort_Error_Request
  // */
  // function testParametersException()
  // {
  //   Payfort::setApiKey('test_sec_k_25dd497d7e657bb761ad6');

  //   $data = array(
  //     "amount" => -1.30,
  //     "currency" => "usd",
  //     "card" => array(
  //       "number" => "4242424242424242",
  //       "exp_month" => 12,
  //       "exp_year" => 2016,
  //       "cvc" => "123"
  //     ),
  //     "description" => "Charge for test@example.com"
  //   );

  //   Payfort_Charge::create($data);
  // }

  // We need to setup the card to raise a Processing error
  // /*
  //  * @expectedException Payfort_Error_Processing
  //  */
  // function testApiException()
  // {
  //   Payfort::setApiKey('test_sec_k_25dd497d7e657bb761ad6');

  //   $data = array(
  //     "amount" => 1050,
  //     "currency" => "usd",
  //     "card" => array(
  //       "number" => "3566002020360505",
  //       "exp_month" => 12,
  //       "exp_year" => 2016,
  //       "cvc" => "123"
  //     ),
  //     "description" => "Charge for test@example.com"
  //   );

  //   Payfort_Charge::create($data);
  // }
}
