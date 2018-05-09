<?php

class Transaction {

  public static function insertNew($accountId, $payeeId, $total, $tax, $txType) {
    if ($_REQUEST['interest'] == "") { $interest = 0; } else { $interest = $_REQUEST['interest']; }

    $dbh = dbHandle();
    $q = 'INSERT INTO transactions 
          (accountId,entityId,date,amount,tax,interest,transactionNumber,
           notes,transactionType, created) VALUES(
            :accountId, :payeeId, :date, :total, :tax, :interest,
            :transactionNum, :notes, :transactionType, 
           CURRENT_TIMESTAMP)';
    $stmt = $dbh->prepare($q);
    $stmt->bindParam(':accountId', $accountId);
    $stmt->bindParam(':payeeId', $payeeId);
    $stmt->bindParam(':date', $_REQUEST['date']);
    $stmt->bindParam(':total', $total);
    $stmt->bindParam(':tax', $tax);
    $stmt->bindParam(':interest', $interest);
    $stmt->bindParam(':transactionNum', $_REQUEST['transactionNumber']);
    $stmt->bindParam(':notes', $_REQUEST['notes']);
    $stmt->bindParam(':transactionType', $txType);
    $stmt->execute();
    if ($stmt->rowCount()) { return $dbh->lastInsertId(); } 
    else { return null; }
  }



  public static function update($id, $accountId, $payeeId, $total, $tax, $txType) { 
    $dbh = dbHandle();
    $q = 'UPDATE transactions SET
            transactionNumber=:txNum,
            accountId=:accountId,
            entityId=:entityId, 
            date=:date,
            amount=:amount,
            tax=:tax,
            interest=:interest,
            notes=:notes 
          WHERE id=:txId';
    $stmt = $dbh->prepare($q);
    $stmt->bindParam(':txNum', $_REQUEST['transactionNumber']);
    $stmt->bindParam(':accountId', $accountId);
    $stmt->bindParam(':entityId', $payeeId);
    $stmt->bindParam(':date', $_REQUEST['date']);
    $stmt->bindParam(':amount', $total);
    $stmt->bindParam(':interest', $_REQUEST['interest']);
    $stmt->bindParam(':tax', $tax);
    $stmt->bindParam(':notes', $_REQUEST['notes']);
    $stmt->bindParam(':txId', $id);
    $stmt->execute();
    if ($stmt->rowCount()) { return $id; } 
    else { return 0; }
  }



  public static function updatePairedTransaction($depositTxId, $withdrawTxId) { 
    $dbh = dbHandle(1);

    // set the transaction pairs
    $q = 'UPDATE transactions SET pairedTransaction=:pairedTransaction
          WHERE id=:withdrawTxId';
    $stmt = $dbh->prepare($q);
    $stmt->bindParam(':pairedTransaction', $depositTxId);
    $stmt->bindParam(':withdrawTxId', $withdrawTxId);
    $stmt->execute();
  }


  public static function save() {
    if ($_REQUEST['tax'] == "") { $tax = 0; } else { $tax = $_REQUEST['tax']; }

    $txType = $_REQUEST['transactionType'];
    if ($txType == "Withdrawal") { $total = abs($_REQUEST['total'])*-1; } 
    else { $total = abs($_REQUEST['total']); }

    if (($_REQUEST['payee'] == "") && ($_REQUEST['payeeName'] != '')) {
      $payeeId = Entity::add($_REQUEST['payeeName']);
    }
    else { $payeeId = $_REQUEST['payee']; }
    if ($total < 0) { $withdrawal = 1; } else { $withdrawal = 0; }

    if ($_REQUEST['mode'] == "edit") {
      $transactionId = $_REQUEST['transactionId'];
      $accountId = $_REQUEST['accountId'];
      Transaction::update($transactionId, $accountId, $payeeId, $total, $tax,
                          $_REQUEST['transactionType']);
      $exists = 1;
      $reflectionMethod = 'transactionUpdate';
    }
    else if ($_REQUEST['mode'] == "new") {
      $transactionId = Transaction::insertNew($_REQUEST['accountId'],
					      $payeeId, $total, $tax, $txType);
      $exists = 0;
      $reflectionMethod = 'transactionCreate';
      $response = array('result' => 'success', 'transactionId' => $transactionId);
    }

    if ($transactionId != '') {
      // TODO:: IF DATE WAS CHANGED (FORWARD), WE NEED TO USE THE OLD DATE HERE
      Account::updateAccountBalance($_REQUEST['accountId'], $_REQUEST['date']);
      Transaction::updateCategories($transactionId, $exists, $withdrawal);
      Transaction::updateTags($transactionId);
      Plugins::run($reflectionMethod, array($transactionId));
      return array('result' => 'success', 'transactionId' => $transactionId);
    }
    else { return array('result' => 'error'); }
  }



