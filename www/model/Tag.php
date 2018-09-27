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


  public static function getAllForListing() {
    $tags = array();
    $dbh = dbHandle(1);
    $q = 'SELECT t.id,t.name,'.
         'COUNT(tm.tagId) as transactionCount '.
       'FROM tags t '.
       'LEFT JOIN tagMapping tm ON t.id=tm.tagId '.
      'GROUP BY t.id ORDER BY t.name';
    $results = $dbh->query($q);
    $tags = $results->fetchAll(PDO::FETCH_ASSOC);
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



  public static function update($id, $name) {
    $dbh = dbHandle(1);
    $stmt = $dbh->prepare('UPDATE tags SET name=:name WHERE id=:id');
    $stmt->bindParam(':name', $name);
    $stmt->bindParam(':id', $id);
    $stmt->execute();
    if ($stmt->errorCode() == 0) { return 1; } else { return 0; }
  }



  public static function delete($id) {
    try {
      $dbh = dbHandle(1);
      $stmt = $dbh->prepare('DELETE FROM tags WHERE id=?');
      $stmt->execute(array($id));
      if ($stmt->rowCount()) { 
	$q = 'DELETE FROM tagMapping WHERE tagId=?';
	$stmtTwo = $dbh->prepare($q);
	$stmtTwo->execute(array($id));

        return array('success'); 
      }
      else { returnarray('error', 'nothing to delete'); }
    }
    catch (PDOException $e) {
      return array('error', $e->getMessage());
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