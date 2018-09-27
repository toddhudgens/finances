<?php

function search() {
  $response = Entity::search($_GET['q']);
  header('Content-Type: text/plain');
  echo json_encode($response);
}


function listAll() {
  $entities = Entity::getAllForListing();
  $viewParams = array('entities' => $entities);
  Twig::render('entity-listing.twig', $viewParams);
}


function save() {
  $response = array('success');

  try {
    if ($_GET['id'] != '') {
      if (!Entity::updateName($_GET['id'], $_GET['name'])) {
        $response = array('error', 'no rows updated'); 
      }
    }
    else {
      $entityId = Entity::add($_GET['name']);
      if (!$entityId) {
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
    $response = Entity::delete($_GET['id']);
    echo json_encode($response);
  }
  else { echo json_encode(array('error')); }
}

?>