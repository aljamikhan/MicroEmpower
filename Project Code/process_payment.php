<?php
require "dbconnect.php";
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'customer') { header("Location: login.html"); exit; }

if (!isset($_POST['schedule_id'], $_POST['amount'], $_POST['method'])) die("Invalid request");

$sid    = (int)$_POST['schedule_id'];
$amount = (float)$_POST['amount'];
$method = $mysqli->real_escape_string(trim($_POST['method']));
$now    = date("Y-m-d H:i:s");
$uid    = (int)$_SESSION['user_id'];

/* Check info to this id */
$q = $mysqli->query("
  SELECT 
    LR.CustomerID, C.ID AS UserID,
    RS.Status,
    RS.InterestDue, RS.PrincipalDue, RS.PenaltyAmount,
    (SELECT IFNULL(SUM(Amount),0) FROM Penalty WHERE ScheduleID=RS.ScheduleID) AS SumPenalty,
    RS.DueDate
  FROM RepaymentSchedule RS
  JOIN LoanContract LC ON RS.LoanID=LC.LoanID
  JOIN LoanRequest  LR ON LC.ApplicationID=LR.ApplicationID
  JOIN Customer     C  ON LR.CustomerID=C.CustomerID
  JOIN Users        U  ON C.ID = U.ID
  WHERE RS.ScheduleID=$sid AND U.ID=$uid
  LIMIT 1
");
if (!$q || $q->num_rows!==1) { if($q) $q->free(); die("Schedule not found for your account."); }
$R = $q->fetch_assoc();
$q->free();

if ($R['Status'] !== 'DUE') die("This installment is not payable (status: ".$R['Status'].").");

$cid = (int)$R['CustomerID'];
$interest = (float)$R['InterestDue'];
$principal= (float)$R['PrincipalDue'];
$basePen  = (float)$R['PenaltyAmount'];
$sumPen   = (float)$R['SumPenalty'];
$totalDue = round($interest + $principal + $basePen + $sumPen, 2);

/* Checking if payment is overdue */
$dueDate = $R['DueDate'];
$isOverdue = (strtotime($now) > strtotime($dueDate));  // true false

/* If overdue*/
if ($isOverdue) {
  $curRisk = 100.0;
  $rs = $mysqli->query("SELECT ScoreValue FROM RiskScore WHERE CustomerID=$cid ORDER BY CalculatedDate DESC, ScoreID DESC LIMIT 1");
  if ($rs && $rs->num_rows === 1) {
    $curRisk = (float)$rs->fetch_assoc()['ScoreValue'];
  }
  if ($rs) $rs->free();

  // minus 5
  $newRisk = max(0, $curRisk - 5);
  $label = ($newRisk >= 90 ? 'Good' : ($newRisk >= 70 ? 'Moderate' : 'Risky'));


  $today = date("Y-m-d");
  $mysqli->query("INSERT INTO RiskScore (ScoreValue, RiskValue, CalculatedDate, CustomerID) VALUES ($newRisk, '$label', '$today', $cid)");


  $mysqli->query("UPDATE Customer SET CreditScore=$newRisk WHERE CustomerID=$cid");
}

/* 4) Recording payment */
$ins = "INSERT INTO Payment (AmountPaid, PaymentDate, PaymentMethod, ScheduleID)
        VALUES ($amount, '$now', '$method', $sid)";
if (!$mysqli->query($ins)) die("Payment failed: ".$mysqli->error);

$mysqli->query("UPDATE Customer SET AccountBalance = AccountBalance - $amount WHERE CustomerID=$cid");

/* 6) If fully paid +2 */
if ($amount + 0.0001 >= $totalDue) {
  $mysqli->query("UPDATE RepaymentSchedule SET Status='PAID' WHERE ScheduleID=$sid");

  $cur = 100.0;
  $rs = $mysqli->query("SELECT ScoreValue FROM RiskScore WHERE CustomerID=$cid ORDER BY CalculatedDate DESC, ScoreID DESC LIMIT 1");
  if ($rs && $rs->num_rows === 1) $cur = (float)$rs->fetch_assoc()['ScoreValue'];
  if ($rs) $rs->free();

  if ($cur <= 98.0) {
    $new = $cur + 2.0;
    if ($new > 100.0) $new = 100.0;
    $label = ($new >= 90 ? 'Good' : ($new >= 70 ? 'Moderate' : 'Risky'));
    $today = date("Y-m-d");
    $mysqli->query("INSERT INTO RiskScore (ScoreValue, RiskValue, CalculatedDate, CustomerID) VALUES ($new, '$label', '$today', $cid)");


    $mysqli->query("UPDATE Customer SET CreditScore=$new WHERE CustomerID=$cid");
  }
}
echo "Payment recorded. <a href='make_payment.php'>Back</a>";
