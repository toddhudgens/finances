<?php

function assetAllocation() {
  $assets = Asset::valuesByCategory();
  $assetJSON = array(); $totalValue = 0;
  foreach ($assets as $id => $assetInfo) {
    if ($assetInfo['totalValue'] > 300) {
      $label = $assetInfo['label'] . ' '.
	'($'.number_format($assetInfo['totalValue'],2) . ')';
      $assetJSON[] = array('label' => $label, 
			   'data' => $assetInfo['totalValue']);
    }
  }
  foreach ($assets as $a => $assetInfo) { $totalValue += $assetInfo['totalValue']; }

  $viewParams = array('pageTitle' => "Asset Allocation",
		      'totalValue' => number_format($totalValue, 2),
                      'assetJSON' => $assetJSON);
  Twig::render('asset-allocation.twig', $viewParams);
}



function liquidAssetAllocation() {
  $assets = Asset::valuesByCategory($liquid=1);

  $assetJSON = array(); $totalValue = 0;
  foreach ($assets as $id => $assetInfo) {
    if ($assetInfo['totalValue'] > 250) {
      $label = $assetInfo['label'].' '.
	'($'.number_format($assetInfo['totalValue'],2) . ')';
      $assetJSON[] = array('label' => $label,
			   'data' => $assetInfo['totalValue']);
    }
  }
  foreach ($assets as $a => $assetInfo) { $totalValue += $assetInfo['totalValue']; }

  $viewParams = array('pageTitle' => "Liquid Asset Allocation",
                      'totalValue' => number_format($totalValue, 2),
                      'assetJSON' => $assetJSON);
  Twig::render('liquid-asset-allocation.twig', $viewParams);
}



function expensesByCategory() {
  $categoryId = ''; $range = ''; $groupBy = ''; 
  if (isset($_GET['categoryId'])) { $categoryId = $_GET['categoryId']; } 
  if (isset($_GET['range'])) { $range = $_GET['range']; } 
  if (isset($_GET['groupBy'])) { $groupBy = $_GET['groupBy']; } 
  if (isset($_REQUEST['showAllTimePeriods'])) { $showAllTimePeriods = 1; } else { $showAllTimePeriods = 0; }

  $chartData = ''; $expenses = array();
  if ($categoryId != '') { 
    $expenses = Transaction::expensesByCategory($categoryId, $range, $groupBy);
    $chartData = 
      Report::buildJSONDataForExpenseReport($expenses, $range, $groupBy);
  }

  if (count($expenses) && ($categoryId != '')) {
    $entityExpenses = Report::categoryExpensesByEntity($categoryId, $range);
  }

  if (isset($entityExpenses)) {
    $categoryName = Category::getName($categoryId);
    $subtitle = $categoryName . ' Transactions By Entity';
  }
  else { $subtitle = ''; }

  // calculate entity expenses
  if (isset($entityExpenses)) { 
    $expenseForEntity = array();
    foreach ($entityExpenses as $i => $info) { 
      $expenseForEntity[$info['entityName']] = $info['total'];
    }
    arsort($expenseForEntity);
    $topEntities = array();
    
    $i = 0;
    foreach ($expenseForEntity as $entityName => $entityTotal) { 
      $i++;
      if ($i > 10) { continue; }
      else { 
        $topExpenses[] = array('label' => $entityName . ' ($'.intval($entityTotal).')',
                               'data' => doubleval($entityTotal));
      }
    }
  }
  else { 
    $entityExpenses = array(); 
    $topExpenses = array(); 
  }

  $viewParams = array('pageTitle' => 'Expenses by Category',
                      'subtitle' => $subtitle,
                      'expenses' => $expenses,
                      'entityExpenses' => $entityExpenses, 
                      'topExpenses' => $topExpenses,
		      'chartData' => $chartData,
                      'grouping' => $groupBy,
                      'range' => $range,
                      'selectedCategory' => $categoryId,
                      'showAllTimePeriods' => $showAllTimePeriods, 
                      'categories' => Category::getAll());
  Twig::render('expenses-by-category.twig', $viewParams);
}




