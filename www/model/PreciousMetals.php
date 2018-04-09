<?php

// My Financials Precious Metals Plugin
// Author: Todd Hudgens
// Description: Used to track automotible maintainence

class PreciousMetals extends AbstractPlugin { 

  public static $categories = array("Silver", "Gold");



  public static function getCategoryIds() {
    $metals = '"' . implode('","', self::$categories) . '"';

    $dbh = dbHandle();
    $q = 'SELECT id FROM categories WHERE name IN ('.$metals.')';
    $result = $dbh->query($q);
    $categories = array();
    $rows = $result->fetchAll(PDO::FETCH_ASSOC);
    foreach ($rows as $row) { $categories[] = $row['id']; }
    return $categories;
  }



  public static function getAsset($id) { 
    $dbh = dbHandle(1);
    $q = 'SELECT pma.quantity, pma.automaticPricing, pmt.id, pmt.name, pmt.year,
                 pmt.metal, pmt.weight, pmt.purity, pmt.premium, pmt.averagePrice
          FROM preciousMetalAssets pma
          LEFT JOIN preciousMetalTypes pmt ON pma.typeId=pmt.id
          WHERE pma.assetId=:id';
    $stmt = $dbh->prepare($q);
    $stmt->bindParam(':id', $id);
    $stmt->execute();
    $row = $stmt->fetch(PDO::FETCH_ASSOC);
    return $row;
  }



  public static function getAssets() {
    $dbh = dbHandle(1);
    $q = 'SELECT pma.*,pmt.*,a.*
          FROM preciousMetalAssets pma
          LEFT JOIN preciousMetalTypes pmt ON pma.typeId=pmt.id
          LEFT JOIN assets a ON pma.assetId=a.id
          WHERE pma.automaticPricing=1';
    $results = $dbh->query($q);
    return $results;
  }


  public static function updatePrice($metal, $price) {
    if ($price != 0) {
      $dbh = dbHandle(1);
      $q = 'INSERT INTO preciousMetalPrices VALUES(0,?,?,CURRENT_TIMESTAMP)';
      $stmt = $dbh->prepare($q);
      $stmt->execute(array($metal, $price));
      if ($stmt->rowCount() == 0) { return 0; } 
      else { return 1; }
    }
  }



  public static function preciousMetalSelect($type) { 

    $html = '<select id="'.strtolower($type).'Type"><option value=""></option>';

    try { 
      $dbh = dbHandle(1);
      $stmt = $dbh->prepare('SELECT id,name,year FROM preciousMetalTypes WHERE metal=? ORDER BY name,year');
      $stmt->execute(array($type));
      $results = $stmt->fetchAll(PDO::FETCH_ASSOC);

      foreach ($results as $row) { 
        $label = $row['name'];
        if (($row['year'] != '') && ($row['year'] != 0)) { $label .= ', ' . $row['year']; }
        $html .= '<option value="'.$row['id'].'">' . $label . '</option>';
      }
    }
    catch (PDOException $e) {}

    $html .= '</select>';
    return $html;
  }

  public static function assetEditFields(&$html) { 
    $html .= '<tr class="silverAssetInfo">';
    $html .= '<td class="label">Type:</td>';
    $html .= '<td>' . PreciousMetals::preciousMetalSelect('silver') . '</td>'; 
    $html .= '</tr>';
    $html .= '<tr class="pmAssetInfo">';
    $html .= '<td class="label">Qty:</td><td><input type="text" id="pmQty" value="1"></td>';
    $html .= '</tr>';
    $html .= '<tr class="pmAssetInfo">';
    $html .= '<td class="label">Auto Pricing:</td><td><input type="checkbox" id="pmAutoPricing"></td>';
    $html .= '</tr>';
  }

  public static function assetUpdate($assetId) {
    if (isset($_REQUEST['pmType'])) { $type = $_REQUEST['pmType']; }
    if (isset($_REQUEST['pmQty'])) { $qty = $_REQUEST['pmQty']; }

    if (isset($qty) && isset($type)) { 
      $dbh = dbHandle(1);
      $autoPriced = ($_REQUEST['pmAutoPricing'] == "true" ? 1 : 0);
      $q = 'REPLACE INTO preciousMetalAssets VALUES(:assetId,:qty,:type,:autoPriced)';
      $stmt = $dbh->prepare($q);      
      $stmt->bindValue(':assetId', $assetId);
      $stmt->bindValue(':qty', $qty);
      $stmt->bindValue(':type', $type);
      $stmt->bindValue(':autoPriced', $autoPriced);
      $stmt->execute();
    }
  }

