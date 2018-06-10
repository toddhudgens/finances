<?php



function delete() {
  $response = Asset::delete($_GET['id']);
  echo json_encode($response);
}


function search() {
  $response = Asset::search($_GET['q']);
  header('Content-Type: text/plain');
  echo json_encode($response);
}



function save() {
  $response = array('success');

  try { 
    if ($_GET['mode'] == "edit") {
      if (Asset::update()) { 
        Plugins::run('assetUpdate', array($_REQUEST['assetId']));
      }
      else { $response = array('error', 'no rows updated'); }
    }
    else if ($_GET['mode'] == "new") {
      $assetId = Asset::add();
      if ($assetId) { 
        Plugins::run('assetCreate', array($_REQUEST['assetId']));
      }
    }
  }
  catch (PDOException $e) { $response = array('error', $e->getMessage()); }
  echo json_encode($response);
}



function listAll() {
  $assets = Asset::getAllByCategory();
  $categories = array();
  $assetEditFields = ''; 
  
  Plugins::run('assetListingCustomizations', array(&$assets));
  Plugins::run('assetCategoryExtraInfo', array(&$categories));
  Plugins::run('assetEditFields', array(&$assetEditFields));
  $countries = Country::getAll();

  Twig::render('assets-listing.twig', 
               array('assets' => $assets,
                     'assetEditFields' => $assetEditFields,
		     'categoryDetails' => $categories,
                     'countries' => $countries));
}


?>