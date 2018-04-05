<?php


class AccountType {

  public static function getAll() {
    try {
      $dbh = dbHandle(1);
      $q = 'SELECT atid as id, name FROM accountType ORDER BY sortOrder';
      $dbRes = $dbh->query($q);
      $rows = $dbRes->fetchAll(PDO::FETCH_ASSOC);
      $results = array();
      foreach ($rows as $i => $info) { $results[$info['id']] = $info['name']; }
      return $results;
    }
    catch (PDOException $e) { }
  }
}


?>