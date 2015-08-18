<?php

/**
 * Class to hold Payfort API settings
 *
 * @author Yazin <yazin@payfort.com>
 * @link https://start.payfort.com/docs/
 * @license http://opensource.org/licenses/MIT
 */

class Payfort
{

  /**
  * Current API key
  * @var string
  */
  protected static $apiKey;

  /**
  * API Server URL
  * @var string
  */
  protected static $baseURL = 'https://api.start.payfort.com/';

  /**
  * API endpoints
  * @var array
  */
  protected static $endpoints = array(
    'charge'        => 'charges/',
    'charge_list'   => 'charges/',
    'customer'      => 'customers/',
    'customer_list' => 'customers/',
    'refund'        => 'refunds/'
  );

  /*
  * Path to the CA Certificates required when making CURL calls
  */
  public static function getCaPath() {
    return realpath(dirname(__FILE__)) . '/data/ca-certificates.crt';
  }

  /**
  * sets API Key
  *
  * @param string $apiKey API key
  */
  public static function setApiKey($apiKey)
  {
    self::$apiKey = $apiKey;
  }

  /**
  * returns current set API Key
  *
  * @return string ApiKey
  */
  public static function getApiKey()
  {
    return self::$apiKey;
  }

  /**
  * returns current set API Base URL
  *
  * @return string BaseURL
  */
  public static function getBaseURL()
  {
    return self::$baseURL;
  }

  /**
  * returns full endpoint URI
  *
  * @return string endpoint URI
  */
  public static function getEndPoint($name)
  {
    return self::$baseURL . self::$endpoints[$name];
  }

  public static function handleErrors($result, $httpStatusCode)
  {
    switch($result['error']['type']) {
      case Payfort_Error_Authentication::$TYPE:
        throw new Payfort_Error_Authentication($result['error']['message'], $result['error']['code'], $httpStatusCode);
        break;

      case Payfort_Error_Banking::$TYPE:
        throw new Payfort_Error_Banking($result['error']['message'], $result['error']['code'], $httpStatusCode);
        break;

      case Payfort_Error_Processing::$TYPE:
        throw new Payfort_Error_Processing($result['error']['message'], $result['error']['code'], $httpStatusCode);
        break;

      case Payfort_Error_Request::$TYPE:
        throw new Payfort_Error_Request($result['error']['message'], $result['error']['code'], $httpStatusCode);
        break;
    }

    // None of the above? Throw a general White Error
    throw new Payfort_Error($result['error']['message'], $result['error']['code'], $httpStatusCode);
  }
}
