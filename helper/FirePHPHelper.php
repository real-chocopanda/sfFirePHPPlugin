<?php

/**
 * Log variable to firebug
 *
 * @param mixed $object
 * @param string $level
 */
function flog($object, $level = FirePHP::LOG) {
  $firephp = sfFirePHP::getInstance(true);
  $firephp->fb($object, $level);
}

/**
 * Log info to firebug
 *
 * @param mixed $object
 */
function finfo($object) {
  fire_log($object, FirePHP::INFO);
}

/**
 * Log warning to firebug
 *
 * @param mixed $object
 */
function fwarn($object) {
  fire_log($object, FirePHP::WARN);
}

/**
 * Log error to firebug
 * 
 * @param mixed $object
 */
function ferror($object) {
  fire_log($object, FirePHP::ERROR);
}