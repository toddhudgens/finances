<?php

function dbHandle() {
  return new PDO(getenv('PDOCONNSTR'), getenv('DBUN'), getenv('DBPW'));
}

?>