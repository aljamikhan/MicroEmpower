<?php
require "dbconnect.php";
if (!isset($_SESSION['user_id']) || $_SESSION['role']!=='customer') { header("Location: login.html"); exit; }
if (!isset($_POST['offer_id'], $_POST['application_id'])) die("Invalid request");

$offerId = (int)$_POST['offer_id'];
$appId   = (int)$_POST['application_id'];
$uid     = (int)$_SESSION['user_id'];

/* Verifying application for this customer */
$cr = $mysqli->query("SELECT C.CustomerID
                      FROM LoanRequest LR JOIN Customer C ON LR.CustomerID=C.CustomerID
                      WHERE LR.ApplicationID=$appId AND C.ID=$uid LIMIT 1");
if (!$cr || $cr->num_rows!==1) die("Not your application.");
$cid = (int)$cr->fetch_assoc()['CustomerID'];
if ($cr) $cr->free();

/* Offer must be Proposed chck */
$or = $mysqli->query("SELECT LO.OfferID, LO.Status, LO.InterestRate, LO.RepaymentTerm, LR.AmountRequested
                      FROM LoanOffer LO JOIN LoanRequest LR ON LO.ApplicationID=LR.ApplicationID
                      WHERE LO.OfferID=$offerId AND LR.ApplicationID=$appId LIMIT 1");
if (!$or || $or->num_rows!==1) die("Offer not found for this application.");
$odata = $or->fetch_assoc(); $or->free();

if ($odata['Status'] !== 'Proposed') die("This offer is not available for acceptance.");

/* disable if a contract already exists for this application */
$already = $mysqli->query("SELECT LoanID FROM LoanContract WHERE ApplicationID=$appId LIMIT 1");
if ($already && $already->num_rows>0) { if($already) $already->free(); die("This application already has an accepted offer/contract."); }
if ($already) $already->free();

/* Creatinggggggggg contract */
$amount    = (float)$odata['AmountRequested'];
$rate      = (float)$odata['InterestRate'];
$repayTerm = (int)$odata['RepaymentTerm'];
$start     = date("Y-m-d");

$q = "INSERT INTO LoanContract (PrincipalAmount, StartDate, CurrentInterestRate, Status, ApplicationID, OfferID)
      VALUES ($amount, '$start', $rate, 'Active', $appId, $offerId)";
if (!$mysqli->query($q)) die("Contract create failed: ".$mysqli->error);
$loanId = $mysqli->insert_id;

/* CREDIT loan ammout to customers AccountBalance with principal */
$mysqli->query("UPDATE Customer SET AccountBalance = AccountBalance + $amount WHERE CustomerID=$cid");

/* statuses */
$mysqli->query("UPDATE LoanOffer SET Status='Accepted' WHERE OfferID=$offerId");
$mysqli->query("UPDATE LoanRequest SET Status='Approved' WHERE ApplicationID=$appId");

/* Interest pay calculate */
$principalPart = round($amount / $repayTerm, 2);
$remaining = $amount;
for ($i=1; $i<=$repayTerm; $i++) {
  $interest = round(($remaining * ($rate/100)) / 12, 2);
  $dueDate = date("Y-m-d", strtotime("+$i month", strtotime($start)));
  $ins = "INSERT INTO RepaymentSchedule (InterestDue, PrincipalDue, PenaltyAmount, DueDate, Status, LoanID)
          VALUES ($interest, $principalPart, 0.00, '$dueDate', 'DUE', $loanId)";
  $mysqli->query($ins);
  $remaining = max(0, $remaining - $principalPart);
}

echo "Offer accepted and contract created. <a href='my_current_loan.php'>View Loan</a>";
