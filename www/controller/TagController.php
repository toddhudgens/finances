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

?>