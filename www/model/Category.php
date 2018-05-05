<?php

class Category { 


public static function get($id) {
  $dbh = dbHandle(1);
  $q = 'SELECT id,name FROM categories WHERE id=? LIMIT 1';
  $stmt = $dbh->prepare($q);
  $stmt->execute(array($id));
  $results = $stmt->fetchAll(PDO::FETCH_ASSOC);
  if ($results) { foreach ($results as $row) { return $row; }}
  else { return array(); }
}


public static function getAll() {
  $categories = array();
  $dbh = dbHandle(1);
  $results = $dbh->query('SELECT * FROM categories ORDER BY name');
  $categories = $results->fetchAll(PDO::FETCH_ASSOC);
  return $categories;
}


public static function getName($id) {
  $dbh = dbHandle(1);
  $q = 'SELECT name FROM categories WHERE id=?';
  $stmt = $dbh->prepare($q);
  $stmt->execute(array($id));
  $results = $stmt->fetchAll(PDO::FETCH_ASSOC);
  if ($results) { 
    foreach ($results as $row) { return $row['name']; }
  }
  else { return ''; }
}



public static function search($q) {
  try {
    $dbh = dbHandle(1);
    $q = 'SELECT c.id, c.name FROM categories c
          WHERE c.name LIKE ? ORDER BY c.name LIMIT 6';
    $stmt =$dbh->prepare($q);
    $stmt->execute(array('%'.$_GET['q'].'%'));
    $results = $stmt->fetchAll(PDO::FETCH_ASSOC);
    return $results; 
  }
  catch (PDOException $e) {}
}



public static function add($name) {
  if ($name == "") { return -1; }
  $dbh = dbHandle(1);
  $stmt = $dbh->prepare('SELECT id FROM categories WHERE name=?');
  $stmt->execute(array($name));
  $row = $stmt->fetch(PDO::FETCH_ASSOC);
  if ($row) { return $row['id']; }
  else { 
    $stmt = $dbh->prepare('INSERT INTO categories(id,name) VALUES(0,?)');
    $stmt->execute(array($name));
    if ($stmt->rowCount()) { return $dbh->lastInsertId(); }
    else { return -1; }
  }
}


public static function buildLabel($row) {
  if (isset($row['subcategory']) && ($row['subcategory'] != "")) {
    return $row['category'] . ' - ' . $row['subcategory'];
  }
  else { return $row['category']; }
}



function getSubmittedCategories() {
  if (isset($_REQUEST['category']) && ($_REQUEST['category'] != '')) {
    return array($_REQUEST['category']);
  }
  else {
    $categories = array();
    for ($i = 1; $i < 6; $i++) {
      if (isset($_REQUEST['category'.$i]) && ($_REQUEST['category'.$i] != '')) {
        $categories[] = $_REQUEST['category'.$i];
      }
    }
    return $categories;
  }
}


public static function buildLink($cNames, $cIds, $timefilter) {
  $categories = explode(",", $cNames);
  $categoryIds = explode(",", $cIds);
  $link = '';
  for ($c = 0; $c < count($categories); $c++) {
    if ($c > 0) { $link .= ', '; }
    $link .= '<a href="/account/show-transactions?categoryId='.$categoryIds[$c].$timefilter.'">' .
              trim($categories[$c]).'</a>';
  }
  return $link;
}



}

?>