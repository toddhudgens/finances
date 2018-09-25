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


?>