function expensesByEntity() {
  if (isset($_GET['entityId'])) { $entityId = $_GET['entityId']; } else { $entityId = ''; }
  if (isset($_GET['range'])) { $range = $_GET['range']; } else { $range = ''; }
  if (isset($_GET['groupBy'])) { $groupBy = $_GET['groupBy']; } else { $groupBy = ''; }
  if (isset($_REQUEST['showAllTimePeriods'])) { $showAllTimePeriods = 1; } else { $showAllTimePeriods = 0; }

  $expenses = array(); $chartdata = '';
  if ($entityId != '') { 
    $expenses = Transaction::expensesByEntity($entityId, $range, $groupBy);
    $chartdata = Report::buildJSONDataForExpenseReport($expenses, $range, $groupBy);
  }

  $viewParams = array('pageTitle' => 'Expenses by Entity',
                      'expenses' => $expenses,
                      'chartData' => $chartdata,
                      'grouping' => $groupBy,
                      'range' => $range,
                      'selectedEntity' => $entityId,
                      'showAllTimePeriods' => $showAllTimePeriods,
                      'entities' => Entity::getAll());
  Twig::render('expenses-by-entity.twig', $viewParams);
}



function expensesByTag() {
  if (isset($_GET['tagId'])) { $tagId = $_GET['tagId']; } else { $tagId = ''; }
  if (isset($_GET['range'])) { $range = $_GET['range']; } else { $range = ''; }
  if (isset($_GET['groupBy'])) { $groupBy = $_GET['groupBy']; } else { $groupBy = ''; }
  if (isset($_REQUEST['showAllTimePeriods'])) { $showAllTimePeriods = 1; } else { $showAllTimePeriods = 0; }

  if ($tagId != '') {
    $expenses = Transaction::expensesByTag($tagId, $range, $groupBy);
    $chartdata = Report::buildJSONDataForExpenseReport($expenses, $range, $groupBy);
  }
  else { $expenses = array(); $chartdata = ''; }

  $viewParams = array('pageTitle' => 'Expenses by Tag',
                      'expenses' => $expenses,
                      'chartData' => $chartdata,
                      'grouping' => $groupBy,
                      'range' => $range,
                      'selectedTag' => $tagId,
                      'showAllTimePeriods' => $showAllTimePeriods, 
                      'tags' => Tag::getAll());
  Twig::render('expenses-by-tag.twig', $viewParams);
}



function networth() {
  $results = Networth::getLogEntries();
  if (!$results) {
    Networth::update();
    $results = Networth::getLogEntries();
  }
  
  $networthLog = array(); $first = '';
  foreach ($results as $row) { 
    if ($first == '') { $first = $row['ts']; }
    $networthLog[] = array($row['ts'], $row['value']);
  }
  if ($first == '') { $from = date('Y-m-d'); }
  else { $from = date('Y-m-d', $first/1000); }
  
  $viewParams = array('pageTitle' => "Net Worth Report",
		      'to' => date('Y-m-d'),
		      'from' => $from,
                      'networthLog' => $networthLog);
  Twig::render('networth-report.twig', $viewParams);
}



function gasPrices() {
  $rawData = Automobile::getGasPriceReportData();

  $gasPriceLog = array(); $first = ''; 
  foreach ($rawData as $row) {
    if ($first == '') { $first = $row['ts']; }
    $gasPriceLog[] = array($row['ts'], $row['gasPrice']);
  }

  if ($first == '') { $from = date('Y-m-d'); }
  else { $from = date('Y-m-d', $first/1000); }

  $viewParams = array('pageTitle' => 'Gas Price Report',
                      'to' => date('Y-m-d'),
                      'from' => $from,
                      'gasPriceData' => $gasPriceLog);
  Twig::render('gas-price-report.twig', $viewParams);
}

?>