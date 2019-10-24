<?php

function new_database_connection()
{
       global $db_host, $db_name, $db_user, $db_pass;
       require_once "../db_config.php";
       include "../db_credentials.php";
       $pdo = new PDO("mysql:host=$db_host;dbname=$db_name", $db_user, $db_pass);
       return $pdo;
}

function getClientIP()
{
       if (array_key_exists('HTTP_X_FORWARDED_FOR', $_SERVER)) {
              return  $_SERVER["HTTP_X_FORWARDED_FOR"];
       } else if (array_key_exists('REMOTE_ADDR', $_SERVER)) {
              return $_SERVER["REMOTE_ADDR"];
       } else if (array_key_exists('HTTP_CLIENT_IP', $_SERVER)) {
              return $_SERVER["HTTP_CLIENT_IP"];
       }

       return '';
}