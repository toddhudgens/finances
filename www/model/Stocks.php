<?php


class Stocks extends AbstractPlugin { 

  public static function purchase() {
    $_REQUEST['payee'] = 0;
    $_REQUEST['transactionType'] = "Withdrawal";

    if ($_REQUEST['adminFees'] == '') { $fees = 0; } else { $fees = $_REQUEST['adminFees']; }

    $results = Transaction::save();    
    $txId = $results['transactionId'];

    $q = 'REPLACE INTO stockTransactions VALUES('.
      ':txId,:txType,:ticker,:qty,:price,:fees,:total)';

    $type = "Purchase";
    $dbh = dbHandle();
    $stmt = $dbh->prepare($q);
    $stmt->bindParam('txId', $txId);
    $stmt->bindParam('txType', $type);
    $stmt->bindParam('ticker', $_REQUEST['ticker']);
    $stmt->bindParam('qty', $_REQUEST['shares']);
    $stmt->bindParam('price', $_REQUEST['sharePrice']);
    $stmt->bindParam('fees', $fees);
    $stmt->bindParam('total', $_REQUEST['total']);
    $stmt->execute();
    if ($stmt->rowCount()) { 
      Stocks::updateStockAssets("Purchase", $_REQUEST['accountId'], $_REQUEST['ticker'], 
                                $_REQUEST['shares'], $_REQUEST['sharePrice']);
    }
    return array('result' => 'success');
  }

  public static function sale() {
    $_REQUEST['payee'] = 0;
    $_REQUEST['transactionType'] = "Deposit";

    $results = Transaction::save();

    $txId = $results['transactionId'];
    $q = 'REPLACE INTO stockTransactions VALUES('.
      ':txId,:txType,:ticker,:qty,:price,:fees, :total)';

    $type = "Sale";
    $dbh = dbHandle();
    $stmt = $dbh->prepare($q);
    $stmt->bindParam('txId', $txId);
    $stmt->bindParam('txType', $type);
    $stmt->bindParam('ticker', $_REQUEST['ticker']);
    $stmt->bindParam('qty', $_REQUEST['shares']);
    $stmt->bindParam('price', $_REQUEST['sharePrice']);
    $stmt->bindParam('fees', $_REQUEST['adminFees']);
    $stmt->bindParam('total', $_REQUEST['total']);
    $stmt->execute();
    Stocks::updateStockAssets("Sale", $_REQUEST['accountId'], $_REQUEST['ticker'], 
                              $_REQUEST['shares'], $_REQUEST['sharePrice']);
    return array('result' => 'success');
  }


  public static function updateStockAssets($type, $accountId, $ticker, $shares, $price) {
    // see if we an existing row for this stock
    $dbh = dbHandle();
    $q = 'SELECT * FROM stockAssets WHERE ticker=:ticker AND accountId=:accountId';
    $stmt = $dbh->prepare($q);
    $stmt->bindParam(':ticker', $ticker);
    $stmt->bindParam(':accountId', $accountId);
    $stmt->execute();
    $row = $stmt->fetch(PDO::FETCH_ASSOC);    
    if ($row) { $stockInfo = $row; }
    else { $stockInfo = array('qty' => 0, 'avgPrice' => 0); }
    
    $total = $shares * $price;
    if ($type == "Purchase") { 
      $updatedShareCount = $stockInfo['qty'] + $shares; 
    }
    else if ($type == "Sale") { 
      $updatedShareCount = $stockInfo['qty'] - $shares;       
    }

    if ($stockInfo['avgPrice'] == 0) { $avgPrice = $price; }
    else { 
      if ($type == "Sale") { 
        $avgPrice = $stockInfo['avgPrice']; 
        $newTotal = $stockInfo['total'] - $total; 
      }
      else {
        $total = $stockInfo['total'];
        $newTotal = $total + ($shares * $price);
        $avgPrice = $newTotal / $updatedShareCount;
      }
    }

    Stocks::updatePrice($ticker);
    $price = Stocks::getLatestPrice($ticker);
    $currentValue = $price * $updatedShareCount; 
   
    $q = 'REPLACE INTO stockAssets VALUES'.
         '(:accountId,:ticker,:qty,:avgPrice,:total,:currentValue,CURRENT_TIMESTAMP)';
    $stmt = $dbh->prepare($q);
    $stmt->bindParam(':accountId', $accountId);
    $stmt->bindParam(':ticker', $ticker);
    $stmt->bindParam(':qty', $updatedShareCount);
    $stmt->bindParam(':avgPrice', $avgPrice);
    $stmt->bindParam(':total', $newTotal);
    $stmt->bindParam(':currentValue', $currentValue);
    $stmt->execute();
    return array('results' => 'success');
  }



