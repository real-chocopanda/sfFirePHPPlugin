<?php

class sfFirePHPLogger extends sfVarLogger
{
  protected $sfFire = null;
  protected $dispatcher = null;

  public function initialize(sfEventDispatcher $dispatcher, $options = array())
  {
    $this->sfFire = sfFirePHP::getInstance(true);

    if(isset($options['processor']))
    {
      sfFirePHP::setProcessor($options['processor'].'?'.time());
    }

    if(isset($options['renderer']))
    {
      sfFirePHP::setRenderer($options['renderer'].'?'.time());
    }

    if(isset($options['fire_php_options']))
    {
      $instance = FirePHP::getInstance(true);
      $instance->setOptions($options['fire_php_options']);
      unset($options['fire_php_options']);
    }

    $dispatcher->connect('response.filter_content', array($this, 'filterResponseContent'));

    $this->dispatcher = $dispatcher;

    return parent::initialize($dispatcher, $options);
  }


  protected function fireLog($subject, $message, $priority)
  {
    switch($priority)
    {
      case  sfLogger::ERR:
      case  sfLogger::CRIT:
      case  sfLogger::EMERG:
      case  sfLogger::ALERT:
        $this->sfFire->error($message, $subject);
        break;
      case sfLogger::WARNING:
        $this->sfFire->warn($message, $subject);
        break;
      case sfLogger::DEBUG:
        $this->sfFire->log($message, $subject);
        break;
      default:
        $this->sfFire->info($message, $subject);
    }
  }


  public function filterResponseContent(sfEvent $event, $content)
  {
    $this->sendMemory();
    $this->sendTimers();
    if (sfConfig::get('sf_logging_enabled'))
    {
      $this->sendConfig();
    }

    $this->sendLogs();

    return $content;
  }

  protected function sendConfig()
  {
    $context = sfContext::getInstance();
    $config = array(
      'debug'        => sfConfig::get('sf_debug')           ? 'on' : 'off',
      'xdebug'       => extension_loaded('xdebug')          ? 'on' : 'off',
      'logging'      => sfConfig::get('sf_logging_enabled') ? 'on' : 'off',
      'cache'        => sfConfig::get('sf_cache')           ? 'on' : 'off',
      'compression'  => sfConfig::get('sf_compressed')      ? 'on' : 'off',
      'tokenizer'    => function_exists('token_get_all')    ? 'on' : 'off',
      'eaccelerator' => extension_loaded('eaccelerator') && ini_get('eaccelerator.enable') ? 'on' : 'off',
      'apc'          => extension_loaded('apc') && ini_get('apc.enabled')                  ? 'on' : 'off',
      'xcache'       => extension_loaded('xcache') && ini_get('xcache.cacher')          ? 'on' : 'off',
      'request'      => sfDebug::removeObjects(sfDebug::requestAsArray($context->getRequest())),
      'response'     => sfDebug::removeObjects(sfDebug::responseAsArray($context->getResponse())),
      'user'         => sfDebug::removeObjects(sfDebug::userAsArray($context->getUser())),
      'settings'     => sfDebug::removeObjects(sfDebug::settingsAsArray()),
      'globals'      => sfDebug::removeObjects(sfDebug::globalsAsArray()),
      'php'          => sfDebug::removeObjects(sfDebug::phpInfoAsArray()),
      'symfony'      => sfDebug::removeObjects(sfDebug::symfonyInfoAsArray())
    );


    $this->sfFire->dump('Config', $config);

  }



  protected function sendMemory()
  {
    if (function_exists('memory_get_usage'))
    {
      $totalMemory = sprintf('%.1f', (memory_get_usage() / 1024));

      $this->sfFire->fb('Memory: '.$totalMemory.'KB');
    }
  }

  protected function sendLogs()
  {

    $event = $this->dispatcher->filter(new sfEvent($this, 'debug.fire_php.filter_logs'), $this->logs);
    $logs = $event->getReturnValue();


    $this->sfFire->group('Logs '.count($logs), array('Collapsed' => true));
    foreach($logs as $log)
    {
      if(!count($log['debug_backtrace']))
      {
         $this->fireLog($log['type'], $log['message'], $log['priority']);
      }
      else
      {
        $this->sfFire->group($log['type'].': '.$log['message'], array('Collapsed' => true));
        foreach($log['debug_backtrace'] as $debug)
        {
          $this->sfFire->fb($debug);
        }
        $this->sfFire->groupEnd();
      }
    }
    $this->sfFire->groupEnd();
  }

  protected function sendTimers()
  {
    $timers = sfTimerManager::getTimers();

    $totalTime = $this->getTotalTime();
    $panel = array();

    $timer_nb = 1;
    foreach ($timers as $name => $timer)
    {
      array_push($panel, array(
                                  'number'  => $timer_nb,
                                  'name'    => $name,
                                  'calls'   => $timer->getCalls(),
                                  'time'    => $timer->getElapsedTime() * 1000,
                                  'percent' => $totalTime ? ($timer->getElapsedTime() * 1000 * 100 / $totalTime) : 'N/A'
                                ));
      $timer_nb++;
    }

    if(count($panel))
    {
      array_unshift($panel, array('','Type', 'Calls', 'Time(ms)', 'Time(%)'));
      $this->sfFire->table('Timers '.$this->getTotalTime().' ms',  $panel);
    }
    else
    {
        $this->sfFire->group('Timers '.$this->getTotalTime().' ms');
        $this->sfFire->info('No info available');
        $this->sfFire->groupEnd();
    }
  }

  protected function getTotalTime()
  {
    return isset($_SERVER['REQUEST_TIME']) ? sprintf('%.0f', (microtime(true) - $_SERVER['REQUEST_TIME']) * 1000) : 0;
  }
}
