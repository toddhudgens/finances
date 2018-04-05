<?php

class Account {  

  public static function get($id) { 
    $dbh = dbHandle(1);
    $q = 'SELECT
        a.id,
        e.name,
        a.entityId,
        a.initialBalance,
        a.balance,
        a.accountType,
        a.liquid,
        a.notes,
        a.active
     FROM accounts a, entities e
      WHERE a.entityId=e.id AND a.id=? LIMIT 1';
    $stmt = $dbh->prepare($q);
    $stmt->execute(array($id));
    $results = $stmt->fetchAll(PDO::FETCH_ASSOC);
    if ($results) { foreach ($results as $row) { return $row; }}
    else { return array(); }
  }


  public static function getAll($groupBy='') { 
    $results = array();
    $accounts = array();
    $dbh = dbHandle();
    $q = 'SELECT accountId,SUM(currentValue) as currentValue
          FROM stockAssets GROUP BY accountId';
    $results = $dbh->query($q);
    $stockAssets = $results->fetchAll(PDO::FETCH_ASSOC);
    $stockAccountValues = array();
    foreach ($stockAssets as $id => $info) {
      $stockAccountValues[$info['accountId']] = $info['currentValue'];
    }

    try { 
      $dbh = dbHandle(1);
      $q = 'SELECT
          a.id,
          e.name,
          a.initialBalance,
          a.balance,
          a.accountType,
          a.liquid,
          a.active,
          a.assetId,
          a.notes,
          a.entityId,
          ass.name as assetName,
          at.name as accountTypeName
        FROM entities e, accounts a
        LEFT JOIN assets ass ON a.assetId=ass.id
        LEFT JOIN accountType at ON a.accountType=at.atid
        WHERE a.entityId=e.id AND a.active=1
        ORDER BY at.sortOrder, a.sortOrder';
     
      $stmt = $dbh->prepare($q);
      $stmt->execute();
      $results = $stmt->fetchAll(PDO::FETCH_ASSOC);

      if (count($results)) { 
        if ($groupBy == 'accountType') { 
          foreach ($results as $row) { 
            if (isset($stockAccountValues[$row['id']])) { 
              $row['balance'] += $stockAccountValues[$row['id']]; 
            }
            $accounts[$row['accountTypeName']][] = $row;
          }
        }
        else { 
          foreach ($results as $row) { 
            $accounts[$row['id']] = $row;
          }
        }
      }

      $q = 'SELECT accountId,SUM(currentValue) as currentValue 
            FROM stockAssets GROUP BY accountId';
      $results = $dbh->query($q);
      $stockAssets = $results->fetchAll(PDO::FETCH_ASSOC);
      $stockAccountValues = array();
      foreach ($stockAssets as $id => $info) {
        $stockAccountValues[$info['accountId']] = $info['currentValue'];
      }
    }
    catch (PDOException $e) {}

    return $accounts;
  }


  public static function getEntityIdMap() {
    $dbh = dbHandle(1);
    $q = 'SELECT e.id as entityId,e.name,a.id as accountId 
          FROM accounts a, entities e
          WHERE a.entityId=e.id';
    $results = $dbh->query($q);
    $accounts = $results->fetchAll(PDO::FETCH_ASSOC);

    $accountsByEntityId = array();
    foreach ($accounts as $i => $info) { 
      $accountsByEntityId[$info['entityId']] = $info['accountId'];
    }
    return $accountsByEntityId;
  }


  public static function getSelectValues() {
    $accounts = array();
    try {
      $dbh = dbHandle(1);
      $q = 'SELECT e.id,e.name FROM entities e, accounts a
            WHERE a.entityId=e.id AND a.active=1
            ORDER BY e.name';
      $results = $dbh->query($q);
      $accounts = $results->fetchAll(PDO::FETCH_ASSOC);
    }
    catch (PDOException $e) {}
    return $accounts;
  }



  public static function add() {

    if ($_GET['initialBalance'] == '') { $initBal = 0; }
    else { $initBal = $_GET['initialBalance']; }
    
    // insert into the entities table 
    $dbh = dbHandle(1);
    $stmt = $dbh->prepare('INSERT INTO entities(name) VALUES(?)');
    $stmt->execute(array($_GET['name']));

    // select the id (can't use last_insert_id() because of dupes)
    $stmt = $dbh->prepare('SELECT id FROM entities WHERE name=?');
    $stmt->execute(array($_GET['name']));
    $row = $stmt->fetch(PDO::FETCH_ASSOC);
    $entityId = $row['id'];

    if (($_GET['accountType'] == 4) || ($_GET['accountType'] == 6)) {
      $hasLoanInterest = 1;
    }
    else { $hasLoanInterest = 0; }

    if (!isset($_GET['assetId']) || ($_GET['assetId'] == "")) { $assetId = 0; }
    else { $assetId = $_GET['assetId']; }

    $q = 'INSERT INTO accounts(initialBalance, balance, entityId, accountType, '.
                              'assetId, liquid, active,hasLoanInterest) '.
         'VALUES(:initialBalance, :balance, :entityId, :accountType,'.
                 ':assetId, :liquid, :active, :hasLoanInterest)';
    $stmt = $dbh->prepare($q);
    $stmt->bindParam(':initialBalance', $initBal);
    $stmt->bindParam(':balance', $initBal);
    $stmt->bindParam(':entityId', $entityId);
    $stmt->bindParam(':accountType', $_GET['accountType']);
    $stmt->bindParam(':assetId', $assetId);
    $stmt->bindParam(':liquid', $_GET['liquid']);
    $stmt->bindParam(':active', $_GET['active']);
    $stmt->bindParam(':hasLoanInterest', $hasLoanInterest);
    $stmt->execute();
    if ($stmt->rowCount()) { $response = array('success'); }
    else { $response = array('error', $q, print_r($stmt->errorInfo(), true)); } 
    return $response; 
  }


