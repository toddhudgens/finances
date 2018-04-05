<?php

class Asset {


  public static function get($id) { 
    $dbh = dbHandle(1);
    $stmt = $dbh->prepare('SELECT a.* FROM assets a WHERE a.id=?');
    $stmt->execute(array($id));
    $results = $stmt->fetchAll(PDO::FETCH_ASSOC);
    if ($results) {
      foreach ($results as $row) { return $row; }
    }
    else { return null; }
  }



  public static function getAllByCategory() {
    try {
      $dbh = dbHandle(1);
      $q = 'SELECT a.id, a.name, a.initialValue, a.currentValue, 
              a.notes, a.picture,
              c.name as category, a.countryId,
              c.id as categoryId, cn.name as countryName
            FROM assets a
            LEFT JOIN categories c ON a.categoryId=c.id
            LEFT JOIN countries cn ON a.countryId=cn.id
            WHERE a.sold=0
            ORDER BY c.name, a.name';
      $results = $dbh->query($q);
      $assets = $results->fetchAll(PDO::FETCH_ASSOC);
      $assetsByCategory = array();
      foreach ($assets as $i => $assetInfo) {
        $cName = $assetInfo['category'];
        $assetsByCategory[$cName][] = $assetInfo;
      }
      return $assetsByCategory;
    }
    catch (PDOException $e) {}
  }



  public static function search($searchText) {
    $dbh = dbHandle(1);
    $q = 'SELECT a.id, a.name FROM assets a
          WHERE a.name LIKE ? ORDER BY a.name LIMIT 6';
    $stmt = $dbh->prepare($q);
    $stmt->execute(array('%'.$searchText.'%'));
    $results = $stmt->fetchAll(PDO::FETCH_ASSOC);
    $response = array();
    if (count($results)) { 
      foreach ($results as $row) { 
	$response[] = array('id' => $row['id'], 'name' => $row['name']);
      }
      return $response;
    }
    else { return array('no results'); }
  }


  public static function add() {
    if ($_GET['category'] == "") { $categoryId = Category::add($_GET['categoryName']); }
    else { $categoryId = $_GET['category']; }

    if ($_GET['datePurchased'] == "") { $datePurchased = null; }
    else { $datePurchased = $_GET['datePurchased']; }
    if ($_GET['dateSold'] == "") { $dateSold = null; }
    else { $dateSold = $_GET['dateSold']; }

    try { 
      $dbh = dbHandle(1);
      $q = 'INSERT INTO assets VALUES '.
           '(0, :name,:categoryId,:purchasePrice,:currentValue,1,:notes,:madeIn,:picture,0,'.
	   ':datePurchased,:dateSold)';
      $stmt = $dbh->prepare($q);
      $stmt->bindParam(':name', $_GET['name']);
      $stmt->bindParam(':categoryId', $categoryId);
      $stmt->bindParam(':purchasePrice', $_GET['purchasePrice']);
      $stmt->bindParam(':currentValue', $_GET['currentValue']);
      $stmt->bindParam(':notes', $_GET['notes']);
      $stmt->bindParam(':picture', $_GET['picture']);
      $stmt->bindParam(':madeIn', $_GET['madeIn']);
      $stmt->bindParam(':datePurchased', $datePurchased);
      $stmt->bindParam(':dateSold', $dateSold);
      $stmt->execute();
      $assetId = $dbh->lastInsertId();
      return $assetId;
    }
    catch (PDOException $ex) { die($e->getMessage()); }
  }


