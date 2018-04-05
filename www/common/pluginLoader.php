<?php

function loadPlugins() {
  global $css, $js, $plugins;

  $css = array(); $js = array();

  $dbh = dbHandle(1);
  $results = $dbh->query("SELECT * FROM plugins");
  foreach ($results as $plugin) {
    if ($plugin['enabled'] == 1) { 
      $plugins[$plugin['script']] = $plugin['classname'];
      $cssFN = './css/'.$plugin['script'].'.css';
      $jsFN = './js/'.$plugin['script'].'.js';
      if (file_exists($cssFN)) { addCSS($plugin['script'].'.css'); }
      if (file_exists($jsFN)) { addJS($plugin['script'].'.js'); }
    }
  }
}


?>