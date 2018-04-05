<?php


function search() {
  $response = Entity::search($_GET['q']);
  header('Content-Type: text/plain');
  echo json_encode($response);
}

?>