<?php


function calcPmt($amt,$i,$term) {
  $int = $i/1200;
  $int1 = 1+$int;
  $r1 = pow($int1, $term);
  $pmt = $amt*($int*$r1)/($r1-1);
  return $pmt;
}


function index() { 
  // retrieve account info
  $validAccount = 0;
  if (isset($_GET['id'])) { 
    $results = Account::get($_GET['id']);
    if ($results) { 
      $accountInfo = $results;
      $validAccount = 1;
    }
  }

  if ($validAccount) { 
    $loanDetails = Mortgage::getLoanDetails($_GET['id']);
    if ($loanDetails) { 
      $loanDetails['initialBalance'] = $loanDetails['purchasePrice'] - $loanDetails['downPayment'];
    }
  }
  else {
    // set some defaults
    $loanDetails = array('purchasePrice' => 150000, 
                         'downPayment' => 30000, 
                         'initialBalance' => 120000,
                         'rate' => 4,
                         'term' => 30,
                         'firstPayment' => date('Y-m-d'),
                         'payment' => calcPmt(120000,4,360));
  }


  // get loan detail updates
  $loanDetailUpdates = array();
  if ($validAccount) { 
    $res = Mortgage::getLoanDetailUpdates($_GET['id']);
    foreach ($res as $row) { 
      $monthStr = substr($row['date'], 0, 7);
      $loanDetailUpdates[$monthStr] = $row; 
    }
  }

  // retrieve info about payments made to this account
  if ($validAccount) { $loanPayments = Mortgage::getPayments($_GET['id']); }

  // retrieve info about curtailment payments made to this account
  $extraPayments = array();
  if ($validAccount) { 
    $extraPayments = Mortgage::getExtraPayments($_GET['id'], 
                                                $loanDetails['payment'], 
                                                $loanDetailUpdates); 
  }

  // set the interest rate
  if (!isset($_GET['rate'])) { 
    $rate = $loanDetails['rate'] / 100; 
    $rateForDisplay = $loanDetails['rate'];
  }
  else { 
    $rate = $_GET['rate']/100; 
    $rateForDisplay = $_GET['rate'];
  }

  // set the amount of extra payment
  if (isset($_GET['extra'])) { $extra = $_GET['extra']; } else { $extra = 0; }

  // calculate loan data for the custom schedule
  $customSchedule = Mortgage::calculateLoanData($loanDetails['firstPayment'],
  	  			      $loanDetails['term'],
				      $rate,
				      abs($loanDetails['initialBalance']),
				      $loanDetails['payment'],
				      $extra,
				      $extraPayments,
                                      $loanDetailUpdates);


  $normalSchedule = Mortgage::calculateLoanData($loanDetails['firstPayment'],
				      $loanDetails['term'],
				      ($loanDetails['rate']/100),
				      abs($loanDetails['initialBalance']),
				      $loanDetails['payment'],
				      0,
				      $extraPayments,
                                      $loanDetailUpdates);

  $maxInterest = $normalSchedule['totalInterestPaid'];



  if ($validAccount) { $accountId = $_GET['id']; } else { $accountId = ''; }
  $payoffDate = $customSchedule['loanData'][count($customSchedule['loanData'])-1]['month'];


  $loanData = $customSchedule['loanData'];

  $viewParams = array('pageTitle' => "Mortgage Calculator",
                      'currentMonth' => date('F-Y'),
                      'accounts' => Mortgage::getAccounts(),
                      'accountId' => $accountId,
                      'loanDetails' => $loanDetails,
                      'customSchedule' => $customSchedule, 
                      'rateForDisplay' => $rateForDisplay,
                      'maxInterest' => $maxInterest, 
                      'payoffDate' => $payoffDate, 
                      'extra' => $extra,
                      'loanData' => $loanData);
  echo $GLOBALS['twig']->render('mortgage-calculator.twig', $viewParams);
}

?>