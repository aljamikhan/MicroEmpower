<?php
require "dbconnect.php";
if (!isset($_SESSION['user_id']) || $_SESSION['role']!=='customer') { header("Location: login.html"); exit; }
if (!isset($_POST['offer_id'])) die("Invalid request");

$offerId = (int)$_POST['offer_id'];
$uid     = (int)$_SESSION['user_id'];

$q = $mysqli->query("SELECT LR.ApplicationID
                     FROM LoanOffer LO JOIN LoanRequest LR ON LO.ApplicationID=LR.ApplicationID
                     JOIN Customer C ON LR.CustomerID=C.CustomerID
                     WHERE LO.OfferID=$offerId AND C.ID=$uid LIMIT 1");
if (!$q || $q->num_rows!==1) die("Offer not found for your account.");
$appId = (int)$q->fetch_assoc()['ApplicationID'];
if ($q) $q->free();

/* If already accepted cant be rejected */
$ck = $mysqli->query("SELECT LoanID FROM LoanContract WHERE OfferID=$offerId LIMIT 1");
if ($ck && $ck->num_rows>0) { if($ck) $ck->free(); die("This offer is already bound to a contract."); }
if ($ck) $ck->free();

$mysqli->query("UPDATE LoanOffer SET Status='Rejected' WHERE OfferID=$offerId");
echo "Offer rejected. <a href='view_offers.php'>Back</a>";
