<?php

function debug_log($txt) {
  if ((getenv('DEBUG_LOG') != '') && file_exists(getenv('DEBUG_LOG'))) { 
    $fp = fopen(getenv('DEBUG_LOG'), 'a+');
    fwrite($fp, $txt . "\n");
    fclose($fp);
  }
}


?>