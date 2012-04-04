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
      if(self::tokenGoodForReading($userAddress, $category, $token)) {
        $value = Db::getStrings(array('value'), 'items', array('user_address' => $userAddress, 'category' => $category, 'key_name' => $itemKey));
        if($value) {
          header('HTTP/1.0 200 OK');
          echo($value[0]);
        } else {
          header('HTTP/1.0 404 Not Found');
        }
      } else {
        header('403 Access Denied');
      }
    } if($method == 'PUT') {
      if(self::tokenGoodForWriting($userAddress, $category, $token)) {
        $value = Db::setStrings(array('value' => $data), 'items', array('user_address' => $userAddress, 'category' => $category, 'key_name' => $itemKey));
        if($value) {
          header('HTTP/1.0 200 OK');
          echo($value);
        } else {
          header('HTTP/1.0 404 Not Found');
        }
      } else {
        header('403 Access Denied');
      }
    } if($method == 'DELETE') {
      if(self::tokenGoodForWriting($userAddress, $category, $token)) {
        $value = Db::deleteRow('items', array('user_address' => $userAddress, 'category' => $category, 'key_name' => $itemKey));
        if($value) {
          header('HTTP/1.0 200 OK');
          echo($value);
        } else {
          header('HTTP/1.0 404 Not Found');
        }
      } else {
        header('403 Access Denied');
      }
    }
  }
}