  public static function saveTransfer() {
    $accounts = Account::getAll();
    $total = abs($_REQUEST['total']);
    if ($_REQUEST['tax'] == "") { $tax = 0; } else { $tax = $_REQUEST['tax']; }

    $fromAccountId = $_REQUEST['fromAccount'];
    $fromAccount = $accounts[$fromAccountId];
    $toAccountId = $_REQUEST['toAccount'];
    $toAccount = $accounts[$toAccountId];

    // new transfer
    if ($_REQUEST['mode'] == "new") {
      $withdrawTxId = Transaction::insertNew($fromAccountId,
					     $toAccount['entityId'], ($total*-1), $tax, 'Transfer');

      Transaction::updateCategories($withdrawTxId, $exists=0, $withdrawal=1);
      Transaction::updateTags($withdrawTxId);

      $depositTxId = Transaction::insertNew($toAccountId,
					    $fromAccount['entityId'], $total, $tax, 'Transfer');
      Transaction::updateCategories($depositTxId, $exists=0, $withdrawal=0);
      Transaction::updateTags($depositTxId);
      Transaction::updatePairedTransaction($depositTxId, $withdrawTxId);
      Transaction::updatePairedTransaction($withdrawTxId, $depositTxId);

      // update balances
      Account::updateAccountBalance($fromAccountId, $_REQUEST['date']);
      Account::updateAccountBalance($toAccountId, $_REQUEST['date']);
    }

    // edit transfer transaction
    else if ($_REQUEST['mode'] == "edit") {
      if ($_REQUEST['accountId'] == $_REQUEST['fromAccount']) { $transferType = "withdrawal"; }
      else { $transferType = "deposit"; }

      $pairedTransactionId = Transaction::getPairedTransaction($_REQUEST['transactionId']);

      if ($transferType == "withdrawal") {
        $transactionId = $_REQUEST['transactionId'];

        // if there is no paired transaction, this is a withdrawal turned into a transfer
        if (($pairedTransactionId == 0) && ($_REQUEST['transactionType'] == "Transfer")) {
          $pairedTransactionId =
            Transaction::insertNew($toAccountId,
                                   $fromAccount['entityId'],
                                   $_REQUEST['total'],
                                   $_REQUEST['tax'],
                                   'Transfer');
        }
      }
      else {
        // TODO: Implement Transfer changed to a Deposit
        $transactionId = $pairedTransactionId;
      }

      $withdrawalAmt = (abs($_REQUEST['total']) * -1);
      $depositAmount = abs($_REQUEST['total']);

      Transaction::update($transactionId, $fromAccountId,
			  $toAccount['entityId'],
			  $withdrawalAmt, '', 'Transfer');
      Transaction::updateCategories($transactionId, $exists=1, $withdrawal=1);
      Transaction::updateTags($transactionId);

      if ($pairedTransactionId > 0) {
        if ($transferType == "withdrawal") { $transactionId = $pairedTransactionId; }
        else { $transactionId = $_REQUEST['transactionId']; }
	Transaction::update($transactionId, $toAccountId, $fromAccount['entityId'],
                            $depositAmount, '', 'Transfer');
	Transaction::updateCategories($transactionId, $exists=1, $withdrawal=0);
	Transaction::updateTags($transactionId);
      }

      // update balances
      Account::updateAllAccountBalances();

      // run the transactionUpdate() method on each plugin
      Plugins::run('transactionUpdate', array($_REQUEST['transactionId']));
    }

    // TODO: error handling
    return array('result' => 'success');
  }
 


