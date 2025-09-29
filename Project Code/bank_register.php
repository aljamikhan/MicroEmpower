<?php
require "dbconnect.php";

if (!isset($_POST['username'], $_POST['email'], $_POST['password'])) die("Invalid request");

$username = $mysqli->real_escape_string(trim($_POST['username']));
$email    = $mysqli->real_escape_string(trim($_POST['email']));
$pass     = $_POST['password'];
$contact  = $mysqli->real_escape_string($_POST['contact'] ?? "");
$bank     = $mysqli->real_escape_string($_POST['bank'] ?? "");
$branch   = $mysqli->real_escape_string($_POST['branch'] ?? "");

$hash = password_hash($pass, PASSWORD_DEFAULT);

$sql = "INSERT INTO Users (UserName, Password, Role, Email, ContactInfo)
        VALUES ('$username', '$hash', 'bank', '$email', '$contact')";
if (!$mysqli->query($sql)) die("User insert failed: ".$mysqli->error);
$userId = $mysqli->insert_id;

$sql = "INSERT INTO BankStaff (ID, Branch, Bank) VALUES ($userId, '$branch', '$bank')";
if (!$mysqli->query($sql)) die("BankStaff insert failed: ".$mysqli->error);

header("Location: login.html");
exit;
