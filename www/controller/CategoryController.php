<?php

function search() {
  $categories = Category::search($_GET['q']);
  $response = array();

  if (count($categories)) { 
    foreach ($categories as $row) { 
      $response[] = array('id' => $row['id'], 'name' => $row['name']);
    }
  }
  else { 
    $response = array("no results");
  }
  header('Content-Type: text/plain');
  echo json_encode($response);
}


function listAll() {
  $categories = Category::getAllForListing();
  $viewParams = array('categories' => $categories);
  Twig::render('category-listing.twig', $viewParams);
}


function save() {
  $response = array('success');

  try {
    if (is_int($_GET['id'])) {
      if (!Category::update($_GET['id'], $_GET['name'])) {
        $response = array('error', 'no rows updated'); 
      }
    }
    else {
      $categoryId = Category::add($_GET['name']);
      if (!$categoryId) {
        $response = array('error', 'no rows updated');
      }
    }
  }
  catch (PDOException $e) { 
    $response = array('error', $e->getMessagee()); 
  }
  echo json_encode($response);
}

function delete() {
  if ($_GET['id'] != '') { 
    $response = Category::delete($_GET['id']);
    echo json_encode($response);
  }
  else { echo json_encode(array('error')); }
}

?>