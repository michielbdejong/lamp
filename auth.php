<?php
require_once 'db.php';
require_once 'browserid.php';

class Auth {
  static function getSecondaryAddress($primaryAddress) {
    //SELECT secondary FROM address WHERE primary = $primaryAddress:
    $secondaryAddress = Db::getString('secondary_address', 'address', 'primary_address', $primaryAddress);
    return $secondaryAddress ? $secondaryAddress : $primaryAddress;
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
  static function displayDialog($appHost, $scopes, $userAddress) {
    var_dump(self::getSecondaryAddress($userAddress));
  }
  public static function showOAuth($uri) {
    $uriParts = explode('?', $uri);
    if(count($uriParts)==2) {
      $pathParts = explode('/', $uriParts[0]);
      if(count($pathParts) == 3 && $pathParts[0] == '' && $pathParts[1] == 'oauth') {
        $userAddress = $pathParts[2].'@'.Config::$usersHost;
        foreach($_GET as $k => $v) {
          if($k == 'redirect_uri') {
            $redirectUri = $v;
          }
          if($k == 'scope') {
            $scope = $v;
          }
        }
        if((!isset($redirectUri)) || (!isset($scope))) {
          echo 'not the right params!';
        } else {
          $redirectUriParts = explode('/', $redirectUri);
          if(count($redirectUriParts) >= 3) {
            $appHostParts = explode(':', $redirectUriParts[2]);
            $appHost = $appHostParts[0];
            $scopes = explode(',', $scope);
            foreach($scopes as $scope) {
              $scopeParts = explode(':', $scope);
              if(count($scopeParts) != 2) {
                echo 'wrong scope';
                return;
              }
              if(!in_array($scopeParts[1], array('read', 'full'))) {
                echo 'wrong scope type';
                return;
              }
            }
            self::displayDialog($appHost, $scopes, $userAddress);
          } else {
            echo 'cannot extract app domain from redirectUri';
          }
        }
      } else {
        echo 'not 3 path parts!';
      }
    } else {
      echo 'not 2 uri parts!';
    }
  }
}
