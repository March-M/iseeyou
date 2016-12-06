<?php
//Paramètres mysql
$db_hostname = "localhost";
$db_user = "o_rly?";
$db_password = "you_thinks_i_left_real_password_here_:thinking_face:_?";
$db_name = "iseeyou";

//Connexion a mysql
global $mysql;
$mysql = new PDO('mysql:host='.$db_hostname.';dbname='.$db_name, $db_user, $db_password);
$mysql->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
$mysql->exec('SET NAMES utf8');
?>
