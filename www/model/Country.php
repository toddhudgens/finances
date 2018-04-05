<?php

class Country {

  public static function getAll() {
    $dbh = dbHandle(1);
    $q = 'SELECT * FROM countries ORDER BY name';
    $results = $dbh->query($q);
    $rows = $results->fetchAll(PDO::FETCH_ASSOC);
    return $rows;
  }
}

?>