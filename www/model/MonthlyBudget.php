<?php

class MonthlyBudget {

  public static function addItem() {
    try {
      $dbh = dbHandle(1);
      $q = 'INSERT INTO monthlyBudgetItem VALUES '.
           '(0, :name,:type,:amount,:variableAmount,:variableCategory,:lookBehind)';
      $stmt = $dbh->prepare($q);
      $stmt->bindParam(':name', $_GET['label']);
      $stmt->bindParam(':type', $_GET['type']);
      $stmt->bindParam(':amount', $_GET['amount']);
      $stmt->bindParam(':variableAmount', $_GET['autoCalculate']);
      $stmt->bindParam(':variableCategory', $_GET['categoryId']);
      $stmt->bindParam(':lookBehind', $_GET['lookBehind']);
      $stmt->execute();
      $id = $dbh->lastInsertId();

      if ($_GET['autoCalculate']) {
	MonthlyBudget::recalculateMonthlyExpense($id,
                                                 $_GET['categoryId'],
                                                 $_GET['lookBehind']);
      }

      return $id;
    }
    catch (PDOException $ex) { die($e->getMessage()); }
  }


  public static function updateItem() {
    try { 
      $dbh = dbHandle();
      $q = 'UPDATE monthlyBudgetItem SET '.
             'name=:name,'.
             'type=:type,'.
             'amount=:amount,'.
             'variableAmount=:variableAmount,'.
             'variableAmountCategoryId=:variableCategory,'.
             'variableAmountLookBehind=:lookBehind ' .
   	   'WHERE id=:id';
      $stmt = $dbh->prepare($q);
      $stmt->bindParam(':id', $_GET['itemId']);
      $stmt->bindParam(':name', $_GET['label']);
      $stmt->bindParam(':type', $_GET['type']);
      $stmt->bindParam(':amount', $_GET['amount']);
      $stmt->bindParam(':variableAmount', $_GET['autoCalculate']);
      $stmt->bindParam(':variableCategory', $_GET['categoryId']);
      $stmt->bindParam(':lookBehind', $_GET['lookBehind']);
      $stmt->execute();

      if ($_GET['autoCalculate']) { 
        MonthlyBudget::recalculateMonthlyExpense($_GET['itemId'], 
  					         $_GET['categoryId'], 
					         $_GET['lookBehind']);
      }
    }
    catch (PDOException $ex) { die($e->getMessage()); }
  }


  public static function deleteItem($id) {
    try { 
      $dbh = dbHandle();
      $q = 'DELETE FROM monthlyBudgetItem WHERE id=:id';
      $stmt = $dbh->prepare($q);
      $stmt->bindParam(':id', $id);
      $stmt->execute();
    }
    catch (PDOException $ex) { die($e->getMessage()); }
  }



  public static function getIncome() {
    $dbh = dbHandle(1);
    $q = 'SELECT mcc.*, c.name as categoryName
          FROM monthlyBudgetItem mcc 
          LEFT JOIN categories c ON mcc.variableAmountCategoryId=c.id
          WHERE mcc.type="Income"';
    $results = $dbh->query($q);
    //print_r($results);
    if ($results) { 
      $rows = $results->fetchAll(PDO::FETCH_ASSOC);
      return $rows;
    }
    else { return array(); }
  }

  public static function getFixedExpenses() {
    $dbh = dbHandle(1);
    $q = 'SELECT mcc.*, c.name as categoryName
          FROM monthlyBudgetItem mcc
          LEFT JOIN categories c ON mcc.variableAmountCategoryId=c.id
          WHERE mcc.type="Expense" AND mcc.amount>0 AND mcc.variableAmount=0
          ORDER BY mcc.amount DESC';
    $results = $dbh->query($q);
    if ($results) { 
      $rows = $results->fetchAll(PDO::FETCH_ASSOC);
      return $rows;
    }
    else { return array(); }
  }

  public static function getVariableExpenses() {
    $dbh = dbHandle();
    $q = 'SELECT mcc.*, c.name as categoryName
          FROM monthlyBudgetItem mcc
          LEFT JOIN categories c ON mcc.variableAmountCategoryId=c.id
          WHERE mcc.type="Expense" AND mcc.amount>0 AND mcc.variableAmount=1
          ORDER BY mcc.amount DESC';
    $results = $dbh->query($q);
    if ($results) { 
      $rows = $results->fetchAll(PDO::FETCH_ASSOC);
      return $rows;
    }
    else { return array(); }
  }


  public static function recalculateMonthlyExpense($id, $categoryId, $lookBehind) {
    $dbh = dbHandle();
    $q = 'SELECT ABS(SUM(tc.amount)) as amt 
          FROM transactions t, transactionCategory tc, categories c
          WHERE 
            t.id=tc.transactionId AND 
            tc.categoryId=c.id AND 
            t.date > DATE_SUB(CURRENT_TIMESTAMP, INTERVAL '.($lookBehind*30).' DAY) AND 
            tc.categoryId=?';
    $stmt = $dbh->prepare($q);
    $stmt->execute(array($categoryId));
    $row = $stmt->fetch(PDO::FETCH_ASSOC);
    $monthlyAmt = round($row['amt'] / $lookBehind, 2);

    // update the amount value
    $q2 = 'UPDATE monthlyBudgetItem SET amount=:amount WHERE id=:id';
    $stmt = $dbh->prepare($q2);
    $stmt->bindParam(':amount', $monthlyAmt);
    $stmt->bindParam(':id', $id);
    $stmt->execute();
    return $monthlyAmt;
  }


}

?>