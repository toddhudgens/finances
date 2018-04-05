<?php


function updatePrices() {
  $ch = curl_init(); 
  curl_setopt($ch, CURLOPT_URL, "m.kitco.com");
  curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
  $html = curl_exec($ch); 
  if(curl_errno($ch)){ echo 'Curl error: ' . curl_error($ch); }
  curl_close($ch);

  $htmlByLine = explode("\n", $html);
  $metalPrices = array('Gold' => 0, 'Silver' => 0);

  for ($i = 0; $i < count($htmlByLine); $i++) { 
    if (strpos($htmlByLine[$i], "precious-metals/Gold.html")) { 
      $nextLine = $htmlByLine[$i+1];
      $noWrapLoc = strpos($nextLine, "nowrap") + 16;
      $endTdLoc = strpos($nextLine, "</td>");
      $metalPrices['Gold'] = substr($nextLine, $noWrapLoc, $endTdLoc-$noWrapLoc); 
    }
    else if (strpos($htmlByLine[$i], "precious-metals/Silver.html")) { 
      $nextLine =$htmlByLine[$i+1];
      $noWrapLoc = strpos($nextLine, "nowrap") + 16;
      $endTdLoc =strpos($nextLine, "</td>");
      $metalPrices['Silver'] = substr($nextLine, $noWrapLoc, $endTdLoc-$noWrapLoc);
    }
  }

  // insert prices into database
  PreciousMetals::updatePrice("Gold", $metalPrices['Gold']);
  PreciousMetals::updatePrice("Silver", $metalPrices['Silver']);
  
  $results = PreciousMetals::getAssets();
  foreach ($results as $row) { 
    $value = ($metalPrices[$row['metal']] * $row['weight'] * $row['purity'] * $row['quantity']) + 
             ($row['premium'] * $row['quantity']);
    Asset::updateCurrentValue($row['assetId'], $value);
  }
}


function assetInfo() {
  $row = PreciousMetals::getAsset($_GET['id']);
  if ($row) { echo json_encode($row); }
  else { echo json_encode(array()); }
}

?>