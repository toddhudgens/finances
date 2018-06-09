<?php

function dbHandle() {
  if (isset($GLOBALS['dbh'])) { return $GLOBALS['dbh']; }
  else { 
    $GLOBALS['dbh'] = new PDO(getenv('PDOCONNSTR'), getenv('DBUN'), getenv('DBPW'));
    return $GLOBALS['dbh'];
  }
}

?>