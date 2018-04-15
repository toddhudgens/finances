<?php



function save() {
  if ($_GET['accountId'] == "") { $response = Account::add(); }
  else { $response = Account::update(); }
  echo json_encode($response);
}


function showTransactions() {
  global $title; 

  // load account/category/entity info
  $balance = 0; $reportType = ''; $nextTxNum = ''; $accountId = ''; 
  $entityId = ''; $categoryId = ''; $showInterest = 0;
  $showMtgCalcLink = 0; $showStockAssetsLink = 0;
  $transactionTypes = array('Withdrawal', 'Deposit', 'Transfer');
  if (isset($_GET['id'])) { 
    $accountId = $_GET['id'];
    $accountInfo = Account::get($accountId);
    $balance = $accountInfo['initialBalance']; 
    $title = $accountInfo['name'];
    $reportType = 'Account';
    $nextTxNum = Account::getNextTransactionNumber($accountId);
    if (isset($accountInfo['entityId'])) { $entityId = $accountInfo['entityId']; } 
    else { $entityId = ''; }
    if ($accountInfo['accountType'] == 4) { $showMtgCalcLink = 1; } 
    if (($accountInfo['accountType'] == 4) || ($accountInfo['accountType'] == 6)) { 
      $showInterest = 1;
    }
    if ($accountInfo['accountType'] == 7) { 
      $transactionTypes[] = 'Stock Purchase';
      $transactionTypes[] = 'Stock Sale';
      $showStockAssetsLink = 1;
    }
  }
  else if (isset($_GET['categoryId'])) { 
    $categoryId = $_GET['categoryId'];
    $categoryInfo = Category::get($_GET['categoryId']);
    $title = $categoryInfo['name'];
    $reportType = 'Category';
  }
  else if (isset($_GET['entityId'])) { 
    $entityInfo = Entity::get($_GET['entityId']);
    $title = $entityInfo['name'];
    $reportType = 'Entity';
  }
  else if (isset($_GET['tagId'])) { 
    $tagInfo = Tag::get($_GET['tagId']);
    $title = $tagInfo['name'];
    $reportType = 'Tag';
  }

  if ($reportType != "Account") { $showAccountName = 1; } else { $showAccountName = 0; }

  $timefilter = '';
  if (isset($_GET['dateRange'])) { 
    $timefilter = '&dateRange='.$_GET['dateRange'];
    $dateRange = $_GET['dateRange']; 
  } 
  else { 
    $dateRange = 'all'; 
  }
  if (isset($_GET['month'])) { 
    $month = $_GET['month']; 
    $timefilter = '&month='.$month;
  }
  else { $month = ''; }

  $transactions = Transaction::getForShow();
  //echo '<pre>' . print_r($transactions, true) . '</pre><br><br>';
  $entityIdToAccountIdMap = Account::getEntityIdMap();
  //print_r($entityIdToAccountIdMap); die();

  if (count($transactions)) { 
    for ($j = 0; $j < count($transactions); $j++) { 
      $row = $transactions[$j];

      // set withdrawal and deposit amounts for a transfer
      $withdrawal = ''; $deposit = ''; 
      if ($row['transactionType'] == "Transfer") { 
        if ($row['amount'] > 0) { 
 	  $deposit = $row['amount']; 
        }
        else {
	  $withdrawal = $row['amount'];
        }
      }
      else if ($row['transactionType'] == "Deposit") { 
        $deposit = $row['amount'];
      }
      else if ($row['transactionType'] == "Withdrawal") { 
        $withdrawal = $row['amount']; 
      }

      // update the balance
      if ($reportType == "Account") { $balance = $row['accountBalance']; }
      else if (($reportType == "Entity") || ($reportType == "Tag")) {
        $balance += $row['amount']; 
      }
      else if ($reportType == "Category") { 
        if ($row['categoryAmounts'] > 0) {         
  	  $deposit = $row['categoryAmounts'];
	  $balance += $deposit;
        }
        else { 
          if (isset($_GET['categoryId'])) { 
	    $catIds = explode(",", $row['categoryId']);
            $amounts = explode(",", $row['categoryAmounts']);
	    for ($i = 0; $i < count($catIds); $i++) {
	      if ($catIds[$i] == $_GET['categoryId']) { 
	        $withdrawal = money_format('%i', abs($amounts[$i]));
	        $balance -= abs($amounts[$i]);
	      }
	    }
	  }
        }
      }
      $transactions[$j]['withdrawal'] = $withdrawal;
      $transactions[$j]['deposit'] = $deposit;
      $transactions[$j]['balance'] = $balance; 
     
      // set the hidden vars for this row
      $hiddenVars = array('id' => $row['id'],
                          'date' => $row['date'],
                          'num' => $row['transactionNumber'],
                          'p' => $row['entityId'],
                          'pName' => $row['payee'],
                          'cat' => $row['categoryId'],
                          'catAmt' => $row['categoryAmounts'],
                          'tax' => $row['tax'],
                          'interest' => $row['interest'],
                          'total' => $row['amount'],
                          'type' => $row['transactionType'],
                          'accountId' => $row['accountId']);

      if ($row['notes'] != "") { $hiddenVars['notes'] = $row['notes']; }
      if ($row['tagIds'] != null) { $hiddenVars['tagIds'] = $row['tagIds']; }
      if ($row['tagNames'] != null) { $hiddenVars['tagNames'] = $row['tagNames']; }
  
      if ($row['transactionType'] == "Transfer") { 
        if ($row['amount'] < 0) { 
          if (isset($accountInfo['entityId'])) { 
            $hiddenVars['fromAccount']= $accountInfo['entityId']; 
          } 
          else { $hiddenVars['fromAccount'] = $row['accountId']; }
          $hiddenVars['fromAccount'] = $row['accountId'];
	  $hiddenVars['toAccount'] = $row['entityId'];
        }
        else { 
          if (isset($accountInfo['entityId'])) { $hiddenVars['toAccount'] = $accountInfo['entityId']; }
          else { $hiddenVars['toAccount'] = $row['accountId']; }
	  $hiddenVars['fromAccount'] = $row['entityId'];
        }
	$transactions[$j]['transferAccountId'] = $entityIdToAccountIdMap[$row['entityId']];
      }

      $transactions[$j]['hiddenVars'] = $hiddenVars;

      // Build category link
      if ($row['transactionType'] == "Transfer") { 
        $categoryLink = 
          '<a href="/account/show-transactions?categoryId=4">Transfer</a>'; 
        $transactions[$j]['categoryLink'] = $categoryLink;
      }
      else if ($row['stockTransactionType'] != '') { 
        $transactions[$j]['categoryLink'] = 'Stock ' . $row['stockTransactionType'];
        $transactions[$j]['payee'] = $row['ticker'] . ', ' . 
          $row['shares'] . ' @ $' . $row['sharePrice'];
      }
      else { 
        $categories = explode(",", $row['category']);
        $categoryIds = explode(",", $row['categoryId']);
        $categoryLink = '';
        for ($c = 0; $c < count($categories); $c++) {
          if ($c > 0) { $categoryLink .= ', '; }
          else {}
          $categoryLink .= '<a href="?categoryId='.$categoryIds[$c].$timefilter.'">' . 
                             trim($categories[$c]).'</a>';
        }
        $transactions[$j]['categoryLink'] = $categoryLink; 
      }
    }
  }

  $transactionEditFields = '';
  Plugins::run('transactionEditFields', array(&$transactionEditFields));

  $viewParams = array('pageTitle' => $title,
                      'reportType' => $reportType,
                      'nextTxNumber' => $nextTxNum,
                      'today' => date('Y-m-d'),
                      'dateRange' => $dateRange, 
                      'month' => $month,
                      'timefilter' => $timefilter,
                      'categories' => Category::getAll(),
                      'accounts' => Account::getSelectValues(),
                      'accountsFull' => Account::getAll(),
                      'accountId' => $accountId, 
                      'entityId' => $entityId,
                      'categoryId' => $categoryId,
                      'showAccountName' => $showAccountName,
                      'showMtgCalcLink' => $showMtgCalcLink,
                      'showInterest' => $showInterest,
                      'showStockAssetsLink' => $showStockAssetsLink,
                      'transactions' => $transactions,
                      'transactionEditFields' => $transactionEditFields,
                      'transactionTypes' => $transactionTypes);
  echo $GLOBALS['twig']->render('transaction-listing.twig', $viewParams);
}


function assets() {
  global $title;

  $accountInfo = Account::get($_GET['id']);
  $assets = Stocks::getForAccount($_GET['id']);

  $title = $accountInfo['name'] . ' - Account Assets';
  $viewParams = array('pageTitle' => $title,
                      'accountId' => $_GET['id'],
                      'assets' => $assets);
  echo $GLOBALS['twig']->render('account-assets.twig', $viewParams);
}

?>
