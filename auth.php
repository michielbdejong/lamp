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
  static function displayDialog($appHost, $scopes, $userAddress) {
    echo '<html><script src="https://browserid.org/include.js"></script>'."\n"
      .'<body><input type="submit" onclick="navigator.id.get(function(assertion) {'."\n"
      .'  var xhr = new XMLHttpRequest();'."\n"
      .'  xhr.open(\'POST\', \'\', true);'."\n"
      .'  xhr.onreadystatechange = function() {'."\n"
      .'    if(xhr.readyState == 4) {'."\n"
      .'      if(xhr.status == 200) {'."\n"
      .'        //location=xhr.responseText;'."\n"
      .'      }'."\n"
      .'    }'."\n"
      .'  };'."\n"
      .'  xhr.send(assertion);'."\n"
      .'}, {requiredEmail: \''.self::getSecondaryAddress($userAddress).'\'});"></body></html>'."\n";
  }
  static function processDecision($appHost, $scopesStr, $userAddress, $assertion, $redirectUri) {
    $token = self::checkAccess($assertion, Config::$serverProtocol+'://'+Config::$serverHost, $userAddress);
    if($token) {
      Db::insert('grants', $token, $appHost, $scopesStr);
      echo $redirectUri.'#access_token='.$token;
    }
  }
  public static function showOAuth($method, $uri, $post, $get) {
    $uriParts = explode('?', $uri);
    if(count($uriParts)==2) {
      $pathParts = explode('/', $uriParts[0]);
      if(count($pathParts) == 3 && $pathParts[0] == '' && $pathParts[1] == 'oauth') {
        $userAddress = $pathParts[2].'@'.Config::$usersHost;
        if((!isset($get['redirect_uri'])) || (!isset($get['scope']))) {
          echo 'not the right params!';
        } else {
          $redirectUriParts = explode('/', $get['redirect_uri']);
          if(count($redirectUriParts) >= 3) {
            $appHostParts = explode(':', $redirectUriParts[2]);
            $appHost = $appHostParts[0];
            $scopes = explode(',', $get['scope']);
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
            if($method == 'GET') {
              self::displayDialog($appHost, $scopes, $userAddress);
            } else {
              self::processDecision($appHost, $get['scopes'], $userAddress, $post, $get['redirect_uri']);
            }
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
