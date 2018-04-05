<?php

class Expenses {

public static function getIncome() {
  $dbh = dbHandle(1);
  $results = $dbh->query('SELECT * FROM monthlyCashflow WHERE type="Income"');
  $rows = $results->fetchAll(PDO::FETCH_ASSOC);
  return $rows;
}

public static function getFixedExpenses() {
  $dbh = dbHandle(1);
  $q = 'SELECT * FROM monthlyCashflow 
        WHERE type="Expense" AND amount>0 AND variableAmount=0 
        ORDER BY amount DESC';
  $results = $dbh->query($q);
  $rows = $results->fetchAll(PDO::FETCH_ASSOC);
  return $rows;
}

public static function getVariableExpenses() {
  $dbh = dbHandle(1);
  $q = 'SELECT * FROM monthlyCashflow 
        WHERE type="Expense" AND amount>0 AND variableAmount=1 
        ORDER BY amount DESC';
  $results = $dbh->query($q);
  $rows = $results->fetchAll(PDO::FETCH_ASSOC);
  return $rows;
}


public static function calculateMonthlyExpense($row) {
  $id = $row['id'];
  $months = $row['variableAmountLookBehind'];
  $categoryId = $row['variableAmountCategoryId'];

  $dbh = dbHandle(1);
  $q =
   'SELECT
    ABS(SUM(tc.amount)) as amt
    FROM
      transactions t,
      transactionCategory tc,
      categories c
    WHERE
      t.id=tc.transactionId AND
      tc.categoryId=c.id AND
      t.date > DATE_SUB(CURRENT_TIMESTAMP, INTERVAL '.($months*30).' DAY) AND
      tc.categoryId=?';
  $stmt = $dbh->prepare($q);
  $stmt->execute(array($categoryId));
  $results = $stmt->fetchAll(PDO::FETCH_ASSOC);
  foreach ($results as $row) {
    $monthlyAmt = round($row['amt'] / $months,2);
  }

  // update the amount value
  $q2 = 'UPDATE monthlyCashflow SET amount=:amount WHERE id=:id';
  $stmt = $dbh->prepare($q2);
  $stmt->bindParam(':amount', $monthlyAmt);
  $stmt->bindParam(':id', $id);
  $stmt->execute();

  return $monthlyAmt;
}

}

?>