  public static function search($s) {

    $dbh = dbHandle(1);

    // get any matching entities
    $entityIds = array();
    $stmt = $dbh->prepare('SELECT * FROM entities e WHERE e.name LIKE ?');
    $stmt->execute(array('%'.$s.'%'));
    $results = $stmt->fetchAll(PDO::FETCH_ASSOC);
    foreach ($results as $row) { $entityIds[] = $row['id']; }

    // finding transactions with matching notes or matching entities
    $q = 'SELECT t.*,
            e.name as entityName,
            ae.name as accountName,
            GROUP_CONCAT(c.name) as category,
            GROUP_CONCAT(c.id) as categoryId,
            GROUP_CONCAT(tc.amount) as categoryAmounts
          FROM transactions t 
          LEFT JOIN entities e ON t.entityId=e.id
          LEFT JOIN accounts a ON t.accountId=a.id
          LEFT JOIN entities ae ON a.entityId=ae.id
          LEFT JOIN transactionCategory tc ON t.id=tc.transactionId 
          LEFT JOIN categories c ON tc.categoryId=c.id
          WHERE t.notes LIKE ? ';

    $queryParams[] = '%'.$s.'%';
    if (count($entityIds)) {
      $q .= ' OR e.id IN (' . str_pad('', count($entityIds)*2-1, '?,') . ')';
      foreach ($entityIds as $i => $entityId) { $queryParams[] = $entityId; }
    }

    $q .= ' GROUP BY t.id ORDER BY t.date'; 

    $stmt = $dbh->prepare($q);
    $stmt->execute($queryParams);
    $txs = $stmt->fetchAll(PDO::FETCH_ASSOC);
   
    foreach ($txs as $i => $tx) { 
      $link = Category::buildLink($tx['category'], $tx['categoryId'], '');
      $txs[$i]['categoryLink'] = $link;
    }
   
    return $txs;
  }



