<?php


class Grocery extends AbstractPlugin { 

  public static $foodCategoryIds = array(7, 9, 15, 20, 22, 34, 56, 60, 147);

  public static function transactionCreate($transactionId) {
    self::transactionUpdate($transactionId);
  }

  public static function transactionUpdate($transactionId) {
    $categoryIds = Category::getSubmittedCategories();

    // is this a matching transaction?
    $matchingTransaction = false;
    foreach ($categoryIds as $i => $categoryId) { 
      if (in_array($categoryId, self::$foodCategoryIds)) { 
        $matchingTransaction = true; 
      }
    }

    // if so, make the API request to my food and diet tracker
    if ($matchingTransaction) { 
      $apiEndpoint = 'https://food.toddhudgens.com/add-grocery-trip';
      $requestURI = $apiEndpoint;
      $requestURI .= '?transactionId='.$transactionId;
      $requestURI .= '&userId=1';
      $requestURI .= '&date='.$_REQUEST['date'];
      $requestURI .= '&payee='.urlencode($_REQUEST['payeeName']);
      $requestURI .= '&total='.$_REQUEST['total'];
      $requestURI .= '&notes='.urlencode($_REQUEST['notes']);
      file_get_contents($requestURI);
    }
  }

  
}


?>