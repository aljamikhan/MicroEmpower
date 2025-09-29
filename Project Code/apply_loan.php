<?php
require "dbconnect.php";
if (!isset($_SESSION['user_id']) || $_SESSION['role']!=='customer') { header("Location: login.html"); exit; }
if (!isset($_POST['amount'], $_POST['term'], $_POST['purpose'])) die("Invalid request");

$uid = (int)$_SESSION['user_id'];
$res = $mysqli->query("SELECT CustomerID FROM Customer WHERE ID=$uid LIMIT 1");
if (!$res || $res->num_rows!==1) die("Customer record missing");
$cid = (int)$res->fetch_assoc()['CustomerID'];
$res->free();

$amount = (float)$_POST['amount'];
$term   = (int)$_POST['term'];
$purpose= $mysqli->real_escape_string(trim($_POST['purpose']));
$appDate= date("Y-m-d");
$status = "Submitted";

$sql = "INSERT INTO LoanRequest (ApplicationDate, AmountRequested, Term, Purpose, Status, CustomerID)
        VALUES ('$appDate', $amount, $term, '$purpose', '$status', $cid)";
if (!$mysqli->query($sql)) die("Apply failed: ".$mysqli->error);

echo "Application submitted. <a href='customer_dashboard.php'>Back</a>";