  public static function getForShow() {
    $dbh = dbHandle(1);

    $where = ''; $joins = ''; $bindParams = array();
    if (isset($_GET['id'])) {
      $where .= 't.accountId=:accountId AND ';
      $bindParams[':accountId'] = $_GET['id'];
    }

    if (isset($_GET['categoryId'])) {
      $where .= 'tc2.categoryId=:categoryId AND ';
      $bindParams[':categoryId'] = $_GET['categoryId'];
      $joins = 'LEFT JOIN transactionCategory AS tc2 ON t.id=tc2.transactionId' ;
    }

    if (isset($_GET['entityId'])) {
      $where .= 't.entityId=:entityId AND ';
      $bindParams[':entityId'] = $_GET['entityId'];
    }

    if (isset($_GET['tagId'])) {
      $where .= '';
      $where .= 'tm.tagId=:tagId AND ';
      $bindParams[':tagId'] = $_GET['tagId'];
    }

    if (isset($_GET['month'])) {
      $where .= 'DATE_FORMAT(t.date, "%Y-%m") = :month AND ';
      $bindParams[':month'] = $_GET['month']; 
    }
    else if (isset($_GET['year'])) {
      $where .= 'DATE_FORMAT(t.date, "%Y") = :year AND ';
      $bindParams[':year'] = $_GET['year'];
    }

    if (isset($_GET['dateRange']) && ($_GET['dateRange'] == "last30")) { 
      $where .= 'DATEDIFF(CURRENT_TIMESTAMP, t.date) <= 30 AND ';
    }

    if (isset($_GET['dateRange']) && ($_GET['dateRange'] == "currentYear")) {
      $where .= 'DATE_FORMAT(t.date, "%Y") = "'.date('Y').'" AND ';
    }

    if (isset($_GET['dateRange']) && ($_GET['dateRange'] == "lastYear")) {
      $where .= 'DATE_FORMAT(t.date, "%Y") = "'.(date('Y')-1).'" AND ';
    }

    $q = 'SELECT
        t.id,
        a.id as accountId,
        ae.name as accountName,
        ea.id as destinationAccountId,
        DATE_FORMAT(t.date, "%Y-%m-%d") as date,
        DATEDIFF(CURRENT_TIMESTAMP, t.date) as daysOld,
        IF (DATE_FORMAT(t.date, "%Y")=
            DATE_FORMAT(CURRENT_TIMESTAMP,"%Y"),1,0) as currentYear,
        IF (DATE_FORMAT(t.date, "%Y") =
            (DATE_FORMAT(CURRENT_TIMESTAMP,"%Y")-1),1,0) as lastYear,
        t.transactionNumber,
        t.entityId,
        e.name as payee,
        GROUP_CONCAT(c.name) as category,
        GROUP_CONCAT(c.id) as categoryId,
        GROUP_CONCAT(tc.amount) as categoryAmounts,
        GROUP_CONCAT(DISTINCT tm.tagId) as tagIds,
        GROUP_CONCAT(DISTINCT tags.name) as tagNames, 
        t.transactionType,
        t.amount,
        t.tax,
        t.interest,
        t.amount-t.interest as principal,
        t.notes,
        t.accountBalance,
        st.transactionType as stockTransactionType,
        st.ticker,
        st.qty as shares,
        st.price as sharePrice,
        st.fees as txFees
      FROM transactions t
      LEFT JOIN accounts a ON t.accountId=a.id
      LEFT JOIN entities ae ON a.entityId=ae.id
      LEFT JOIN entities e ON t.entityId=e.id
      LEFT JOIN accounts ea ON e.id=ea.entityId
      LEFT JOIN transactionCategory tc ON t.id=tc.transactionId
      LEFT JOIN categories c ON tc.categoryId=c.id
      LEFT JOIN tagMapping tm ON t.id=tm.transactionId
      LEFT JOIN tags ON tm.tagId=tags.id
      LEFT JOIN stockTransactions st ON t.id=st.transactionId
      ' . $joins . '
      WHERE '.
        $where . ' 1=1
      GROUP BY t.id 
      ORDER BY t.date,t.transactionNumber';
    //print '<pre>'.$q.'</pre><br><br>';  // die();
    $stmt = $dbh->prepare($q);
    foreach ($bindParams as $key => &$value) { 
      $stmt->bindParam($key, $value, PDO::PARAM_STR);
    }
    $stmt->execute();
    $transactions = array();
     
    if ($stmt->rowCount()) { 
      $results = $stmt->fetchAll(PDO::FETCH_ASSOC);
      foreach ($results as $row) { $transactions[] = $row; }
      return $transactions;
    }
    return $transactions;
  }



  public static function getPairedTransaction($txId) { 
    // lookup the paired transactionId for the transaction being edited
    // first lookup the transactionNumber, amount and date prior to the edit
    $dbh = dbHandle();
    $q = 'SELECT pairedTransaction FROM transactions WHERE id=:txId';
    $stmt = $dbh->prepare($q);
    $stmt->bindParam(':txId', $txId);
    $stmt->execute();
    $row = $stmt->fetch(PDO::FETCH_ASSOC);
    $pairedTxId = $row['pairedTransaction'];
    return $pairedTxId;
  }


