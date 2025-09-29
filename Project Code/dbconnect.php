<?php
$DB_HOST = "localhost";
$DB_USER = "root";
$DB_PASS = "";
$DB_NAME = "exchatloan";

$mysqli = new mysqli($DB_HOST, $DB_USER, $DB_PASS, $DB_NAME);
if ($mysqli->connect_errno) { die("DB connect failed: " . $mysqli->connect_error); }
$mysqli->set_charset("utf8mb4");

if (session_status() === PHP_SESSION_NONE) session_start();
?>
