<?php

class Tag {


  public static function get($id) {
    $dbh = dbHandle(1);
    $stmt = $dbh->prepare('SELECT id,name FROM tags WHERE id=?');
    $stmt->execute(array($id));
    $row = $stmt->fetch(PDO::FETCH_ASSOC);
    if ($row) { return $row; } else { return array(); }
  }

  public static function getAll() {
    $tags = array();
    $dbh = dbHandle(1);
    $results = $dbh->query('SELECT * FROM tags ORDER BY name');
    if ($results) { foreach ($results as $row) { $tags[] = $row; }}
    return $tags;
  }



  public static function add($name) {
    $dbh = dbHandle(1);
    $stmt = $dbh->prepare('INSERT INTO tags (id,name) VALUES(0,?)');
    $stmt->execute(array($name)); 
    if ($stmt->rowCount()) {
      $tagId = $dbh->lastInsertId();
      return array($tagId);
    }
    else {
      // most likely a duplicate tag, check for that
      $stmt = $dbh->prepare('SELECT * FROM tags WHERE name=?');
      $stmt->execute(array($name));
      $row = $stmt->fetch(PDO::FETCH_ASSOC);
      if (isset($row['id'])) { return array($row['id']); }
      else { return array('error', $stmt->errorInfo()); }
    }
  }



  public static function search($searchText) {
    $dbh = dbHandle(1);
    $q = 'SELECT t.id, t.name FROM tags t WHERE t.name LIKE ? '.
         'ORDER BY t.name LIMIT 6';
    $stmt = $dbh->prepare($q);
    $stmt->execute(array('%'.$searchText.'%'));
    $results = $stmt->fetchAll(PDO::FETCH_ASSOC);
    if (!$results) { return array("no results"); }
    foreach ($results as $row) { 
      $response[] = array('id' => $row['id'], 'name' => $row['name']);
    }
    return $response;
  }
}


?>