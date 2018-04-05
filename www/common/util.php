<?php


function addCSS($script) {
  global $css;
  if ($script == null) { return; }
  if (is_array($script)) { foreach ($script as $i => $scr) { array_push($css, $scr); } }
  else { array_push($css, $script); }
}

function addJS($script) {
  global $js;
  if ($script == null) { return; }
  if (is_array($script)) { foreach ($script as $i => $scr) { array_push($js, $scr); } }
  else { array_push($js, $script); }
}

function redirectToPage($page, $timer) { 
  print '<meta http-equiv="refresh" target="_top" content="' . $timer . ';url=' . $page . '">'; 
}

function isLoggedIn() { return loggedIn(); }

function loggedIn() { if (isset($_SESSION['userId'])) { return true; }  else { return false; } }

?>