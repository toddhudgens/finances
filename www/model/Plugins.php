<?php

class Plugins {

  public static function run($methodName, $params) {
    global $plugins; 
    foreach ($plugins as $p => $pluginScript) {
      $method = new ReflectionMethod($pluginScript, $methodName);
      echo $method->invokeArgs(null, $params);
    }
  }
}

?>