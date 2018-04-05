<?php

class User {

public static function login($un, $pw) {
  $salt = '&CASH_RULES_EVERYTHING_AROUND_ME_CREAM';

  $dbh = dbHandle(0);
  $stmt = $dbh->prepare('SELECT id FROM users WHERE (name = :un AND password = :pw)');
  $stmt->bindValue(':un', $un);
  $stmt->bindValue(':pw', md5($pw.$salt));
  $stmt->execute();
  $result = $stmt->fetch();
  return $result;
}


}