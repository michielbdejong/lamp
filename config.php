<?php

class Config {
  //server setup:
  public static $mysqlHost = 'localhost',
    $mysqlUsr = 'root',
    $mysqlPwd = '',
    $mysqlDb = 'remotestorage',
    $serverHost = 'lamp.unhosted.org',
    $serverProtocol = 'http',
    $usersHost = 'lamp.unhosted.org';

  //behaviour:
  const BACKEND_MYSQL = 1,
    BACKEND_RIAK = 2,
    BACKEND_COUCHDB = 3;
  const API_SIMPLE = 1,
    API_PASSTHROUGH = 2;//for BACKEND_COUCHDB
  public static $checkUsersTable=false,//set to false if you have no special per-user info in there
    $api = self::API_SIMPLE,
    $backend = self::BACKEND_MYSQL;
}