  public static function update() {
    $dbh = dbHandle(1);
    $q = 'UPDATE accounts SET
           initialBalance=:initialBalance,
           accountType=:accountType,
           notes=:notes,
           liquid=:liquid,
           active=:active
          WHERE id=:accountId';
    $stmt = $dbh->prepare($q);
    $stmt->bindParam(':initialBalance', $_GET['initialBalance']);
    $stmt->bindParam(':accountType', $_GET['accountType']);
    $stmt->bindParam(':notes', $_GET['notes']);
    $stmt->bindParam(':liquid', $_GET['liquid']);
    $stmt->bindParam(':active', $_GET['active']);
    $stmt->bindParam(':accountId', $_GET['accountId']);
    $stmt->execute();

    if($stmt->errorCode() == 0) {
      Account::updateEntityName($_GET['accountId'], $_GET['name']);
      Account::updateAccountBalance($_GET['accountId']);
      $response = array('success');
    }
    else { $response = array('error', $q, print_r($stmt->errorInfo(), true)); }

    return $response;
  }


  public static function getNextTransactionNumber($accountId) {
    $dbh = dbHandle(1);
    if (isset($accountId)) { 
      $q = 'SELECT id,(transactionNumber+1) as nextId
            FROM transactions
            WHERE accountId=?
           ORDER BY transactionNumber DESC LIMIT 1';
      $stmt = $dbh->prepare($q);
      $stmt->execute(array($accountId));
      $results = $stmt->fetchAll(PDO::FETCH_ASSOC);
      if ($results) { 
        foreach ($results as $row) { 
          if (isset($row['nextId'])) { return $row['nextId']; }
        }
      }
      return 1;
    }
  }



  public static function updateAllAccountBalances() {
    $dbh = dbHandle(1);
    $results = $dbh->query("SELECT id FROM accounts");
    foreach ($results as $row) { 
      Account::updateAccountBalance($row['id']);
    }
  }


  public static function updateAccountBalance($accountId, $date='') {

    // retrieve initial balance and if this account has interest
    $dbh = dbHandle(1);
    $q = 'SELECT hasLoanInterest, initialBalance 
          FROM accounts WHERE id=:accountId';
    $stmt = $dbh->prepare($q);
    $stmt->bindParam(':accountId', $accountId);
    $stmt->execute();
    $row = $stmt->fetch(PDO::FETCH_ASSOC);
    $initialBalance = $row['initialBalance'];
    $hasLoanInterest = $row['hasLoanInterest'];
     

    // retrieve sum of all transactions for that account
    $q = 'SELECT SUM(amount) as total, SUM(interest) as interestTotal
          FROM transactions WHERE accountId=:accountId';
    $stmt = $dbh->prepare($q);
    $stmt->bindParam(':accountId', $accountId);
    $stmt->execute();
    $row = $stmt->fetch(PDO::FETCH_ASSOC);
    $total = $row['total'];
    if ($hasLoanInterest) { 
      $loanInterest = $row['interestTotal'];
      $total -= $loanInterest;
    }
    $newBalance = $initialBalance + $total;   


    // update account with new balance
    $q = 'UPDATE accounts SET balance=:newBalance WHERE id=:accountId';
    $stmt = $dbh->prepare($q);
    $stmt->bindParam(':newBalance', $newBalance);
    $stmt->bindParam(':accountId', $accountId);
    $stmt->execute();


    // if date isnt set, then we want to use the initial balance
    if ($date == '') { 
      $balance = $initialBalance;
    }
    else { 
      // retrieve the transaction immediately before the one that was editted
      $q = 'SELECT id, accountBalance FROM transactions '.
           'WHERE date < :date AND accountId=:accountId ' . 
           'ORDER BY date DESC, transactionNumber DESC';
      $stmt = $dbh->prepare($q);
      $stmt->bindParam(':date', $date);
      $stmt->bindParam(':accountId', $accountId);
      $stmt->execute();
      $row = $stmt->fetch(PDO::FETCH_ASSOC);
      if (isset($row['accountBalance'])) { 
        $balance = $row['accountBalance'];
      }
      // if there are no rows before, we must have editted the first tx
      else { 
        $balance = $initialBalance;
      }
    }
   
    // now select the transaction in question, and everything after it. 
    // so that we can recalculate the accountBalance field
    $q = 'SELECT t.id,t.amount,t.interest 
          FROM transactions t 
          WHERE t.date >=:date AND t.accountId=:accountId 
          ORDER BY t.date ASC, t.transactionNumber ASC';
    $stmt = $dbh->prepare($q);
    $stmt->bindParam(':date', $date);
    $stmt->bindParam(':accountId', $accountId);
    $stmt->execute();
    $results = $stmt->fetchAll(PDO::FETCH_ASSOC);

    foreach ($results as $row) { 
      $balance += $row['amount'];
      if ($hasLoanInterest) { $balance -= $row['interest']; }

      $q = 'UPDATE transactions SET accountBalance=:acctBalance WHERE id=:id';
      $stmt = $dbh->prepare($q);
      $stmt->bindParam(':acctBalance', $balance);
      $stmt->bindParam(':id', $row['id']);
      $stmt->execute();
    }
  }



  public static function updateEntityName($accountId, $name) { 
    $info = Account::get($accountId);
    if ($info['entityId'] != '') { 
      Entity::updateName($info['entityId'], $name);
    }    
  }

}

?>