  public static function expensesByCategory($categoryId, $range, $groupBy) { 
    $categoryTotals = array();

    $rangeCriteria = '';
    if ($range == 'currentyear') { $rangeCriteria = ' AND YEAR(t.date)=YEAR(CURRENT_TIMESTAMP) '; }
    else if ($range == 'lastyear') { $rangeCriteria = ' AND (YEAR(CURRENT_TIMESTAMP)-YEAR(date)<2) '; }
    else if ($range == 'lasttwoyears') { $rangeCriteria = ' AND (YEAR(CURRENT_TIMESTAMP)-YEAR(date)<3) '; }
    else if ($range == 'lastthreeyears') { $rangeCriteria = ' AND (YEAR(CURRENT_TIMESTAMP)-YEAR(date)<4) '; }
    else if ($range == 'all') {}

    $dbh = dbHandle(1);
    $q = "SELECT 
            DATE_FORMAT(t.date,'%Y-%m') as month,
            DATE_FORMAT(t.date,'%Y') as year, 
            ABS(tc.amount) as total
          FROM transactions t, transactionCategory tc
          WHERE
             t.id=tc.transactionId AND
             tc.categoryId IN (?) ".
              $rangeCriteria . '
          ORDER BY t.date';
    $stmt = $dbh->prepare($q);
    $stmt->execute(array($categoryId));
    $results = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    if ($results) { 
      foreach ($results as $row) {
        if (!isset($categoryTotals[$row[$groupBy]])) { $categoryTotals[$row[$groupBy]] = 0; }
        $categoryTotals[$row[$groupBy]] += $row['total'];
      }
    }    
    return $categoryTotals; 
  }


  public static function expensesByEntity($entityId, $range, $groupBy) { 
    $categoryTotals = array();

    $rangeCriteria = '';
    if ($range == 'currentyear') { $rangeCriteria = ' AND YEAR(t.date)=YEAR(CURRENT_TIMESTAMP) '; }
    else if ($range == 'lastyear') { $rangeCriteria = ' AND (YEAR(CURRENT_TIMESTAMP)-YEAR(date)<2) '; }
    else if ($range == 'lasttwoyears') { $rangeCriteria = ' AND (YEAR(CURRENT_TIMESTAMP)-YEAR(date)<3) '; }
    else if ($range == 'lastthreeyears') { $rangeCriteria = ' AND (YEAR(CURRENT_TIMESTAMP)-YEAR(date)<4) '; }
    else if ($range == 'all') {}

    $dbh = dbHandle(1);
    $q = "SELECT
            DATE_FORMAT(t.date,'%Y-%m') as month,
            DATE_FORMAT(t.date,'%Y') as year,
            ABS(tc.amount) as total
          FROM transactions t, transactionCategory tc, entities e
          WHERE
            t.id=tc.transactionId AND
            t.entityId=e.id AND
            t.entityId IN (?) ".
            $rangeCriteria . '
          ORDER BY t.date';
    $stmt = $dbh->prepare($q);
    $stmt->execute(array($entityId));
    $results = $stmt->fetchAll(PDO::FETCH_ASSOC);

    $categoryTotals = array();
    foreach ($results as $row) {
      if (!isset($categoryTotals[$row[$groupBy]])) { $categoryTotals[$row[$groupBy]] = 0; }
      $categoryTotals[$row[$groupBy]] += $row['total'];
    }
    return $categoryTotals; 
  }


