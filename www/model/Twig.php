<?php

class Twig { 

public static function render($viewScript, $params=array()) {
  $viewParams = array_merge($GLOBALS['view'], $params); 
  echo $GLOBALS['twig']->render($viewScript, $viewParams);
}


}


?>