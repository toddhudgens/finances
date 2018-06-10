<?php


function index() {
  $accounts = Account::getAll('accountType');
  $accountsTotal = 0;
  $assetsValue = 0;
  $accountLiabilities = 0;
  $accountTypes = AccountType::getAll();
  $assetsValue = Asset::totalValue();

  foreach ($accountTypes as $i => $type) { 
    if (isset($accounts[$type])) { 
      $total = 0;
      foreach ($accounts[$type] as $accountInfo) { $total += $accountInfo['balance']; }
      if ($total > 0) { $assetsValue += $total; } else { $accountLiabilities += $total; }
    }
  }
  $networth = $assetsValue + $accountLiabilities;

  $reports = array('report/asset-allocation' => "Asset Allocation",
                   'report/liquid-asset-allocation'=>"Liquid Asset Allocation",
                   'automobiles' => "Automobile Overview",
                   'report/gas-prices' => 'Gas Price Report',
                   'report/expenses-by-category' => 'Expenses by Category',
                   'report/expenses-by-entity' => 'Expenses by Entity',
                   'report/expenses-by-tag' => 'Expenses by Tag',
                   'report/networth' => 'Net Worth Report',
                   '/monthly-budget' => 'Monthly Budget',
                   'mortgage-calculator' => 'Mortgage Calculator');

  $expenses = Transaction::getTotalForFrontPage("Withdrawal", 30);
  $income = Transaction::getTotalForFrontPage("Deposit", 30);
  $topCategories = Transaction::getTotalsByCategory(30);

  $viewParams = array('pageTitle' => 'My Accounts',
                      'accounts' => $accounts,
                      'accountTypes' => $accountTypes,
                      'assetsValue' => $assetsValue,
		      'accountLiabilities' => $accountLiabilities,
                      'networth' => $networth,
                      'reports' => $reports,
                      'income' => $income,
                      'expenses' => $expenses,
                      'topCategories' => $topCategories);
  Twig::render('user-homepage.twig', $viewParams);
}


function login() {
  if (!loggedIn()) { 
    Twig::render('login-form.twig', array());
  }
  else { redirectToPage('/', 0); }
}



function loginSubmit() {
  $result = User::login($_POST['username'], $_POST['password']);
  if ($result) { 
    $_SESSION['valid'] = 1;
    $_SESSION['name'] = $_POST['username'];
    $_SESSION['userId'] = $result['id'];
    Twig::render('login-success.twig', array());
    redirectToPage("/", 1);
  }
  else { 
    $_SESSION['valid'] = null;
    Twig::render('login-failure.twig', array());
    redirectToPage('/', 3);
  }
}



function logout() {
  session_destroy();
  Twig::render('logout.twig', array());
  redirectToPage('/', 2);
}



function sessionUpdate() {
  ini_set('session.gc_maxlifetime', 900);
}

?>