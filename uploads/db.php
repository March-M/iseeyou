<?php
//Paramètres mysql
$db_hostname = "localhost";
$db_user = "iseeyou";
$db_password = "iseeyou1337HDPXws";
$db_name = "iseeyou";

//Connexion a mysql
global $mysql;
$mysql = new PDO('mysql:host='.$db_hostname.';dbname='.$db_name, $db_user, $db_password);
$mysql->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
$mysql->exec('SET NAMES utf8');
?>