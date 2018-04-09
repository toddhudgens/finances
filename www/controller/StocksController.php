<?php



function updatePrices() {
  $dbh = dbHandle();

  // get all of the unique stocks we need to track
  $q = 'SELECT distinct ticker FROM stockPrices';
  $results = $dbh->query($q);
  $stockTickers = $results->fetchAll(PDO::FETCH_ASSOC);
  //print_r($stockTickers);

  // update their prices
  foreach ($stockTickers as $i => $info) {
    echo "Updating price for: " . $info['ticker'] . "<br>";
    Stocks::updatePrice($info['ticker']);
    sleep(1);
  }

  // update the stockAssets table
  foreach ($stockTickers as $i => $info) { 
    $price = Stocks::getLatestPrice($info['ticker']);
    Stocks::updateAssetsWithTicker($info['ticker'], $price);
  }
}