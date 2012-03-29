<?php
require_once 'db.php';
require_once 'browserid.php';

class Auth {
  static function getSecondaryAddress($primaryAddress) {
    //SELECT secondary FROM address WHERE primary = $primaryAddress:
    $secondaryAddress = Db::getString('secondary', 'address', 'primary', $primaryAddress);
    return $secondaryAddress || $primaryAddress;
  }
  static function checkAccess($assertion, $audience, $userAddress) {
    $authedUserAddress = Browserid::verifyAssertion($assertion, $audience);
    return ($authedUserAddress && ($authedUserAddress == $userAddress || $authedUserAddress == getSecondaryAddress($userAddress)));
  }
  public static function process($assertion, $audience, $userAddress, $scopes, $app) {
    if(checkAccess($assertion, $userAddress)) {
      return getToken($userAddress, $scopes, $app);
    } else {
      return false;
    }
  }
  public static function showOAuth($uri) {
    $pathParts =	explode('/', $uri);
    if(count($pathParts) == 3 && $pathParts[0] == '' && $pathParts[1] == 'oauth') {
      $userAddress = $pathParts[2].'@'.Config::$host;
      var_dump($userAddress);
      var_dump($_GET);
    }
  }
}
