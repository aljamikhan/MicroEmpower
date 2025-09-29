<?php
require "dbconnect.php";
if (!isset($_SESSION['user_id'])) { header("Location: login.html"); exit; }

if (!isset($_POST['offer_id'], $_POST['message'])) die("Invalid request");
$offerId = (int)$_POST['offer_id'];
$uid = (int)$_SESSION['user_id'];
$role = $_SESSION['role'];
$message = trim($_POST['message']);
if ($message === '') { header("Location: chat.php?offer_id=".$offerId); exit; }
if (strlen($message) > 2000) $message = substr($message, 0, 2000); // simple cap
$escMsg = $mysqli->real_escape_string($message);

// offer chek
$infoSql = "SELECT LO.OfferID, LO.Status AS OfferStatus, LO.ApplicationID, LO.StaffID, LR.CustomerID
            FROM LoanOffer LO
            JOIN LoanRequest LR ON LO.ApplicationID = LR.ApplicationID
            WHERE LO.OfferID = $offerId
            LIMIT 1";
$infoRes = $mysqli->query($infoSql);
if (!$infoRes || $infoRes->num_rows !== 1) { if ($infoRes) $infoRes->free(); die("Offer not found"); }
$offer = $infoRes->fetch_assoc();
$infoRes->free();

// no sending if already accepted
if ($offer['OfferStatus'] === 'Accepted') {
  header("Location: chat.php?offer_id=".$offerId);
  exit;
}

// ability check
$canSend = false;
if ($role === 'customer') {
  $cRes = $mysqli->query("SELECT CustomerID FROM Customer WHERE ID=$uid LIMIT 1");
  $myCid = ($cRes && $cRes->num_rows===1) ? (int)$cRes->fetch_assoc()['CustomerID'] : 0;
  if ($cRes) $cRes->free();
  $canSend = ($myCid > 0 && $myCid === (int)$offer['CustomerID']);
} elseif ($role === 'bank') {
  $sRes = $mysqli->query("SELECT StaffID FROM BankStaff WHERE ID=$uid LIMIT 1");
  $mySid = ($sRes && $sRes->num_rows===1) ? (int)$sRes->fetch_assoc()['StaffID'] : 0;
  if ($sRes) $sRes->free();
  $canSend = ($mySid > 0 && $mySid === (int)$offer['StaffID']);
}

if (!$canSend) die("Not allowed.");

// type message to send
$now = date("Y-m-d H:i:s");
$ins = "INSERT INTO ChatMessage (OfferID, SenderUserID, Message, SentAt)
        VALUES ($offerId, $uid, '$escMsg', '$now')";
if (!$mysqli->query($ins)) die("Failed to send: ".$mysqli->error);

header("Location: chat.php?offer_id=".$offerId);
exit;
