<?php


function maintenanceInfo() {
  $info = Automobile::getMaintenanceInfo($_REQUEST['transactionId']);
  echo json_encode($info);
}


function gasMileageInfo() { 
  $info = Automobile::getGasMileageInfo($_REQUEST['transactionId']);
  echo json_encode($info);
}


function taxInfo() {
  $info = Automobile::getTaxInfo($_REQUEST['transactionId']);
  echo json_encode($info);
}


function insuranceInfo() {
  $info = Automobile::getInsuranceInfo($_REQUEST['transactionId']);
  echo json_encode($info);
}


function updateMaintenanceNotes() {
  $res = Automobile::updateMaintenanceNotes($_POST['id'], $_POST['notes']);
  echo $res;
}


function index() {
  $catId = Automobile::getCategoryId();
  $assets = Asset::getForCategory($catId);
  echo $GLOBALS['twig']->render('automobile-overview.twig',
				array('assets' => $assets));
}


function gasMileage() {
  global $title; 
  $vehicleInfo = Asset::get($_GET['id']);
  $title = $vehicleInfo['name'] . ' - Gas Mileage Report';
  $mileageInfo = Automobile::getGasMileage($_GET['id']);
  $lastReading = 0;
  $totals = array('milesDriven' => 0, 'gasPumped' => 0, 'totalSpent' => 0);
  foreach ($mileageInfo as $i => $log) { 
    if ($i > 0) { 
      $milesDriven = $log['mileage'] - $lastReading;
      $mileageInfo[$i]['milesDriven'] = $milesDriven;
      $totals['milesDriven'] += $milesDriven; 
      $mpg = round($milesDriven / $log['gasPumped'],1);
      $mileageInfo[$i]['mpg'] = $mpg;
    }
    else { 
      $mileageInfo[$i]['milesDriven'] = '--';
      $mileageInfo[$i]['mpg'] = '--'; 
    }
    $mileageInfo[$i]['amount'] = abs($log['amount']);
    $totals['gasPumped'] += $log['gasPumped'];
    $totals['totalSpent'] += abs($log['amount']);
    $lastReading = $log['mileage'];    
  }
  if ($totals['gasPumped'] > 0) { 
    $totals['averageMPG'] = round($totals['milesDriven'] / $totals['gasPumped'], 1);
  }
  else { $totals['averageMPG'] = 0; }
  
  if ($totals['milesDriven'] > 0) {
    $totals['costPerMile'] = round(($totals['totalSpent'] / $totals['milesDriven'] * 100), 1);
  }
  else { $totals['costPerMile'] = 0; }

  echo $GLOBALS['twig']->render('automobile-mileage-log.twig',
				array('id' => $_GET['id'],
				      'automobiles' => Automobile::getAll(),
				      'pageTitle' => $title,
                                      'info' => $vehicleInfo,
				      'mileageInfo' => $mileageInfo,
				      'totals' => $totals));
}



// vehicle maintenance report
function maintenance() { 
  global $title;
  //die($_GET['view']);
  $vehicleInfo = Automobile::getInfo($_GET['id']);
  if (isset($_GET['view']) && ($_GET['view'] == "log")) { 
    $view = $_GET['view']; 
    $viewScript = 'automobile-log.twig';
    $title = $vehicleInfo['name'] . ' Log';
  } 
  else { 
    $view = 'maintenance'; 
    $viewScript = 'automobile-maintenance-log.twig';
    $title = $vehicleInfo['name'] . ' Maintenance Log';
  }
  $maintenanceLog = Automobile::getVehicleLog($_GET['id'], $view);

  $totals = array('totalSpent' => 0, 'milesLogged' => 0);
  $lowestOdo = 999999999; $highestOdo = -1;
  foreach ($maintenanceLog as $i => $log) { 
    $totals['totalSpent'] += abs($log['amount']);
    if ($log['mileage'] != '-') { 
      if ($log['mileage'] < $lowestOdo) { $lowestOdo = $log['mileage']; }
      if ($log['mileage'] > $highestOdo) { $highestOdo = $log['mileage']; }
    }
    $maintenanceLog[$i]['amount'] = abs($log['amount']);
  } 
  $totals['milesLogged'] = ($highestOdo - $lowestOdo);
  $totals['costPerMile'] = ($totals['totalSpent'] / $totals['milesLogged']);
  echo $GLOBALS['twig']->render($viewScript,
                                array('id' => $_GET['id'],
				      'view' => $view, 
                                      'automobiles' => Automobile::getAll(),
                                      'pageTitle' => $title,
                                      'info' => $vehicleInfo,
                                      'maintenanceLog' => $maintenanceLog,
                                      'totals' => $totals));

}



function tco() {
  if (!isset($_GET['id'])) { exit; }
  $id = $_GET['id'];
  addCSS('report.css');
  $vehicleInfo = Automobile::getInfo($id);
  global $title;
  $title = $vehicleInfo['name'] . ' - TCO Report';

  $totals = array();
  $totals['start'] = $vehicleInfo['startingOdometer']; 
  $totals['end'] = Automobile::getLatestMileage($id);
  $totals['milesDriven'] = $totals['end'] - $totals['start'];
  $tco = Automobile::getTCO($id);

  if ($totals['milesDriven'] > 0) { 
    $totals['costPerMile'] = round(($tco['totalCost'] / $totals['milesDriven'] * 100), 1);
  }
  else { $totals['costPerMile'] = 0; }

  $metrics = array('depreciation', 'interest', 'fuel', 'insurance', 'maintenance', 'taxes');
  $chartData = array();
  for ($i = 0; $i < count($metrics); $i++) { 
    $key = $metrics[$i];
    $label = ucwords($key) . ' ($'.$tco[$key].')';
    $chartData[] = array('label' => $label, 'data' => $tco[$key]);
  }
  echo $GLOBALS['twig']->render('automobile-tco-report.twig',
                                array('id' => $_GET['id'],
                                      'automobiles' => Automobile::getAll(),
                                      'pageTitle' => $title,
				      'chartData' => $chartData,
				      'tco' => $tco,
                                      'totals' => $totals));
}

?>