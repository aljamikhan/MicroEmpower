<?php
require "dbconnect.php";
if (!isset($_SESSION['user_id']) || $_SESSION['role']!=='bank') { header("Location: login.html"); exit; }
if (!isset($_GET['customer_id'])) die("Missing customer_id");
$cid = (int)$_GET['customer_id'];



$chk = $mysqli->query("SELECT ScoreID FROM RiskScore WHERE CustomerID=$cid ORDER BY CalculatedDate DESC LIMIT 1");
if (!$chk || $chk->num_rows===0) {
  $today = date("Y-m-d");
  $mysqli->query("INSERT INTO RiskScore (ScoreValue,RiskValue,CalculatedDate,CustomerID) VALUES (100,'Good','$today',$cid)");
}
if ($chk) $chk->free();

$q = $mysqli->query("SELECT ScoreValue,RiskValue,CalculatedDate FROM RiskScore WHERE CustomerID=$cid ORDER BY CalculatedDate DESC LIMIT 1");
$r = $q ? $q->fetch_assoc() : null;
if ($q) $q->free();


echo '<!DOCTYPE html><html><head><meta charset="utf-8"><title>Risk Score</title><style>
  body {
    background: linear-gradient(120deg, #f8fafc 0%, #e0e7ff 100%);
    font-family: "Segoe UI", Arial, sans-serif;
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    min-height: 100vh;
    margin: 0;
  }
  .card {
    background: #fff;
    padding: 2.5rem 2rem;
    border-radius: 1.2rem;
    box-shadow: 0 4px 24px rgba(60, 72, 88, 0.12);
    max-width: 350px;
    width: 100%;
    margin: 2rem auto;
    text-align: center;
  }
  h3 {
    font-weight: 600;
    margin-bottom: 2rem;
    color: #3b4a6b;
    letter-spacing: 1px;
  }
  .score {
    font-size: 2.2rem;
    font-weight: 700;
    color: #6366f1;
    margin-bottom: 0.7rem;
  }
  .label {
    font-size: 1.1rem;
    font-weight: 500;
    color: #2563eb;
    margin-bottom: 0.7rem;
  }
  .date {
    font-size: 0.98rem;
    color: #6b7280;
    margin-bottom: 1.2rem;
  }
  .back-link {
    display: block;
    text-align: center;
    margin-top: 1.5rem;
    color: #6366f1;
    text-decoration: none;
    font-size: 0.98rem;
    transition: color 0.2s;
  }
  .back-link:hover {
    color: #4338ca;
    text-decoration: underline;
  }
</style></head><body>';
echo '<div class="card">';
echo '<h3>Risk Score</h3>';
if ($r){
  echo '<div class="score">' . htmlspecialchars($r['ScoreValue']) . '</div>';
  echo '<div class="label">' . htmlspecialchars($r['RiskValue']) . '</div>';
  echo '<div class="date">' . htmlspecialchars($r['CalculatedDate']) . '</div>';
} else {
  echo '<div class="label">No score.</div>';
}
echo '<a class="back-link" href="view_applications.php">Back</a>';
echo '</div></body></html>';
