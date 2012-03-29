<?php

class Webfinger {
  public static function showWellKnown($uri) {
    if($uri=='/.well-known/host-meta') {
      header('Access-Control-Allow-Origin: *');
      header('Content-Type: application/xrd+xml');
      echo '<?xml version="1.0" encoding="UTF-8"?>'."\n"
        .'  <XRD xmlns="http://docs.oasis-open.org/ns/xri/xrd-1.0" xmlns:hm="http://host-meta.net/xrd/1.0">'."\n"
        .'  <hm:Host xmlns="http://host-meta.net/xrd/1.0">'.$_SERVER['SERVER_NAME'].'</hm:Host>'."\n"
        .'    <Link rel="lrdd" template="http'.(isset($_SERVER['HTTPS'])?'s':'').'://'.$_SERVER['SERVER_NAME'].'/webfinger/?q={uri}">'."\n"
        .'    </Link>'."\n"
        .'  </XRD>'."\n"
        .'<xml>'."\n";
    } else {
      header('HTTP/1.0 404 Not Found');
      header('Access-Control-Allow-Origin: *');
    }
  }
  public static function showLrdd($uri) {
    $userName = '';
    if($_GET['q']) {
      $bits = explode('@', $_GET['q']);
      if(count($bits)==2 && $bits[1] == $_SERVER['SERVER_NAME']) {
        $userName = $bits[0];
      }
    }
    if(substr($userName, 0, 5) == 'acct:') {
      $userName = substr($userName, 5);
    }
    if(isset($_SERVER['HTTPS'])) {
      $baseAddress = 'https://'.$_SERVER['SERVER_NAME'];
    } else {
      $baseAddress = 'http://'.$_SERVER['SERVER_NAME'];
    }
    if($userName) {
      header('Access-Control-Allow-Origin: *');
      echo '<?xml version="1.0" encoding="UTF-8"?>'."\n"
        .'  <XRD xmlns="http://docs.oasis-open.org/ns/xri/xrd-1.0" xmlns:hm="http://host-meta.net/xrd/1.0">'."\n"
        .'    <hm:Host xmlns="http://host-meta.net/xrd/1.0">'.$_SERVER['SERVER_NAME'].'</hm:Host>'."\n"
        .'    <Link>'."\n"//this links the subject user to her storage:
        .'      <Rel>http://www.w3.org/community/unhosted/wiki/RemoteStorage-2012.04#simple</Rel>'."\n"
        .'      <URI>'.$baseAddress.'/storage/'.$userName.'</URI>'."\n"
        .'      <Link>'."\n"//this links the storage to its auth server:
        .'        <Rel>http://www.w3.org/community/unhosted/wiki/PersonalDataServices-2012.04#oauth</Rel>'."\n"
        .'        <URI>'.$baseAddress.'/oauth/'.$userName.'</URI>'."\n"
        .'      </Link>'."\n"
        .'    </Link>'."\n"
        .'    <Link>'."\n"//this links the subject user to our default socketHub instance:
        .'      <Rel>http://www.w3.org/community/unhosted/wiki/socketHub-2012.04#sockjs</Rel>'."\n"
        .'      <URI>wss:sockethub.unhosted.org</URI>'."\n"
        .'      <Link>'."\n"//this links our socketHub instance to its auth server:
        .'        <Rel>http://www.w3.org/community/unhosted/wiki/PersonalDataServices-2012.04#oauth+browserid</Rel>'."\n"
        .'        <URI>https://auth.unhosted.org/browserid</URI>'."\n"
        .'      </Link>'."\n"
        .'    </Link>'."\n"
        .'  </XRD>'."\n"
        .'</xml>';
    } else {
      header('HTTP/1.0 412 Precondition failed');
      header('Access-Control-Allow-Origin: *');
    }
  }
}
