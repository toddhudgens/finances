<?php

class Mortgage { 


public static function getAccounts() {
  $dbh = dbHandle(1);
  $q = 'SELECT a.id, e.name 
        FROM accounts a LEFT JOIN entities e ON a.entityId=e.id 
        WHERE a.accountType=4 ORDER BY e.name';
  $results = $dbh->query($q);
  return $results;
}


public static function getPayments($id) {
  $dbh = dbHandle(1);
  $q = 'SELECT t.id, t.date, tc.amount, c.name as category
        FROM transactions t
        LEFT JOIN transactionCategory tc  ON t.id=tc.transactionId
        LEFT JOIN categories c ON tc.categoryId=c.id
        WHERE t.accountId=:id';
  $stmt = $dbh->prepare($q);
  $stmt->bindParam(':id', $id);
  $stmt->execute();
  $results = $stmt->fetchAll(PDO::FETCH_ASSOC);
  foreach ($results as $row) { $loanPayments[] = $row; }
  return $loanPayments;
}


public static function getExtraPayments($id, $pmt, $loanDetailUpdates) {
  $extraPayments = array();
  $dbh = dbHandle(1);
  $q = 'SELECT
        LEFT(t.date,7) as month,
         t.amount
        FROM transactions t
        LEFT JOIN transactionCategory tc ON t.id=tc.transactionId
        WHERE accountId=:id
        GROUP BY t.id';
  $stmt = $dbh->prepare($q);
  $stmt->bindParam(':id', $id);
  $stmt->execute();
  $results = $stmt->fetchAll(PDO::FETCH_ASSOC);
  foreach ($results as $row) {
    if (isset($loanDetailUpdates[$row['month']])) {
      $pmt = $loanDetailUpdates[$row['month']]['payment'];
    }
    if ($pmt != $row['amount']) {
      if (isset($extraPayments[$row['month']])) { $extraPayments[$row['month']] += $row['amount']; }
      else { $extraPayments[$row['month']] = $row['amount']; }
    }
  }
  return $extraPayments;
}


public static function calculateLoanData($firstPayment, $term, $rate, $balance, 
					 $monthlyPayment, $extra, $extraPayments, 
                                         $loanDetailUpdates) {

  $numberOfPayments = $term * 12;
  $monthlyRate = $rate / 12;

  $currentMonth = date("F-o", time());
  $currentMonthNumeric = date("Y-m", time());

  $month = strtotime($firstPayment);
  $loanData = array(); $totalPaid = 0; $totalInterestPaid = 0;
  
  for ($i = 0; $i < $numberOfPayments; $i++) {
    $monthNumeric = date("Y-m", $month);

    if (isset($loanDetailUpdates[$monthNumeric])) {
      $monthlyPayment = $loanDetailUpdates[$monthNumeric]['payment'];
      $monthlyRate = $loanDetailUpdates[$monthNumeric]['rate'] / 1200;
    }

    if ($monthNumeric < $currentMonthNumeric) { $extraForMonth = 0; } 
    else { $extraForMonth = $extra; }
    
    // calculating the extra payment
    // 
    // if it's the first month, add up any curtailment payments prior to the first month
    if ($i == 0) { 
      $extraPayment = 0;
      foreach ($extraPayments as $extraPaymentMonth => $extraPaymentAmount) { 
	if ($extraPaymentMonth < $monthNumeric) { $extraPayment += $extraPaymentAmount; }
	else if ($extraPaymentMonth == $monthNumeric) { 

	  // we just added up all of the curtailment payments that were made before the first
	  // monthly payment, these payment will affect the initial balance for interest reasons
	  $balance -= $extraPayment; 

	  $extraPayment = $extraPaymentAmount + $extraForMonth;
	}
      }
    }
    else if (isset($extraPayments[$monthNumeric])) { 
      $extraPayment = $extraForMonth + $extraPayments[$monthNumeric]; 
    }
    else { 

      if ($monthNumeric < $currentMonthNumeric) { 
	$extraPayment = 0; 
      }
      else {
	$extraPayment = $extraForMonth;
      }
    }

    $interest = round(abs($balance * $monthlyRate),2);
    $totalPayment = $monthlyPayment + $extraPayment;
    $principalPayment = $monthlyPayment - $interest;
    $totalPrincipalPayment = $principalPayment + $extraPayment;

    $monthLabel = date("F-o", $month);
    $loanData[] =
      array('paymentNumber' => ($i+1),
	    'monthNumeric' => $monthNumeric,
	    'month' => $monthLabel,
            'startingBalance' => $balance,
            'monthlyPayment' => $monthlyPayment,
	    'principal' => $principalPayment,
	    'interest' => $interest,
            'extra' => $extraPayment,
            'totalPayment' => $totalPayment,
	    'totalPrincipal' => $totalPrincipalPayment,
            'endingBalance' => $balance - $totalPrincipalPayment,
            'currentMonth' => ($monthLabel == $currentMonth ? 1 : 0));
    $balance -= $totalPrincipalPayment;
    $month = strtotime("+1 month", $month);

    $totalPaid += $totalPayment;
    $totalInterestPaid += $interest;

    if ($balance < 0) { break; }
  }

  return 
    array('loanData' => $loanData, 
          'totalPaid' => $totalPaid,
	  'totalInterestPaid' => $totalInterestPaid);
}


public static function getLoanDetails($accountId) {
  $dbh = dbHandle(1);
  $q = 'SELECT * FROM loanDetails WHERE accountId=:id';
  $stmt = $dbh->prepare($q);
  $stmt->bindParam(':id', $accountId);
  $stmt->execute();
  $row = $stmt->fetch(PDO::FETCH_ASSOC);
  return $row;
}


public static function getLoanDetailUpdates($accountId) {
  $dbh = dbHandle(1);
  $q = 'SELECT ldu.* FROM loanDetailUpdates ldu WHERE ldu.accountId=:id';
  $stmt = $dbh->prepare($q);
  $stmt->bindParam(':id', $accountId);
  $stmt->execute();
  $res = $stmt->fetchAll(PDO::FETCH_ASSOC);
  return $res;
}

}




?>