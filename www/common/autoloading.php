<?php

// setup autoloading of classes
function my_autoloader($class_name) {
  require_once('model/'.$class_name.'.php'); 
}



function autoloadCSS() {
  global $css;
  $fn = strtolower($GLOBALS['controller']).'.css';
  if (isset($GLOBALS['controller']) && file_exists("css/".$fn) && !in_array($fn, array_values($css))) {
    $css[] = $fn; 
  }

  $fn = strtolower($GLOBALS['controller']).'/'.strtolower($GLOBALS['action']).'.css';
  if (isset($GLOBALS['controller']) && 
      isset($GLOBALS['action']) && 
      file_exists("css/".$fn) && 
      !in_array($fn, array_values($css))) {
    $css[] = $fn;
  }
}


function autoloadJS() {
  global $js;
  $fn = strtolower($GLOBALS['controller']).'.js';
  if (isset($GLOBALS['controller']) && 
      file_exists( "js/".strtolower($GLOBALS['controller']).".js") && 
      !in_array($fn, array_values($js))) {
    $js[] = $fn;
  }

  $fn = strtolower($GLOBALS['controller']).'/'.strtolower($GLOBALS['action']).'.js';
  if (isset($GLOBALS['controller']) && 
      isset($GLOBALS['action']) &&
      file_exists("js/".$fn) && 
      !in_array($fn, array_values($js))) {
    $js[] = $fn;
  }
}

?>