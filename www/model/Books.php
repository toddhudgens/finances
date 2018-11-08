<?php


class Books extends AbstractPlugin { 

  public static $bookCategories = array('Books');

  public static function transactionCreate($transactionId) {
    //self::transactionUpdate($transactionId);
  }

  public static function transactionUpdate($transactionId) {
  }

  public static function assetEditFields(&$html) {
    $html .= '<tr class="bookAssetInfo">';
    $html .= '<td class="label">ISBN:</td>';
    $html .= '<td><input type="text" id="isbn" value=""></td>';
    $html .= '</tr>';
  }


  public static function assetUpdate($assetId) {
    if (isset($_REQUEST['isbn'])) { $isbn = $_REQUEST['isbn']; }

    if (isset($qty) && isset($type)) {
      $dbh = dbHandle(1);
      $autoPriced = ($_REQUEST['pmAutoPricing'] == "true" ? 1 : 0);
      $q = 'REPLACE INTO bookAssets VALUES(:assetId,:isbn)';
      $stmt = $dbh->prepare($q);
      $stmt->bindValue(':assetId', $assetId);
      $stmt->bindValue(':isbn', $isbn);
      $stmt->execute();
    }
  }


  
}


?>