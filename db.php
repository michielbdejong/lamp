<?php

class Db {
  private static $link;
  private static function createDb() {
    mysql_query('CREATE DATABASE '.Config::$mysqlDb, self::$link)
        or die('could not create db "'.Config::$mysqlDb.'" '.mysql_error());
    mysql_select_db(Config::$mysqlDb)
        or die('could not select db "'.Config::$mysqlDb.'" '.mysql_error());
    self::doQuery('CREATE TABLE address ('
      .'primary_address VARCHAR(255), '
      .'secondary_address VARCHAR(255), '
      .'PRIMARY KEY(primary_address)'
      .')');
    self::doQuery('CREATE TABLE grants ('
      .'user_address VARCHAR(255), '
      .'token VARCHAR(255), '
      .'scope VARCHAR(255), '
      .'PRIMARY KEY(user_address, token)'
      .')');
    self::doQuery('CREATE TABLE items ('
      .'user_address VARCHAR(255), '
      .'category VARCHAR(255), '
      .'zone INT, '
      .'key_name VARCHAR(255), '
      .'value BLOB, '
      .'PRIMARY KEY(user_address, category, zone, key_name)'
      .')');
  }
  private static function getLink() {
    if(!self::$link) {
      self::$link = mysql_connect(Config::$mysqlHost, Config::$mysqlUsr, Config::$mysqlPwd)
        or die('could not connect '.mysql_error());
    }
    mysql_select_db(Config::$mysqlDb) or self::createDb();
  }
  private static function doQuery($query) {
    self::getLink();
    $result = mysql_query($query, self::$link) or die('Query failed: '. mysql_error());
    return $result;
  }
  public static function getString($column, $table, $whereField, $whereValue) {
    $result = self::doQuery('SELECT '.mysql_escape_string($column)
      .' FROM '.mysql_escape_string($table)
      .' WHERE '.mysql_escape_string($whereField).'="'.mysql_escape_string($whereValue).'"');
    if(mysql_num_rows($result) != 1) {
      return null;
    }
    $line = mysql_fetch_array($result);
    return $line[0];
  }
}
