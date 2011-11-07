<?php
require_once('FirePHPCore/FirePHP.class.php');

class sfFirePHP extends FirePHP
{
  protected function setHeader($Name, $Value)
  {
    $response = sfContext::getInstance()->getResponse();
    return $response->setHttpHeader($Name, $Value);
  }

  protected function getUserAgent()
  {
    return $_SERVER['HTTP_USER_AGENT'];
  }

  protected function newException($Message)
  {
    return new Exception($Message);
  }

  public static function setProcessor($url)
  {
    self::$instance->setHeader('X-FirePHP-ProcessorURL', $url);
  }

  public static function setRenderer($url)
  {
    self::$instance->setHeader('X-FirePHP-RendererURL', $url);
  }
}