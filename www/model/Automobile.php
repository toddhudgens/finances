<?php

// My Financials Vehicle Plugin
// Author: Todd Hudgens
// Description: Used to track vehicle maintainence

class Automobile extends AbstractPlugin { 

  public static $automobileCategoryId = 55;
  public static $automobileGasCategoryId = 8;
  public static $maintenanceCategoryId = 11;
  public static $autoInsuranceCategoryId = 37;
  public static $autoTaxId = 99;

  public static function getCategoryId() {
    $dbh = dbHandle();
    $q = 'SELECT id FROM categories WHERE name="Automobiles"';
    $result = $dbh->query($q);
    $row = $result->fetch(PDO::FETCH_ASSOC);
    return $row['id'];
  } 


  public static function createTables() {
    $dbh = dbHandle();
    $q = "CREATE TABLE 
            vehicleMaintenance(
              assetId int(8), 
              transactionId int(12),
	      amount double(10,2),
	      mileage int(8),
	      notes text)";
    $dbh->query($q);

    $q = "CREATE TABLE `gasMileage` (
         `assetId` int(8) NOT NULL DEFAULT '0',
         `transactionId` int(12) NOT NULL DEFAULT '0',
         `gasPrice` double(6,2) DEFAULT NULL,
         `gasPumped` double(6,2) DEFAULT NULL,
         `amount` double(10,2) DEFAULT NULL,
         `mileage` int(8) DEFAULT NULL,
         PRIMARY KEY (`assetId`,`transactionId`)";
    $dbh->query($q);

    $q = 'CREATE TABLE `vehicleInfo` (
          `assetId` int(8) NOT NULL,
          `vin` varchar(64) DEFAULT NULL,
          `startingOdometer` int(8) DEFAULT NULL,
          `maintenanceNotes` text,
          PRIMARY KEY (`assetId`))';
    $dbh->query($q);
  }


  public static function dropTables() {
    $dbh = dbHandle();
    $q = "DROP TABLE vehicleMaintenance";
    $dbh->query($q);
  }



  public static function assetListingCustomizations(&$assets) { 
    if (isset($assets['Automobile'])) { 
      foreach ($assets['Automobile'] as $i => $info) { 
        $assets['Automobile'][$i]['nameExtra'] = '&nbsp; ' . 
          '<a href="/automobile/log?id='.$info['id'].'">'.
            '[Vehicle Info]'.
          '</a>';
      }
    }
  }


  public static function getAll() {
    $dbh = dbHandle();
    $stmt = $dbh->prepare("SELECT * FROM assets WHERE sold=0 AND categoryId=? ORDER BY name");
    $stmt->execute(array(Automobile::getCategoryId()));
    $results = $stmt->fetchAll(PDO::FETCH_ASSOC); 
    $cars = array();
    if ($results) { foreach ($results as $row) { $cars[] = $row; }}
    return $cars;
  }


  public static function vehicleSelect($id, $carId, $showBlank) {
    $cars = Automobile::getAll();

    $html = '<select id="'.$id.'" name="'.$id.'">';
    if ($showBlank) { $html .= '<option value=""></option>'; }

    for ($i = 0; $i < count($cars); $i++) {
      $car = $cars[$i];
      $html .= '<option value="'.$car['id'].'" '.($carId==$car['id'] ? 'SELECTED' : '').'>';
      $html .= $car['name'].'</option>';
    }
    $html .= '</select>';
    return $html;
  }



  public static function updateMaintenanceNotes($assetId, $notes) { 
    try {
      $dbh = dbHandle(0);
      $q = 'UPDATE vehicleInfo SET maintenanceNotes=:notes WHERE assetId=:id';
      $stmt = $dbh->prepare($q);
      $stmt->bindParam(':id', $assetId);
      $stmt->bindParam(':notes', $notes);
      $stmt->execute();
      return 1; 
    }
    catch (PDOException $e) { return 0; }
  }


  public static function transactionEditFields(&$html) {
    $html .= "<script>window.automobiles = '" . json_encode(Automobile::getAll()) . "';</script>";
    
    $html .= '<tr id="maintainenanceLogHeader" class="maintenance">'.
            '<td class="label" colspan="2" style="text-align:left">'.
             '<big>Maintenance Log</big>&nbsp; &nbsp;'.
             '<img class="maintenanceLogAddIcon" src="/images/add.png" onclick="addMaintenanceLogEntry()">'.
             '<input type="hidden" id="maintenanceRowCount" value="0">'.
           '</tr>';

    $html .= '<tr id="autoTaxLogHeader" class="autoTax">'.
            '<td class="label" colspan="2" style="text-align:left">'.
             '<big>Automobile Tax Log</big>&nbsp; &nbsp;'.
             '<img class="autoTaxLogAddIcon" src="/images/add.png" onclick="addAutoTaxLogEntry()">'.
             '<input type="hidden" id="autoTaxRowCount" value="0">'.
      '</tr>';

    $html .= '<tr id="autoInsuranceLogHeader" class="autoInsurance">'.
            '<td class="label" colspan="2" style="text-align:left">'.
             '<big>Auto Insurance Log</big>&nbsp; &nbsp;'.
             '<img class="autoInsuranceLogAddIcon" src="/images/add.png" onclick="addAutoInsuranceLogEntry()">'.
             '<input type="hidden" id="autoInsuranceRowCount" value="0">'.
      '</tr>';

    $html .=
      '<tr class="gasMileage"><td></td><td><br></td></tr>'. // spacer   
      '<tr id="gasMileageHeader" class="gasMileage">'.
        '<td class="label" colspan="2" style="text-align:left">'.
          '<input type="hidden" id="gasMileageRowCount" value="0">'.
          '<big>Mileage Log</big>&nbsp; &nbsp;'.
          '<img class="gasMileageLogAddIcon" src="/images/add.png" onclick="addGasMileageLogEntry()">'.
        '</td>'.
      '</tr>';
  }



  public static function gasMileageTransactionSave($txId) {
    $amt = -1;
    $rowCount = $_REQUEST['gasMileageRowCount'];

    $dbh = dbHandle();
    $q = 'DELETE FROM gasMileage WHERE transactionId=:txId';
    $stmt = $dbh->prepare($q);
    $stmt->bindParam(':txId', $txId);
    $stmt->execute();

    if ($rowCount > 0) {
      $q = 'REPLACE INTO gasMileage '.
           '(assetId, transactionId, gasPrice, gasPumped, amount, mileage) '.
	'VALUES(:assetId, :txId, :price, :gasPumped, :amt, :mileage)';

      for ($i = 1; $i <= $rowCount; $i++) {
        if ($_REQUEST['gasMileageVehicle'.$i] == '') { continue; }

	$amt = $_REQUEST['pricePerGallon'.$i] * $_REQUEST['gallonsPumped'.$i];

	$stmt = $dbh->prepare($q);
        $stmt->bindParam(':assetId', $_REQUEST['gasMileageVehicle'.$i]);
        $stmt->bindParam(':txId', $txId);
        $stmt->bindParam(':price', $_REQUEST['pricePerGallon'.$i]);
        $stmt->bindParam(':gasPumped', $_REQUEST['gallonsPumped'.$i]);
        $stmt->bindParam(':amt', $amt);
        $stmt->bindParam(':mileage', $_REQUEST['gasMileageOdometer'.$i]);
        $stmt->execute();
      }
    }
  }



  public static function vehicleMaintenanceTransactionSave($txId) { 
    $amt = -1;
    $rowCount = $_REQUEST['maintenanceRowCount'];

    $dbh = dbHandle();
    $q = 'DELETE FROM vehicleMaintenance WHERE transactionId=:txId';
    $stmt = $dbh->prepare($q);
    $stmt->bindParam(':txId', $txId);
    $stmt->execute();
 
    if ($rowCount == 0) { return; }
    else { 
      $q = 'REPLACE INTO vehicleMaintenance '.
           '(assetId,transactionId,amount,datePerformed,mileage,notes) '.
           'VALUES(:assetId,:txId,:amt,:date,:mileage,:notes)';
      for ($i = 1; $i <= $rowCount; $i++) { 
        if ($_REQUEST['maintenanceVehicle'.$i] == '') { continue; }
        $stmt = $dbh->prepare($q);
        $stmt->bindParam(':assetId', $_REQUEST['maintenanceVehicle'.$i]);
        $stmt->bindParam(':txId', $transactionId);
        $stmt->bindParam(':amt', $_REQUEST['maintenanceCost'.$i]);
        $stmt->bindParam(':date', $_REQUEST['date']);
        $stmt->bindParam(':mileage', $_REQUEST['maintenanceMileage'.$i]);
        $stmt->bindParam(':notes', $_REQUEST['maintenanceNotes'.$i]);
        $stmt->execute();
      }
    }
  }



  public static function vehicleTaxTransactionSave($txId) {
    $amt = -1;
    $rowCount = $_REQUEST['autoTaxRowCount'];

    $dbh = dbHandle();
    $q = 'DELETE FROM vehicleTax WHERE transactionId=:txId';
    $stmt = $dbh->prepare($q);
    $stmt->bindParam(':txId', $txId);
    $stmt->execute();

    if ($rowCount > 0) { 
      $q = 'REPLACE INTO vehicleTax (assetId,transactionId,amount) '.
           'VALUES(:assetId, :txId, :amt)';

      for ($i = 1; $i <= $rowCount; $i++) {
        if ($_REQUEST['autoTaxVehicle'.$i] == '') { continue; }
        $stmt = $dbh->prepare($q);
        $stmt->bindParam(':assetId', $_REQUEST['autoTaxVehicle'.$i]);
        $stmt->bindParam(':txId', $transactionId);
        $stmt->bindParam(':amt', $_REQUEST['autoTaxCost'.$i]);
        $stmt->execute();
      }
    }
  }


  public static function vehicleInsuranceTransactionSave($txId) { 
    $amt = -1;
    $rowCount = $_REQUEST['autoInsuranceRowCount'];

    $dbh = dbHandle();
    $q = 'DELETE FROM vehicleInsurance WHERE transactionId=:txId';
    $stmt = $dbh->prepare($q);
    $stmt->bindParam(':txId', $txId);
    $stmt->execute();

    if ($rowCount > 0) {
      $q = 'REPLACE INTO vehicleInsurance (assetId,transactionId,amount) '.
           'VALUES(:assetId, :txId, :amt)';

      for ($i = 1; $i <= $rowCount; $i++) {
        if ($_REQUEST['autoInsuranceVehicle'.$i] == '') { continue; }

        $stmt = $dbh->prepare($q);
        $stmt->bindParam(':assetId', $_REQUEST['autoInsuranceVehicle'.$i]);
        $stmt->bindParam(':txId', $txId);
        $stmt->bindParam(':amt', $_REQUEST['autoInsuranceCost'.$i]);
        $stmt->execute();
      }
    }
  }


  public static function transactionCreate($transactionId) {
    self::transactionUpdate($transactionId);
  }

  public static function transactionUpdate($transactionId) {
    if (isset($_REQUEST['gasMileageRowCount']) && ($_REQUEST['gasMileageRowCount'] > 0)) {
      self::gasMileageTransactionSave($transactionId);
    }
    if (isset($_REQUEST['maintenanceRowCount']) && ($_REQUEST['maintenanceRowCount'] > 0)) {
      self::vehicleMaintenanceTransactionSave($transactionId);
    }
    if (isset($_REQUEST['autoTaxRowCount']) && ($_REQUEST['autoTaxRowCount'] > 0)) {
      self::vehicleTaxTransactionSave($transactionId);
    }
    if (isset($_REQUEST['autoInsuranceRowCount']) && ($_REQUEST['autoInsuranceRowCount'] > 0)) {
      self::vehicleInsuranceTransactionSave($transactionId);
    }
  }



  public static function getGasMileage($id) {
    $dbh = dbHandle(1);
    $q = 'SELECT DATE_FORMAT(gm.date,"%Y-%m-%d") as date, t.amount, 
            gm.transactionId, gm.mileage, gm.gasPumped, gm.gasPrice, e.name as entityName
          FROM gasMileage gm
          LEFT JOIN transactions t ON gm.transactionId=t.id
          LEFT JOIN entities e ON t.entityId=e.id
          WHERE gm.assetId=?
          ORDER BY date, t.id';
    $stmt = $dbh->prepare($q);
    $stmt->execute(array($id));
    $results = $stmt->fetchAll(PDO::FETCH_ASSOC);
    $rows = array();
    if ($results) foreach ($results as $row) { $rows[] = $row; }
    return $rows;
  }


  public static function getGasMileageInfo($txId) { 
    $dbh = dbHandle();
    $q = 'SELECT * FROM gasMileage WHERE transactionId=:txId';
    $stmt = $dbh->prepare($q);
    $stmt->bindParam(':txId', $txId);
    $stmt->execute();
    $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
    return $rows;
  }

  public static function getMaintenanceInfo($txId) {
    $dbh = dbHandle();
    $q = 'SELECT * FROM vehicleMaintenance WHERE transactionId=:txId';
    $stmt = $dbh->prepare($q);
    $stmt->bindParam(':txId', $txId);
    $stmt->execute();
    $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
    return $rows;
  }


  public static function getTaxInfo($txId) {
    $dbh = dbHandle();
    $q = 'SELECT * FROM vehicleTax WHERE transactionId=:txId';
    $stmt = $dbh->prepare($q);
    $stmt->bindParam(':txId', $txId);
    $stmt->execute();
    $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
    return $rows;
  }



  public static function getInsuranceInfo($txId) {
    $dbh = dbHandle();
    $q = 'SELECT * FROM vehicleInsurance WHERE transactionId=:txId';
    $stmt = $dbh->prepare($q);
    $stmt->bindParam(':txId', $txId);
    $stmt->execute();
    $rows = $stmt->fetch(PDO::FETCH_ASSOC);
    return $rows;
  }


  public static function getLatestMileage($id) { 
    $dbh = dbHandle(1);
    $q = 'SELECT max(mileage) as mileage FROM gasMileage g WHERE g.assetId=:assetId'.
         ' UNION '.
         'SELECT max(mileage) as mileage FROM vehicleMaintenance vm WHERE vm.assetId=:assetId '.
         'ORDER BY mileage DESC LIMIT 1';
    $stmt = $dbh->prepare($q);
    $stmt->bindValue(':assetId', $id);
    $stmt->execute();
    $results = $stmt->fetchAll(PDO::FETCH_ASSOC);
    if ($results) { 
      foreach ($results as $row) { return $row['mileage']; }
    }
    else { return ''; }
  }


  public static function getInfo($id) {
    $dbh = dbHandle(1);
    $q = 'SELECT a.*,vi.* FROM assets a LEFT JOIN vehicleInfo vi ON a.id=vi.assetId WHERE id=?';
    $stmt = $dbh->prepare($q);
    $stmt->execute(array($id));
    $results = $stmt->fetchAll(PDO::FETCH_ASSOC);
    if ($results) { foreach ($results as $row) { return $row; }}
    else { return ''; }
  }


  public static function getVehicleLog($id, $view) { 
    $dbh = dbHandle(1);

    $q = 'SELECT DATE_FORMAT(vm.datePerformed, "%Y-%m-%d") as date, vm.amount, vm.transactionId, vm.mileage, vm.notes
          FROM vehicleMaintenance vm 
          LEFT JOIN transactions t ON vm.transactionId=t.id 
          WHERE vm.assetId=:assetId';

    if (isset($view) && ($view == "log")) {
      $q .= ' UNION
             SELECT DATE_FORMAT(t.date,"%Y-%m-%d") as date, t.amount, gm.transactionId, gm.mileage, CONCAT(gm.gasPumped, "gal @ ", gm.gasPrice) as notes  
             FROM gasMileage gm LEFT JOIN transactions t ON gm.transactionId=t.id
             WHERE gm.assetId=:assetId
             UNION 
             SELECT DATE_FORMAT(t.date,"%Y-%m-%d") as date, vi.amount, vi.transactionId, "-" as mileage, 
                    CONCAT(e.name," (Insurance)") as notes
             FROM vehicleInsurance vi 
             LEFT JOIN transactions t ON vi.transactionId=t.id
             LEFT JOIN entities e ON t.entityId=e.id
             WHERE vi.assetId=:assetId
             UNION
             SELECT DATE_FORMAT(t.date,"%Y-%m-%d") as date, vt.amount, vt.transactionId, "-" as mileage,
                    CONCAT(e.name," (Auto Registration / Tax)") as notes
             FROM vehicleTax vt
             LEFT JOIN transactions t ON vt.transactionId=t.id
             LEFT JOIN entities e ON t.entityId=e.id   
             WHERE vt.assetId=:assetId 
             ORDER BY date';
    }
    else { $q .= ' ORDER BY date'; }

    try { 
      $stmt = $dbh->prepare($q);
      $stmt->bindValue(':assetId', $id);
      $stmt->execute();
      $results = $stmt->fetchAll(PDO::FETCH_ASSOC);
      $rows = array();
      if ($results) { 
        foreach ($results as $row) { $rows[] = $row; }
      }
      return $rows;
    }
    catch (PDOException $e) { 
      die($e->getMessage());
    }
  }



  public static function getTCO($id) {
    $tco = array('depreciation' => 0, 'interest' => 0, 'fuel' => 0, 'taxes' => 0,
                 'maintenance' => 0, 'insurance' => 0);

    $dbh = dbHandle(1);
    $q = 'SELECT initialValue-currentValue as depreciation FROM assets WHERE id=?';
    $stmt = $dbh->prepare($q);
    $stmt->execute(array($id));
    $results = $stmt->fetchAll(PDO::FETCH_ASSOC);
    if ($results) {
      foreach ($results as $row) { $tco['depreciation'] = $row['depreciation']; }
    }

    $q = 'select id FROM accounts a WHERE assetId=?';
    $stmt = $dbh->prepare($q);
    $stmt->execute(array($id));
    $results = $stmt->fetchAll(PDO::FETCH_ASSOC);
    if ($results) {
      foreach ($results as $row) {
	$accountId = $row['id'];
	if ($accountId != '') {
          $q2 = 'SELECT SUM(t.interest) as interest '.
                'FROM transactions t  '.
                'WHERE t.accountId=?';
          $stmt2 = $dbh->prepare($q2);
          $stmt2->execute(array($accountId));
          $results2 = $stmt2->fetchAll(PDO::FETCH_ASSOC);
          if ($results2) {
            foreach ($results2 as $row2) { $tco['interest'] = $row2['interest']; }
          }
	}
      }
    }

    $q = 'SELECT SUM(amount) as fuel FROM gasMileage WHERE assetId=?';
    $stmt = $dbh->prepare($q);
    $stmt->execute(array($id));
    $results = $stmt->fetchAll(PDO::FETCH_ASSOC);
    if ($results) {
      foreach ($results as $row) { $tco['fuel'] = $row['fuel']; }
    }

    $q = 'SELECT SUM(amount) as taxes FROM vehicleTax WHERE assetId='.$id;
    $stmt = $dbh->prepare($q);
    $stmt->execute(array($id));
    $results = $stmt->fetchAll(PDO::FETCH_ASSOC);
    if ($results) {
      foreach ($results as $row) { $tco['taxes'] = $row['taxes']; }
    }

    $q = 'SELECT SUM(amount) as maintenance FROM vehicleMaintenance WHERE assetId='.$id;
    $stmt = $dbh->prepare($q);
    $stmt->execute(array($id));
    $results = $stmt->fetchAll(PDO::FETCH_ASSOC);
    if ($results) {
      foreach ($results as $row) { $tco['maintenance'] = $row['maintenance']; }
    }

    $q = 'SELECT SUM(amount) as insurance FROM vehicleInsurance WHERE assetId='.$id;
    $stmt = $dbh->prepare($q);
    $stmt->execute(array($id));
    $results = $stmt->fetchAll(PDO::FETCH_ASSOC);
    if ($results) {
      foreach ($results as $row) { $tco['insurance'] = $row['insurance']; }
    }

    $totalCost = $tco['depreciation'] + $tco['interest'] + $tco['fuel'] + 
                 $tco['insurance'] + $tco['maintenance'] + $tco['taxes'];
    $tco['totalCost'] = $totalCost;
    return $tco;   
  }

 
  public static function getGasPriceReportData() {
    $dbh = dbHandle(1);
    $q = 'SELECT UNIX_TIMESTAMP(date)*1000 as ts,gasPrice FROM gasMileage ORDER BY date';
    $results = $dbh->query($q);
    $rows = $results->fetchAll(PDO::FETCH_ASSOC);
    return $rows;
  }
}