  public static function expensesByTag($tagId, $range, $groupBy) { 
    $categoryByTotals = array();

    $rangeCriteria = '';
    if ($range == 'currentyear') { $rangeCriteria = ' AND YEAR(t.date)=YEAR(CURRENT_TIMESTAMP) '; }
    else if ($range == 'lastyear') { $rangeCriteria = ' AND (YEAR(CURRENT_TIMESTAMP)-YEAR(date)<2) '; }
    else if ($range == 'lasttwoyears') { $rangeCriteria = ' AND (YEAR(CURRENT_TIMESTAMP)-YEAR(date)<3) '; }
    else if ($range == 'lastthreeyears') { $rangeCriteria = ' AND (YEAR(CURRENT_TIMESTAMP)-YEAR(date)<4) '; }
    else if ($range == 'all') {}

    $groupByValue = '';
    if ($groupBy == 'month') { $groupByValue = 'month'; }
    else if ($groupBy == 'year') { $groupByValue = 'year'; }

    $dbh = dbHandle(1);
    $q = "SELECT
         t.date,
       DATE_FORMAT(t.date,'%Y-%m') as month,
       DATE_FORMAT(t.date,'%Y') as year,
       ABS(SUM(tc.amount)) as total
      FROM transactionCategory tc, transactions t 
      LEFT JOIN tagMapping tm ON t.id=tm.transactionId
      WHERE
       t.id=tc.transactionId AND
       tm.tagId IN (?) ".
       $rangeCriteria . '
      GROUP BY '.$groupByValue.'
      ORDER BY t.date';

    $stmt = $dbh->prepare($q);
    $stmt->execute(array($tagId));
    $results = $stmt->fetchAll(PDO::FETCH_ASSOC);
    foreach ($results as $row) { 
      $categoryTotals[$row[$groupByValue]] = $row['total'];
    }
    return $categoryTotals; 
  } 


  public static function delete($id) {
    try { 
      $dbh = dbHandle(1);

      // get transaction info
      $stmt = $dbh->prepare('SELECT * FROM transactions WHERE id=?');
      $stmt->execute(array($id));
      $row = $stmt->fetch(PDO::FETCH_ASSOC);
   
      // delete from transactions and transactionCategory tables
      $stmt = $dbh->prepare('DELETE FROM transactions WHERE id=?');
      $stmt->execute(array($id));
      $stmt = $dbh->prepare('DELETE FROM transactionCategory WHERE transactionId=?');
      $stmt->execute(array($id));

      if ($row['pairedTransaction'] != "0") { 
	$stmt = $dbh->prepare('DELETE FROM transactions WHERE id=?');
	$stmt->execute(array($row['pairedTransaction']));
	$stmt = $dbh->prepare('DELETE FROM transactionCategory WHERE transactionId=?');
	$stmt->execute(array($row['pairedTransaction']));
      }

      // update account balances
      Account::updateAccountBalance($row['accountId'], $row['date']);

      $response = array('success');
    }
    catch (PDOException $e) { $response = array('error', $e->getMessage()); }
    return $response;
  }


  public static function getTotalForFrontPage($type, $daysBack) {
    $dbh = dbHandle(1); 
    $total = 0;
    $q = 'SELECT SUM(ABS(t.amount))-IFNULL(SUM(ABS(st.total)),0) as total
          FROM transactions t
          LEFT JOIN stockTransactions st ON t.id=st.transactionId
          WHERE t.date > DATE_SUB(CURRENT_TIMESTAMP, INTERVAL :daysBack DAY) AND
              t.transactionType=:type';
    //echo $q;
    $stmt = $dbh->prepare($q);
    $stmt->bindParam(':type', $type);
    $stmt->bindParam(':daysBack', $daysBack);
    $stmt->execute();
    $row = $stmt->fetch(PDO::FETCH_ASSOC);
    return $row['total'];
  }


  public static function getTotalsByCategory($daysBack) {
    $dbh = dbHandle(1); 
    $q = 'SELECT
          c.name as category,
          c.id as categoryId,
          SUM(tc.amount) as amt
         FROM transactions t,
             transactionCategory tc,
             categories c
         WHERE
          t.transactionType <> "Transfer" AND 
          t.id=tc.transactionId AND			
          tc.categoryId=c.id AND
          t.date > DATE_SUB(CURRENT_TIMESTAMP, INTERVAL :daysBack DAY)
        GROUP BY tc.categoryId
        ORDER BY amt ASC
        LIMIT 10';
    $stmt = $dbh->prepare($q);
    $stmt->bindParam(':daysBack', $daysBack);
    $stmt->execute();
    $results = $stmt->fetchAll(PDO::FETCH_ASSOC);
    return $results; 
  }


