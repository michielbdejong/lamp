<?php
require_once 'db.php';

class Storage {
  static function tokenGoodForReading($userAddress, $category, $token) {
    return true;
  }
  static function tokenGoodForWriting($userAddress, $category, $token) {
    return true;
  }
  public static function showStorage($method, $uri, $data, $token) {
    header('Access-Control-Allow-Origin: *');
    header('Access-Control-Allow-Methods: GET, PUT, DELETE');
    header('Access-Control-Allow-Headers: content-length, authorization');
    $uriParts = explode('/', $uri);
    if(count($uriParts) >= 5) {
      $userAddress = $uriParts[2].'@'.Config::$usersHost;
      $category = $uriParts[3];
      $itemKey = implode('/', array_slice($uriParts, 4));
    }
    if($method == 'GET') {
      if(self::tokenGoodForReading($userAddress, $category, $token) {
        $value = Db::getStrings(array('value'), 'items', array('user_address' => $userAddress, 'category' => $category, 'key_name' => $itemKey));
      }
   var_dump($userAddress);
var_dump($category);var_dump($itemKey);
      var_dump($token);
var_dump($value);
    }
    echo 'hi, this is your storage speaking';
  }
}
