<?php
require "dbconnect.php";
if (!isset($_SESSION['user_id']) || $_SESSION['role']!=='bank') { header("Location: login.html"); exit; }

if (!isset($_POST['application_id'], $_POST['rate'], $_POST['term'])) die("Invalid request");

$appId = (int)$_POST['application_id'];
$rate  = (float)$_POST['rate'];
$term  = (int)$_POST['term'];
$uid   = (int)$_SESSION['user_id'];

$sr = $mysqli->query("SELECT StaffID FROM BankStaff WHERE ID=$uid LIMIT 1");
if (!$sr || $sr->num_rows!==1) die("Bank staff not found");
$sid = (int)$sr->fetch_assoc()['StaffID'];
if ($sr) $sr->free();

$today = date("Y-m-d");
$sql = "INSERT INTO LoanOffer (OfferDate, InterestRate, RepaymentTerm, Status, ApplicationID, StaffID)
        VALUES ('$today', $rate, $term, 'Proposed', $appId, $sid)";
if (!$mysqli->query($sql)) die("Save failed: ".$mysqli->error);

header("Location: view_applications.php");
exit;
