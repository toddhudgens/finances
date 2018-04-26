<?php


function index() {
  $income = MonthlyBudget::getIncome();
  $fixedExpenses = MonthlyBudget::getFixedExpenses();
  $variableExpenses = MonthlyBudget::getVariableExpenses();

  $combinedItems = array_merge($income, $fixedExpenses, $variableExpenses);
  $itemsById = array();
  foreach ($combinedItems as $id => $info) { $itemsById[$info['id']] = $info; }

  $viewParams = array('pageTitle' => "Monthly Budget",
                      'income' => $income,
                      'fixedExpenses' => $fixedExpenses,
                      'variableExpenses' => $variableExpenses, 
                      'items' => $itemsById);
  echo $GLOBALS['twig']->render('monthly-budget.twig', $viewParams);
}


function saveItem() {
  $response = array('success');

  try {
    if ($_GET['itemId'] != "") { MonthlyBudget::updateItem(); }
    else { MonthlyBudget::addItem(); }
  }
  catch (PDOException $e) { $response = array('error', $e->getMessage()); }
  echo json_encode($response);
}


?>