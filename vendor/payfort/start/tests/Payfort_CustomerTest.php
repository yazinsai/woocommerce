<?php
class Payfort_CustomerTest extends \PHPUnit_Framework_TestCase
{
  function setUp()
  {
    Payfort::setApiKey('test_sec_k_25dd497d7e657bb761ad6');
    // Data for a successful customer
    $this->success_data = array(
      "name" => "Test Customer",
      "email" => "test@customer.com",
      "description" => "Signed up at the fair",
      "card" => array(
        "number" => "4242424242424242",
        "exp_month" => 11,
        "exp_year" => 2016,
        "cvc" => "123"
      )
    );
  }
  function testList()
  {
    $result = Payfort_Customer::all();
    //No assertion. If there is an error, an exception is thrown. Otherwise it was ok.
  }
  function testCreateSuccess()
  {
    $result = Payfort_Customer::create($this->success_data);
    $expected = array(
      'id' => '',
      'email' => '',
      'description' => '',
      'default_card_id' => '',
      'name' => '',
      'created_at' => '',
      'updated_at' => '',
      'object' => '',
      'cards' => ''
      );
    $this->assertEquals(array_keys($expected), array_keys($result));
  }
  function testRetrieveCustomerId()
  {
    $result = Payfort_Customer::create($this->success_data);
    $this->assertArrayHasKey('id', $result);
  }
  // TODO: These tests are really shallow .. beef them up!
}
