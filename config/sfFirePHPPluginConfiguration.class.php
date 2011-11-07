<?php

/**
 * sfFirePHPPluginConfiguration configuration.
 *
 * @package    sfFirePHPPlugin
 * @subpackage config
 * @author     Martinescu Petrica <petrica@martinescu.com>
 */
class sfFirePHPPluginConfiguration extends sfPluginConfiguration
{
  /**
   * @see sfPluginConfiguration
   */
  public function configure()
  {
    require_once dirname(__FILE__) . '/../helper/FirePHPHelper.php';
  }
}