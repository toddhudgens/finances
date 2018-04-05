<?php

class Networth { 

public static function getLogEntries() {
  $dbh = dbHandle(1);
  $q = 'SELECT value,UNIX_TIMESTAMP(ts)*1000 as ts FROM networth ORDER BY ts';
  $results = $dbh->query($q);
  return $results;
}

public static function update() {
  $accounts = Account::getAll('accountType');
  $accountBalance = 0;
  foreach ($accounts as $accountType => $accountsInType) {
    for ($i = 0; $i < count($accountsInType); $i++){
      $accountBalance += $accountsInType[$i]['balance'];
    }
  }
  $assetValue = Asset::totalValue();
  $networth = $accountBalance + $assetValue;
  Networth::updateLog($networth);
}


public static function updateLog($val) { 
  $dbh = dbHandle(1);
  $q = 'INSERT INTO networth VALUES(0,:networth,CURRENT_TIMESTAMP)';
  $stmt = $dbh->prepare($q);
  $stmt->bindParam(':networth', $val);
  $stmt->execute();
}



}

?>