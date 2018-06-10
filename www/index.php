<?php

date_default_timezone_set("America/New_York");

$sessionTimeout = 900;
ini_set('session.gc_maxlifetime', $sessionTimeout);

session_start();
$GLOBALS['executionStart'] = microtime(true);

// update the session with every page load to modify the session data on disk
if (isset($_SESSION['LAST_ACTIVITY']) && (time() - $_SESSION['LAST_ACTIVITY'] > $sessionTimeout)) {
  session_unset();
  session_destroy();
}
$_SESSION['LAST_ACTIVITY'] = time();

require_once('common/autoloading.php');
require_once('vendor/autoload.php');

spl_autoload_register('my_autoloader');

require_once('common/db.php');
require_once('common/logging.php');
require_once('common/routing.php');
require_once('common/util.php');
require_once("common/pluginLoader.php");
require 'controller/ErrorController.php';

// initialize view variables 
$GLOBALS['view'] = array();
if (loggedIn()) { $GLOBALS['view']['loggedIn'] = 1; }
else { $GLOBALS['view']['loggedIn'] = 0; }

global $action, $applyView, $loginRequired;
global $css, $js, $title;

routeRequest();

if ($loginRequired && !isLoggedIn()) { 
  redirectToPage("/login",0); 
  exit; 
}
if ($action == "") { $action = "pageNotFound"; }
else { loadPlugins(); }

$loader = new Twig_Loader_Filesystem('templates');
$twig = new Twig_Environment($loader, 
                              array('cache' => '../cache/'));
$GLOBALS['twig'] = $twig;


ob_start();
$action();
$content = ob_get_clean();

if ($applyView == 1) {
  autoloadCSS();
  autoloadJS();

  // render header template
  $viewParams = array('title' => $title,
                      'js' => $js,
                      'css' => $css);
  echo $GLOBALS['twig']->render('header.twig', $viewParams);

  // display page content
  echo $content;

  // render footer template
  $executionTime = round(microtime(true) - $GLOBALS['executionStart'],3);
  $viewParams = array('year' => date('Y'),
                      'loggedIn' => isLoggedin(),
                      'executionTime' => $executionTime);
  echo $GLOBALS['twig']->render('footer.twig', $viewParams);

}
else { echo $content; }


?>
