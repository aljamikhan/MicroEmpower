<?php
require "dbconnect.php";

if (!isset($_POST['username'], $_POST['email'], $_POST['password'])) die("Invalid request");

$username = $mysqli->real_escape_string(trim($_POST['username']));
$email    = $mysqli->real_escape_string(trim($_POST['email']));
$pass     = $_POST['password'];
$contact  = $mysqli->real_escape_string($_POST['contact'] ?? "");
$address  = $mysqli->real_escape_string($_POST['address'] ?? "");
$income   = (float)($_POST['income'] ?? 0);

$hash = password_hash($pass, PASSWORD_DEFAULT);

$sql = "INSERT INTO Users (UserName, Password, Role, Email, ContactInfo)
        VALUES ('$username', '$hash', 'customer', '$email', '$contact')";
if (!$mysqli->query($sql)) die("User insert failed: ".$mysqli->error);
$userId = $mysqli->insert_id;

$sql = "INSERT INTO Customer (ID, Address, AccountBalance, CreditScore, Income)
        VALUES ($userId, '$address', 0.00, 0, $income)";
if (!$mysqli->query($sql)) die("Customer insert failed: ".$mysqli->error);

// very first RiskScore = 100 / Good
$today = date("Y-m-d");
$sql = "INSERT INTO RiskScore (ScoreValue, RiskValue, CalculatedDate, CustomerID)
        VALUES (100, 'Good', '$today', (SELECT CustomerID FROM Customer WHERE ID=$userId))";
$mysqli->query($sql);

header("Location: login.html");
exit;
