<?php


function delete() {
  $response = Transaction::delete($_GET['transactionId']);
  echo json_encode($response);
}



function search() {
  addJS('user/index.js');
  addCSS(array("user/index.css",'account.css'));
  $transactions = Transaction::search($_GET['s']);
  $viewParams = array('pageTitle' => "Transactions matching: ".$_GET['s'],
                      'searchString' => $_GET['s'],
                      'transactions' => $transactions);
  Twig::render('transaction-search-results.twig', $viewParams);
}



function save() {
  $txType = $_REQUEST['transactionType'];
  if ($txType == "Stock Purchase") { $response = Stocks::purchase(); }
  else if ($txType == "Stock Sale") { $response = Stocks::sale(); }
  else if ($txType == "Transfer") { $response = Transaction::saveTransfer(); }
  else {  $response = Transaction::save(); } // deposits & withdrawals
  echo json_encode($response);
}


?>