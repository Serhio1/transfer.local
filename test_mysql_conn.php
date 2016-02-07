<?php

/**
 *  Checks mysql connection to remote server.
 */ 
$dbname = filter_input(INPUT_POST, 'remote_db', FILTER_SANITIZE_SPECIAL_CHARS);
$dbuser = filter_input(INPUT_POST, 'remote_db_login', FILTER_SANITIZE_SPECIAL_CHARS);
$dbpass = filter_input(INPUT_POST, 'remote_db_password', FILTER_SANITIZE_SPECIAL_CHARS);
$dbhost = filter_input(INPUT_POST, 'remote_db_host', FILTER_SANITIZE_SPECIAL_CHARS);

/*$connect = mysql_connect($dbhost, $dbuser, $dbpass) or die("Connection to $dbhost failed.");
if ($connect) {
    echo 'Mysql connection: OK';
}
mysql_select_db($dbname) or die("Connection is fine, but $dbname databse not exist");
*/

$dsn = "mysql:host=$dbhost;dbname=$dbname;charset=utf8";
$opt = array(
    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
);
try {
    $pdo = new PDO($dsn, $dbuser, $dbpass, $opt);
    echo 'OK';
    $pdo = null;
} catch (PDOException $e) {
    die('Подключение не удалось. Проверьте правильность введенных данных. Если есть блокировка подключения по IP, добавьте этот IP в исключения: ' . $_SERVER['REMOTE_ADDR'] . '. Если выполнить соединение нет возможности - сделайте экспорт базы данных вручную и загрузите полученный sql файл в папку с сайтом. Файл назовите '.$dbname.'.sql.<br>');
}
