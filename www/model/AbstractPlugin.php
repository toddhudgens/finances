<?php

class AbstractPlugin {

  // database setup / teardown methods
  public static function createTables() {}
  public static function dropTables() {}
  
  // asset methods
  public static function assetCreate($assetId) {}
  public static function assetEditFields(&$html) {}
  public static function assetUpdate($assetId) {}
  public static function assetCategoryExtraInfo() {}
  public static function assetListingCustomizations(&$assets) {}

  // transaction methods
  public static function transactionEditFields(&$html) {}
  public static function transactionCreate($transactionId) {}
  public static function transactionUpdate($transactionId) {}

}

?>