  public static function getForAccount($accountId) {
    $dbh = dbHandle();
    $q = 'SELECT * FROM stockAssets sa '.
         'WHERE sa.accountId=:acctId';
    $stmt = $dbh->prepare($q);
    $stmt->bindParam(':acctId', $accountId);
    $stmt->execute();
    $results = $stmt->fetchAll(PDO::FETCH_ASSOC);

    foreach ($results as $i => $info) { 
      $price = Stocks::getLatestPrice($info['ticker']);
      $results[$i]['price'] = $price;
      $results[$i]['net'] = ($info['qty'] * $price) - ($info['qty'] * $info['avgPrice']);
    }
    return $results;
  }

  public static function updateAssetsWithTicker($ticker, $price) { 
    $dbh = dbHandle();
    $q = 'UPDATE stockAssets '.
         'SET currentValue=qty*:price, priceUpdated=CURRENT_TIMESTAMP '.
         'WHERE ticker=:ticker'; 
    $stmt = $dbh->prepare($q);
    $stmt->bindParam(':price', $price);
    $stmt->bindParam(':ticker', $ticker);
    $stmt->execute();
  }


  public static function transactionCreate($transactionId) {
    self::transactionUpdate($transactionId);
  }

  public static function transactionUpdate($transactionId) {
  }


  // https://www.alphavantage.co/query?function=BATCH_STOCK_QUOTES&symbols=MSFT,FB,AAPL,WFIOX&apikey=KEY
  // https://www.alphavantage.co/query?function=TIME_SERIES_DAILY&symbol=WFIOX&apikey=KEY
  // https://www.alphavantage.co/query?function=TIME_SERIES_DAILY_ADJUSTED&symbol=WFIOX&apikey=KEY


  public static function getLatestPrice($ticker, $returnAll = 0) { 
    $dbh = dbHandle(); 
    $q = 'SELECT price, time, DATEDIFF(CURRENT_TIMESTAMP,time) as daysAgo '.
         'FROM stockPrices '.
         'WHERE ticker=:ticker '.
         'ORDER BY time DESC LIMIT 1';
    $stmt = $dbh->prepare($q);
    $stmt->bindParam(':ticker', $ticker);
    $stmt->execute();
    $row = $stmt->fetch(PDO::FETCH_ASSOC);
    if ($returnAll) { return $row; } else { return $row['price']; }
  }



  public static function updatePrice($ticker) {
    $priceInfo = Stocks::getLatestPrice($ticker, 1);

    // TODO: figure out why this isnt working
    //if (isset($priceInfo) && $priceInfo['daysAgo'] == 0) { return; }

    $apikey = getenv('ALPHA_VANTAGE_API_KEY');
    $url = 'https://www.alphavantage.co/query?'.
           'function=TIME_SERIES_DAILY_ADJUSTED'.
           '&symbol='.$ticker.'&apikey='.$apikey;
    $results = file_get_contents($url);
    $response = json_decode($results);
    $prices = $response->{"Time Series (Daily)"};
    $price = 0;
    foreach ($prices as $date => $info) { 
      $price = $info->{'4. close'};
      break;
    }

    if ($price > 0) { 
      $dbh = dbHandle();
      $q = 'INSERT INTO stockPrices VALUES(:ticker,:price,CURRENT_TIMESTAMP)';
      $stmt = $dbh->prepare($q);
      $stmt->bindParam(':ticker', $ticker);
      $stmt->bindParam(':price', $price);
      $stmt->execute();
    }
  }

  
}


?>