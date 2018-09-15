<?php


class Report {

  public static function buildJSONDataForExpenseReport($totals, $range, $groupBy) { 
    $json = '['; 
    if (!isset($_GET['showAllTimePeriods']) || ($_GET['showAllTimePeriods'] == 0)) {
      foreach ($totals as $grouping => $total) { $json .= '["'.$grouping.'",'.$total.'],'; }
      if (count($totals)) {
	$json = substr($json,0,-1);
      }
    }
    else {
      $currentYear = date('Y');
      $currentMonth = date('m');
      $currentMonthStr = $currentYear . '-' . $currentMonth;

      $dbh = dbHandle(1);
      $q = 'SELECT DATE_FORMAT(min(date),\'%Y\') as firstYear, 
                   DATE_FORMAT(min(date),\'%m\') as firstMonth 
            FROM transactions';
      $results = $dbh->query($q);
      $row = $results->fetch(PDO::FETCH_ASSOC);
      $firstMonth = $row['firstMonth'];
      $firstYear = $row['firstYear'];

      if ($groupBy == "month") {
	if ($range == "currentyear") {
	  for ($i = 1; $i <= $currentMonth; $i++) {
	    $month = $currentYear . '-'. str_pad($i, 2, '0', STR_PAD_LEFT);
	    if (isset($totals[$month])) { 
              $json .= '["' . $month . '",' . $totals[$month] . '],'; 
            }
	    else { $json .= '["'.$month . '",0],'; }
	  }
	}
        else if ($range == "lastyear") { 
          for ($i = 1; $i <= 12; $i++) {
	    $month = ($currentYear-1) . '-' . str_pad($i, 2, '0', STR_PAD_LEFT);
	    if (isset($totals[$month])) {
	      $json .= '["' . $month . '",' . $totals[$month] . '],';
	    }
	    else { $json .= '["'.$month . '",0],'; }
          }
        }
        else if ($range == "lasttwoyears") {
          for ($y = ($currentYear-2); $y < $currentYear; $y++) {
            for ($m = 1; $m <= 12; $m++) {
              $month = $y . '-' . str_pad($m, 2, '0', STR_PAD_LEFT);
              if ($month > $currentMonthStr) { continue; }
              if (isset($totals[$month])) {
                $json .= '["' . $month . '",' . $totals[$month] . '],';
              }
              else { $json .= '["'.$month . '",0],'; }
            }
          }
        }
	else if ($range == "lastthreeyears") {
	  for ($y = ($currentYear-3); $y < $currentYear; $y++) {
	    for ($m = 1; $m <= 12; $m++) {
	      $month = $y . '-' . str_pad($m, 2, '0', STR_PAD_LEFT);
	      if ($month > $currentMonthStr) { continue; }

	      if (isset($totals[$month])) { 
                $json .= '["' . $month . '",' . $totals[$month] . '],'; 
              }
	      else { $json .= '["'.$month . '",0],'; }
	    }
	  }
	}
	else if ($range == "all") {
	  $q = 'select DATE_FORMAT(min(date),\'%Y\') as firstYear, DATE_FORMAT(min(date),\'%m\') as firstMonth FROM transactions';
          $results = $dbh->query($q);
          $row = $results->fetch(PDO::FETCH_ASSOC);
	  $month = $firstMonth;

	  for ($y = $row['firstYear']; $y <= $currentYear; $y++) {
	    for ($m = $firstMonth; $m <= 12; $m++) {
	      $month = $y . '-' . str_pad($m, 2, '0', STR_PAD_LEFT);
	      if (isset($totals[$month])) { $json .= '["' . $month . '",' . $totals[$month] . '],'; }
	      else { $json .= '["'.$month . '",0],'; }
	    }
	    $month = 1;
	  }
	}
      }
      else if ($groupBy == "year") {
	if ($range == "all") { $startYear = $firstYear; }
	else if ($range == "lastthreeyears") { $startYear = $currentYear - 3; }
	else if ($range == "lasttwoyears") { $startYear = $currentYear - 2; }
        else if ($range == "lastyear") { $startYear = $currentYear-1; } 
	else if ($range == "currentyear") { $startYear = $currentYear; }

	for ($y = $startYear; $y <= $currentYear; $y++) {
	  $label = $y;
	  if (isset($totals[$label])) { $json .= '["' . $label . '",' . $totals[$label] . '],'; }
	  else { $json .= '["'.$label . '",0],'; }
	}
      }
    }
    $json .= '];';
    return $json;
  }


  public static function categoryExpensesByEntity($categoryId, $range='') {
    $rangeCriteria = '';
    if ($range == 'lastyear') { $rangeCriteria = ' AND YEAR(t.date)=(YEAR(CURRENT_TIMESTAMP)-1) '; }
    else if ($range == 'lasttwoyears') { $rangeCriteria = ' AND (YEAR(CURRENT_TIMESTAMP)-YEAR(date)<2) '; }
    else if ($range == 'lastthreeyears') { $rangeCriteria = ' AND (YEAR(CURRENT_TIMESTAMP)-YEAR(date)<3) '; }
    else if ($range == 'currentyear') { $rangeCriteria = ' AND (YEAR(CURRENT_TIMESTAMP)=YEAR(date)) '; }
    else if ($range == 'all') {}

    $categories = explode(',', $categoryId);
    $qMarks = str_repeat('?,', count($categories) - 1) . '?';

    $dbh = dbHandle(1);
    $q = "SELECT
           e.id,
           e.name as entityName,
           ABS(SUM(tc.amount)) as total,
           GROUP_CONCAT(t.notes SEPARATOR '|') as notes
          FROM transactions t, transactionCategory tc, entities e
          WHERE
           t.id=tc.transactionId AND
           tc.categoryId IN ($qMarks) AND t.entityId=e.id".
           $rangeCriteria . '
          GROUP BY e.id
          ORDER BY e.name';
    //die($q);
    $stmt = $dbh->prepare($q);
    $stmt->execute($categories);
    $results = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // split notes
    foreach ($results as $i => $row) { 
      $notes = explode('|', $row['notes']);
      $notesArr = array();
      foreach ($notes as $j => $note) { 
        if (($note != '') && ($note != ' ')){ $notesArr[] = $note; } 
      }
      $results[$i]['notesCollection'] = $notesArr;
    }
    return $results;
  }


  public static function entityExpensesByCategory($entityId, $range) {
    $rangeCriteria = '';
    if ($range == 'lastyear') { $rangeCriteria = ' AND YEAR(t.date)=(YEAR(CURRENT_TIMESTAMP)-1) '; }
    else if ($range == 'lasttwoyears') { $rangeCriteria = ' AND (YEAR(CURRENT_TIMESTAMP)-YEAR(date)<2) '; }
    else if ($range == 'lastthreeyears') { $rangeCriteria = ' AND (YEAR(CURRENT_TIMESTAMP)-YEAR(date)<3) '; }
    else if ($range == 'currentyear') { $rangeCriteria = ' AND (YEAR(CURRENT_TIMESTAMP)=YEAR(date)) '; }
    else if ($range == 'all') {}

    $dbh = dbHandle(1);
    $q = "SELECT
           c.id,
           c.name as categoryName,
           ABS(SUM(tc.amount)) as total,
           GROUP_CONCAT(t.notes SEPARATOR '|') as notes
          FROM transactions t, transactionCategory tc, entities e, categories c
          WHERE
           t.id=tc.transactionId AND
           t.entityId IN (?) AND t.entityId=e.id AND tc.categoryId=c.id ".
           $rangeCriteria . '
          GROUP BY c.id
          ORDER BY c.name';
    $stmt = $dbh->prepare($q);
    $stmt->execute(array($entityId));
    $results = $stmt->fetchAll(PDO::FETCH_ASSOC);
    return $results;
  }
}