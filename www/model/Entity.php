<?php

class Entity {


  public static function get($id) {
    $dbh = dbHandle(1);
    $stmt = $dbh->prepare('SELECT id,name FROM entities WHERE id=?');
    $stmt->execute(array($id));
    $row = $stmt->fetch(PDO::FETCH_ASSOC);
    if ($row) { return $row; } else { return array(); }
  }


  public static function getAll() {
    $entities = array();
    $dbh = dbHandle(1);
    $results = $dbh->query('SELECT * FROM entities WHERE name<>"" ORDER BY name');    
    if ($results) { foreach ($results as $row) { $entities[] = $row; } }
    return $entities;
  }


  public static function getAllForListing() {
    $entities = array();
    $dbh = dbHandle(1);
    $q = 'SELECT e.id,e.name,'.
         'COUNT(t.id) as transactionCount '.
       'FROM entities e '.
       'LEFT JOIN transactions t ON t.entityId=e.id '.
      'GROUP BY e.id ORDER BY e.name';
    $results = $dbh->query($q);
    $entities = $results->fetchAll(PDO::FETCH_ASSOC);
    return $entities;
  }


  public static function updateName($id, $name) { 
    $dbh = dbHandle(1);
    $stmt = $dbh->prepare('UPDATE entities SET name=:name WHERE id=:entityId');
    $stmt->bindParam(':name', $name);
    $stmt->bindParam(':entityId', $id);
    $stmt->execute();
    if ($stmt->errorCode() == 0) { return 1; } else { return 0; }
  }



  public static function add($name) {
    $dbh = dbHandle(1);
    $stmt = $dbh->prepare('SELECT id FROM entities WHERE name=:name');
    $stmt->bindParam(':name', $name);
    $stmt->execute();
    $row = $stmt->fetch(PDO::FETCH_ASSOC);
    if (isset($row['id'])) { return $row['id']; }

    $q = 'INSERT INTO entities(id,name) VALUES (0,:name)';
    $stmt = $dbh->prepare($q);
    $stmt->bindParam(':name', $name);
    $stmt->execute();
    if ($stmt->rowCount()) { return $dbh->lastInsertId(); }
    else { return -1; }
  }



  public static function delete($id) {
    try {
      $dbh = dbHandle(1);
      $stmt = $dbh->prepare('DELETE FROM entities WHERE id=?');
      $stmt->execute(array($id));
      if ($stmt->rowCount()) { return array('success'); }
      else { returnarray('error', 'nothing to delete'); }
    }
    catch (PDOException $e) {
      return array('error', $e->getMessage());
    }
  }


  public static function getAccountIdFromEntityId($entityId) {
    $dbh = dbHandle(1);
    $q = 'SELECT id FROM accounts WHERE entityId=:entityId';
    $stmt = $dbh->prepare($q);
    $stmt->bindParam('entityId', $entityId);
    $stmt->execute();
    $row = $stmt->fetch();
    if (isset($row['id'])) { return $row['id']; } else { return 0; }
  }



  public static function search($searchText) {

    // step 1 - get matching entities
    $dbh = dbHandle(1);
    $q = 'SELECT e.id, e.name FROM entities e WHERE e.name LIKE ? ORDER BY e.name LIMIT 6';
    $stmt = $dbh->prepare($q);
    $stmt->execute(array('%'.$searchText.'%'));
    $results = $stmt->fetchAll(PDO::FETCH_ASSOC);
    $entityIds = array(); $entities = array();
    foreach ($results as $row) { 
      $entityIds[] = $row['id']; 
      $entities[$row['id']] = array('id' => $row['id'], 
                                    'name' => $row['name']); 
    }
    if (empty($entityIds)) { return array("no results"); }

   
    // step 2 - retrieve the most common or recent category for each entity
    foreach ($entityIds as $entityId) { 
      $q = 'SELECT tc.categoryId, c.name as categoryName, '.
                  'COUNT(*) as cnt, MAX(t.date) as lastDate '.
           'FROM transactions t '.
           'LEFT JOIN transactionCategory tc ON t.id=tc.transactionId '.
           'LEFT JOIN categories c ON tc.categoryId=c.id '.
           'WHERE t.entityId IN (:entityId) '.
           'GROUP BY tc.categoryId '.
	'ORDER BY cnt DESC, lastDate DESC LIMIT 1';
      $stmt = $dbh->prepare($q);
      $stmt->bindParam(':entityId', $entityId);
      $stmt->execute();
      $row = $stmt->fetch(PDO::FETCH_ASSOC);
      $entities[$entityId]['lastTransactionCategoryId'] = $row['categoryId'];
      $entities[$entityId]['lastTransactionCategoryName'] = $row['categoryName'];
    }

    $retVal = array();
    foreach ($entityIds as $entityId) { $retVal[] = $entities[$entityId]; }
    return $retVal;
  }
}

?>