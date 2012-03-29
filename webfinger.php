<?php

class Webfinger {
  public static function showWellKnown($uri) {
    if($uri=='/.well-known/host-meta') {
      header('Access-Control-Allow-Origin: *');
      header('Content-Type: application/xrd+xml');
      echo '<?xml version="1.0" encoding="UTF-8"?>'
        .'  <XRD xmlns="http://docs.oasis-open.org/ns/xri/xrd-1.0" xmlns:hm="http://host-meta.net/xrd/1.0">'
        .'  <hm:Host xmlns="http://host-meta.net/xrd/1.0">'.$_SERVER['SERVER_NAME'].'</hm:Host>'
        .'    <Link rel="lrdd" template="http'.(isset($_SERVER['HTTPS'])?'s':'').'://'.$_SERVER['SERVER_NAME'].'/.well-known/webfinger.php?q={uri}">'
        .'    </Link>'
        .'  </XRD>'
        .'<xml>';
    } else {
      header('HTTP/1.0 404 Not Found');
      header('Access-Control-Allow-Origin: *');
    }
  }
  public static function showLrdd($uri) {
  }
}