  public static function update() {
    if ($_GET['category'] == "") { $categoryId = Category::add($_GET['categoryName']); }
    else { $categoryId = $_GET['category']; }

    try { 
      $dbh = dbHandle(1);
      $q = 'UPDATE assets
            SET name=:name,
                categoryId=:categoryId,
                currentValue=:currentValue,
                initialValue=:purchasePrice,
                picture=:picture,
                notes=:notes,
                countryId=:madeIn
            WHERE id=:id';
      $stmt = $dbh->prepare($q);
      $stmt->bindParam(':name', $_GET['name']);
      $stmt->bindParam(':categoryId', $categoryId);
      $stmt->bindParam(':currentValue', $_GET['currentValue']);
      $stmt->bindParam(':purchasePrice', $_GET['purchasePrice']);
      $stmt->bindParam(':picture', $_GET['picture']);
      $stmt->bindParam(':notes', $_GET['notes']);
      $stmt->bindParam(':madeIn', $_GET['madeIn']);
      $stmt->bindParam(':id', $_GET['assetId']);
      $stmt->execute();
      if ($stmt->rowCount()) { return 1; }
    }
    catch (PDOException $ex) { die($e->getMessage()); }
  }


  public static function updateCurrentValue($assetId, $value) {
    $dbh = dbHandle(1);
    $q = 'UPDATE assets SET currentValue=:value WHERE id=:assetId';
    $stmt = $dbh->prepare($q);
    $stmt->bindParam(':value', $value);
    $stmt->bindParam(':assetId', $assetId);
    $stmt->execute();
  }


  public static function delete($id) {
    try {
      $dbh = dbHandle(1);
      $stmt = $dbh->prepare('DELETE FROM assets WHERE id=?');
      $stmt->execute(array($_GET['id']));
      if ($stmt->rowCount()) { return array('success'); }
      else { returnarray('error', 'nothing to delete'); }
    }
    catch (PDOException $e) {return array('error', $e->getMessage()); }
  }


  public static function totalValue() {
    try {
      $dbh = dbHandle(1);
      $results = $dbh->query('SELECT SUM(currentValue) as assetValue FROM assets WHERE sold=0');
      foreach ($results as $row) { return $row['assetValue']; }
    }
    catch (PDOException $e) {}
    
    return 0;
  }


  public static function valuesByCategory($liquid='') {
    $where = ''; 
    if ($liquid == 1) { $where = ' AND a.liquid=1'; }

    $dbh = dbHandle(1);
    $q = 'SELECT c.name as category,SUM(a.currentValue) as totalValue
          FROM assets a, categories c
          WHERE a.sold=0 AND a.categoryId=c.id '. $where .'
          GROUP BY c.id
           ORDER BY totalValue DESC';
    $results = $dbh->query($q);
    if ($results) { 
      foreach ($results as $row) {
        $row['label'] = Category::buildLabel($row);
        $assets[] = $row;
      }
    }

    // add accounts to asset allocation. if it's tied to a asset we can 
    // update the balance by removing the debt value
    $q = 'SELECT a.id, a.assetId, a.balance, e.name
          FROM accounts a 
          LEFT JOIN entities e ON a.entityId=e.id 
          WHERE 1=1 ' . $where;
    $results = $dbh->query($q);
    foreach ($results as $row) { 
      if ($row['assetId'] > 0) {
        foreach ($assets as $id => $assetInfo) {
          if ($assetInfo['id'] == $row['assetId']) {
            $assets[$id]['totalValue'] += $row['balance'];
          }
        }
      }
      else if ($row['balance'] > 0) {
        $row['totalValue'] = $row['balance'];
        $row['label'] = $row['name'];
        $assets[] = $row;
      }
    }

    // sort by value
    $value = array(); 
    foreach ($assets as $key => $row) { $value[$key] = $row['totalValue']; }
    array_multisort($value, SORT_DESC, $assets);

    return $assets;
  }


  public static function getForCategory($catId) { 
    $dbh = dbHandle(1);
    $stmt = $dbh->prepare('SELECT * FROM assets WHERE categoryId=?');
    $stmt->execute(array($catId));
    $results = $stmt->fetchAll(PDO::FETCH_ASSOC);
    $automobiles = array();
    foreach ($results as $row) { $automobiles[] = $row; }
    return $automobiles;
  }

}




?>