  public static function orphans() { 
    $dbh = dbHandle(1);
    $q = 'SELECT t.id,t.amount,t.notes,t.accountId,tc.categoryId,tc.amount
          FROM transactions t LEFT JOIN transactionCategory tc ON t.id=tc.transactionId
          HAVING tc.amount IS NULL';
    $results = $dbh->query($q);
    if ($results) {
      $orphans = array();
      foreach ($results as $row) { $orphans[] = $row; }
      return $orphans;
    }
  }


  public static function updateTags($transactionId) {
    if (empty($transactionId) || !is_numeric($transactionId)) { return; }

    // get the list of tags from the params  
    $tagIds = $_REQUEST['tagIds'];
    $tagIds = str_replace('||', '|', $tagIds);
    $tags = explode('|', $tagIds);

    // remove all existing tags                                            
    $dbh = dbHandle(1);
    $stmt = $dbh->prepare('DELETE FROM tagMapping WHERE transactionId=?');
    $stmt->execute(array($transactionId));

    for ($i = 0; $i < count($tags); $i++) {
      if ($tags[$i] == "") { continue; }
      $q = 'INSERT INTO tagMapping (tagId, transactionId) '.
           'VALUES(:tagId, :txId)';
      $stmt = $dbh->prepare($q);
      $stmt->bindParam(':tagId', $tags[$i]);
      $stmt->bindParam(':txId', $transactionId);
      $stmt->execute();
    }
  }


  public static function updateCategories($transactionId, $exists, $withdrawal=0) {
    debug_log("updateCategories(), txId = " . $transactionId . ", exists = " . $exists . ", withdrawal = " . $withdrawal);
    if ($transactionId == "") { return; }
    $dbh = dbHandle(1);

    if (isset($_REQUEST['category']) && ($_REQUEST['category'] == "")) {
      $_REQUEST['category'] = Category::add($_REQUEST['categoryName']);
    }


    if ($exists) {
      $stmt=$dbh->prepare('DELETE FROM transactionCategory WHERE transactionId=?');
      $stmt->execute(array($transactionId));
    }

    if (isset($_REQUEST['category'])) {
      $categoryId = $_REQUEST['category'];
      $amount = $_REQUEST['total'];
      if ($withdrawal) { $amount = ($amount * -1); }

    $q = 'INSERT INTO transactionCategory (transactionId,categoryId,amount)
          VALUES(:transactionId, :categoryId, :amount)';
    $stmt = $dbh->prepare($q);
    $stmt->bindParam(':transactionId', $transactionId);
    $stmt->bindParam(':categoryId', $categoryId);
    $stmt->bindParam(':amount', $amount);
    $stmt->execute();
    }
    else {
      // if the associated transaction is a withdrawal, we record the entries as 
      // negative so that the transactionCategory table can encode whether a 
      // category should be debited or credited for the specificed amount
      if ($withdrawal) { $factor = -1; } else { $factor = 1; }

      for ($i = 1; $i < 9; $i++) {
	if (isset($_REQUEST['category'.$i]) &&
	    isset($_REQUEST['categoryAmount'.$i]) &&
	    ($_REQUEST['categoryAmount'.$i] > 0)) {
          $q = 'INSERT INTO transactionCategory '.
               '(transactionId,categoryId,amount) '.
	       'VALUES(:transactionId,:categoryId,:amount)';
          $stmt = $dbh->prepare($q);
          $stmt->bindParam(':transactionId', $transactionId);
          $stmt->bindParam(':categoryId', $_REQUEST['category'.$i]);
          $amt = ($_REQUEST['categoryAmount'.$i] * $factor);
          $stmt->bindParam(':amount', $amt);
          debug_log($q . ", txId = " . $transactionId . ", catId = " . $_REQUEST['category'.$i] . ", amt = " . $amt . "\n");
          $stmt->execute();
	}
      }
    }
  }

}


?>