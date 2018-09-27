<?php

function add() {
  $response = Tag::add($_GET['name']);
  header('Content-Type: text/plain');
  echo json_encode($response);
}


function search() {
  $response = Tag::search($_GET['q']);
  header('Content-Type: text/plain');
  echo json_encode($response);
}


function listAll() {
  $tags = Tag::getAllForListing();
  $viewParams = array('tags' => $tags);
  Twig::render('tag-listing.twig', $viewParams);
}


function save() {
  $response = array('success');

  try {
    if ($_GET['id'] != '') {
      if (!Tag::update($_GET['id'], $_GET['name'])) {
        $response = array('error', 'no rows updated');
      }
    }
    else {
      $tagId = Tag::add($_GET['name']);
      if (!$tagId) {
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
    $response = Tag::delete($_GET['id']);
    echo json_encode($response);
  }
  else { echo json_encode(array('error')); }
}

?>