  public static function assetCreate($assetId) { self::assetUpdate($assetId); }


  public static function assetCategoryExtraInfo(&$categories=array()) {
    $dbh = dbHandle();
    $metals = '"' . implode('","', self::$categories) . '"';
    $q = 'SELECT
            metal,
            price, 
            timestamp,
            UNIX_TIMESTAMP(CURRENT_TIMESTAMP)-UNIX_TIMESTAMP(timestamp) as secondsOld 
          FROM preciousMetalPrices 
          WHERE metal IN ('.$metals.') 
          ORDER BY timestamp DESC LIMIT 2';
    $stmt = $dbh->prepare($q);                                                       
    $stmt->execute();
    $pmRows = $stmt->fetchAll(PDO::FETCH_ASSOC);

    foreach ($pmRows as $i => $row) {
      $html = ''; 
      $timeAgo = "";                                                                   
      $secondsOld = $row['secondsOld'];
      if ($secondsOld < 60) { $timeAgo = $secondsOld . " seconds ago"; }
      else if ($secondsOld < 3600) { 
        $timeAgo = floor($secondsOld/60) . " minutes ago"; 
      } 
      else if ($secondsOld < 86400) { 
        $timeAgo = floor($secondsOld/3600) . " hours ago"; 
      }
      else { $timeAgo = "<b>More than 1 day ago!</b>"; }           
      $html .= '<div id="'.$row['metal'].'Price" style="font-size:10px;">';
      $html .= 'Spot Price: <b>$' . $row['price'] . '</b><br>';
      $html .= 'Price Updated: ' . $timeAgo . '<br><br>';
      $html .= '</div>';
      $categories[$row['metal']] = $html;
    }
  }


  public static function assetListingCustomizations(&$assets) { 
    if (!$assets) { return; }

    // retrieve info about precious metals prices
    $dbh = dbHandle();
    $q = 'SELECT DISTINCT metal,price FROM preciousMetalPrices ORDER BY timestamp DESC LIMIT 2';
    $pmResults = $dbh->query($q);
    $pmPrices = array();
    if (count($pmPrices)) { 
      foreach ($pmResults as $pmrow) {
        $pmPrices[$pmrow['metal']] = $pmrow['price'];
      }
    }

    // retrieve info about precious metal assets, 
    // and update value for assets taht have 
    $pmResults = array();
    $pmAssets = array();
    $categoryIds = PreciousMetals::getCategoryIds();
    $q = 'SELECT
            a.id,
            pmt.name,
            pma.quantity,
            pmt.metal,
            pmt.weight,pmt.purity,pma.quantity,pmt.premium,
            ROUND(pmt.weight*pmt.purity*pma.quantity,2) as weight
          FROM assets a, preciousMetalAssets pma, preciousMetalTypes pmt
          WHERE
              a.categoryId IN ('.implode(',', $categoryIds).') AND
              a.id=pma.assetId AND pma.typeId=pmt.id AND pma.automaticPricing=1
          GROUP BY a.id';
    $pmResults = $dbh->query($q);
    if (is_array($pmResults) && count($pmResults)) { 
      foreach ($pmResults as $pmRow) {
        $val = ($pmRow['weight'] * $pmPrices[$pmRow['metal']]) +
	        ($pmRow['premium'] * $pmRow['quantity']);
        $pmAssets[$pmRow['id']] = $val;
      }
    }

    $metals = self::$categories; 
    foreach ($metals as $i => $metal) {
      if (isset($assets[$metal])) { 
        foreach ($assets[$metal] as $assetId => $info) {
          if (isset($pmAssets[$info['id']])) {
            $assets[$metal][$assetId]['currentValue'] = $pmAssets[$info['id']];
          }
        }
      }
    }
  }


}