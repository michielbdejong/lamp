<?php 
require_once('config.php');
require_once('router.php');
if(!Config::$serverHost) {
  Config::$serverHost = $_SERVER['SERVER_NAME'];
}
if(!Config::$serverProtocol) {
  Config::$serverProtocol = isset($_SERVER['HTTPS']) ? 'https' : 'http';
}
if(!Config::$usersHost) {
  Config::$usersHost = $_SERVER['SERVER_NAME'];
}
$headers = getallheaders();
if($headers && isset($headers['Authorization'])) {
  $authHeaderParts = explode(' ', $headers['Authorization']);
  $token = $authHeaderParts[1];
} else {
  $token = '';
}
Router::route($_SERVER['REQUEST_METHOD'], $_SERVER['REQUEST_URI'], file_get_contents('php://input'), $_GET, $token);
