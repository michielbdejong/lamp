<?php
require_once 'db.php';
require_once 'browserid.php';

class Auth {
  static function getSecondaryAddress($primaryAddress) {
    //SELECT secondary FROM address WHERE primary = $primaryAddress:
    $strings = Db::getStrings(array('secondary_address'), 'address', array('primary_address' => $primaryAddress));
    return $strings ? $strings[0] : $primaryAddress;
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
      .'        location=xhr.responseText;'."\n"
      .'      }'."\n"
      .'    }'."\n"
      .'  };'."\n"
      .'  xhr.send(assertion);'."\n"
      .'}, {requiredEmail: \''.self::getSecondaryAddress($userAddress).'\'});"></body></html>'."\n";
  }
  static function genToken() {
    list($usec, $sec) = explode(' ', microtime());
    mt_srand((float) $sec + ((float) $usec * 100000));
    return base64_encode(mt_rand());
  }
  static function mergeScopes($scopeStr1, $scopeStr2) {
    $scopes = array();
    foreach(explode(',', $scopeStr1) as $elt) {
      $parts = explode(':', $elt);
      if(count($parts)==2 && (!isset($scopes[$parts[0].':']) || $scopes[$parts[0].':'] == 'read')) {
        $scopes[$parts[0].':'] = $parts[1];
      }
    }
    foreach(explode(',', $scopeStr2) as $elt) {
      $parts = explode(':', $elt);
      if(count($parts)==2 && (!isset($scopes[$parts[0].':']) || $scopes[$parts[0].':'] == 'read')) {
        $scopes[$parts[0].':'] = $parts[1];
      }
    }
    $scopesStrs=array();
    foreach($scopes as $k => $v) {
      //always keep the global scope; if there is no global one, keep everything; otherwise keep fulls on top of a global read
      if($k == ':' || !isset($scopes[':']) || ($scopes[':']=='read' && $v=='full')) {
        array_push($scopesStrs, $k.$v);
      }
    }
    return implode(',', $scopesStrs);
  }
  static function processDecision($appHost, $scopesStr, $userAddress, $assertion, $redirectUri) {
    if(self::checkAccess($assertion, Config::$serverProtocol.'://'.Config::$serverHost, $userAddress)) {
      $strings = Db::getStrings(array('token', 'scope'), 'grants', array(
        'client_id' => $appHost,
        'user_address' => $userAddress
      ));
      if($strings) {
        list($token, $existingScope) = $strings;
        $newScope = self::mergeScopes($existingScope, $scopeStr);
        if($existingScope != $newScope) {
          var_dump($existingScope);
          var_dump($newScope);
          Db::update('grants', 'scope', $newScope, array('user_address' => $userAddress, 'client_id' => $appHost));
        }
      } else {
        $token = self::genToken();
        Db::insert('grants', array($userAddress, $token, $appHost, $scopesStr));
      }
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
              self::processDecision($appHost, $get['scope'], $userAddress, $post, $get['redirect_uri']);
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
