<?php
require "dbconnect.php";
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'customer') { header("Location: login.html"); exit; }

$uid = (int)$_SESSION['user_id'];

/* for users */
$uRes = $mysqli->query("SELECT * FROM Users WHERE ID=$uid LIMIT 1");
if (!$uRes || $uRes->num_rows !== 1) { if ($uRes) $uRes->free(); die("User record not found. <a href='customer_dashboard.php'>Back</a>"); }
$uRow = $uRes->fetch_assoc();
$uRes->free();

/* for Customer */
$cRes = $mysqli->query("SELECT * FROM Customer WHERE ID=$uid LIMIT 1");
if (!$cRes || $cRes->num_rows !== 1) { if ($cRes) $cRes->free(); die("Customer record not found. <a href='customer_dashboard.php'>Back</a>"); }
$cRow = $cRes->fetch_assoc();
$cRes->free();

$cid         = (int)$cRow['CustomerID'];
$liveBalance = (float)$cRow['AccountBalance'];

/* Risk scooore */
$latestRiskValue = null;
$latestRiskLabel = null;
$latestRiskDate  = null;

$riskRes = $mysqli->query("SELECT ScoreValue, RiskValue, CalculatedDate
                           FROM RiskScore
                           WHERE CustomerID=$cid
                           ORDER BY CalculatedDate DESC, ScoreID DESC
                           LIMIT 1");
if ($riskRes && $riskRes->num_rows === 1) {
  $r = $riskRes->fetch_assoc();
  $latestRiskValue = (float)$r['ScoreValue'];
  $latestRiskLabel = htmlspecialchars((string)$r['RiskValue'], ENT_QUOTES, 'UTF-8');
  $latestRiskDate  = htmlspecialchars((string)$r['CalculatedDate'], ENT_QUOTES, 'UTF-8');
}
if ($riskRes) $riskRes->free();


$userLabels = [
  'ID' => 'User ID',
  'UserName' => 'User Name',
  'Role' => 'Role',
  'Email' => 'Email',
  'ContactInfo' => 'Contact',
];

$customerLabels = [
  'CustomerID' => 'Customer ID',
  'ID' => 'Linked User ID',
  'Address' => 'Address',
  'AccountBalance' => 'Account Balance',
  'Income' => 'Income',
];
?>
<!DOCTYPE html>
<html>
<head>
  <meta charset="utf-8">
  <title>Customer Profile</title>
  <style>
    :root {
      --bg: #f7f7fb;
      --card: #ffffff;
      --text: #222;
      --muted: #666;
      --primary: #2f6fed;
      --border: #e8e8ef;
      --shadow: 0 6px 18px rgba(0,0,0,0.06);
      --radius: 14px;
    }
    * { box-sizing: border-box; }
    body {
      margin: 0 auto; padding: 24px; max-width: 1000px;
      background: var(--bg); color: var(--text); font-family: system-ui, -apple-system, Segoe UI, Roboto, Arial, sans-serif;
    }
    h2 { margin: 0 0 16px 0; letter-spacing: .2px; }
    .toolbar { display:flex; gap:10px; align-items:center; margin-bottom:16px; }
    .btn {
      border: 1px solid var(--border); background: var(--card); padding: 8px 14px; border-radius: 10px; cursor: pointer;
    }
    .summary {
      display:flex; flex-wrap: wrap; gap:14px; margin: 6px 0 20px 0;
    }
    .pill {
      background: var(--card); border: 1px solid var(--border); border-radius: 999px; padding: 10px 16px;
      box-shadow: var(--shadow); font-weight: 600;
      transition: background 0.2s, color 0.2s;
    }
    .pill small { color: var(--muted); font-weight: 500; margin-right: 6px; }
    .risk-good {
      background: #e6fbe6 !important;
      color: #1a7f2a !important;
      border-color: #b2e6b2 !important;
    }
    .risk-moderate {
      background: #fffbe6 !important;
      color: #bfa600 !important;
      border-color: #f7e6b2 !important;
    }
    .risk-risky {
      background: #ffe6e6 !important;
      color: #d12c2c !important;
      border-color: #f7b2b2 !important;
    }
    .grid {
      display:grid; grid-template-columns: 1fr 1fr; gap: 18px;
    }
    @media (max-width: 800px) { .grid { grid-template-columns: 1fr; } }

    .card {
      background: var(--card);
      border: 1px solid var(--border);
      border-radius: var(--radius);
      box-shadow: var(--shadow);
      padding: 18px;
    }
    .card h3 { margin: 0 0 12px 0; font-size: 18px; }
    .kv {
      display:grid; grid-template-columns: 160px 1fr; gap: 8px 12px; align-items: center;
    }
    .kv .label {
      color: var(--muted); font-size: 14px;
    }
    .kv .value {
      font-weight: 600; background: #fafafe; border: 1px dashed var(--border);
      padding: 8px 10px; border-radius: 10px;
    }
    .footer-links {
      margin-top: 24px;
      display: flex;
      gap: 16px;
      flex-wrap: wrap;
      justify-content: flex-start;
    }
    .footer-btn {
      display: inline-block;
      padding: 11px 28px;
      font-size: 1.08rem;
      font-weight: 600;
      color: #fff;
      background: linear-gradient(90deg, #7ec4e8 0%, #c6e2d9 100%);
      border-radius: 999px;
      text-decoration: none;
      box-shadow: 0 2px 16px #c6e2d933, 0 1px 4px #7ec4e822;
      transition: background 0.2s, box-shadow 0.2s, transform 0.2s;
      border: none;
      margin-top: 2px;
      position: relative;
      overflow: hidden;
    }
    .footer-btn:hover {
      background: linear-gradient(90deg, #6971dfff 0%, #aee1f9 100%);
      transform: translateY(-2px) scale(1.04);
      box-shadow: 0 8px 32px #f9b6ae44, 0 2px 8px #aee1f922;
    }
    .header-row { display:flex; align-items:center; justify-content: space-between; gap: 10px; }
  </style>
</head>
<body>
  <div class="header-row">
    <h2>Customer Profile</h2>
    <form method="get"><button class="btn" type="submit">Refresh</button></form>
  </div>

  <!-- Risk Scooore -->
  <div class="summary">
    <div class="pill"><small>Account Balance</small> <?php echo number_format($liveBalance, 2); ?></div>
    <?php
      $riskColor = '';
      if ($latestRiskLabel === 'Good') {
        $riskColor = 'risk-good';
      } elseif ($latestRiskLabel === 'Moderate') {
        $riskColor = 'risk-moderate';
      } elseif ($latestRiskLabel === 'Risky') {
        $riskColor = 'risk-risky';
      }
    ?>
    <div class="pill <?php echo $riskColor; ?>">
      <small>Latest Risk</small>
      <?php
        if ($latestRiskValue === null) {
          echo "N/A";
        } else {
          echo $latestRiskValue . " (" . $latestRiskLabel . ")" . ($latestRiskDate ? " • " . $latestRiskDate : "");
        }
      ?>
    </div>
  </div>

  <div class="grid">
    <div class="card">
      <h3>Account (Users)</h3>
      <div class="kv">
        <?php
          foreach ($userLabels as $col => $pretty) {
            if ($col === 'Password') continue;
            if (!array_key_exists($col, $uRow)) continue;
            $val = $uRow[$col];
            $safeVal = htmlspecialchars((string)$val, ENT_QUOTES, 'UTF-8');
            echo "<div class='label'>{$pretty}</div><div class='value'>{$safeVal}</div>";
          }
        ?>
      </div>
    </div>

    <div class="card">
      <h3>Customer (Profile)</h3>
      <div class="kv">
        <?php
          foreach ($customerLabels as $col => $pretty) {
            if ($col === 'CreditScore') continue;
            if (!array_key_exists($col, $cRow)) continue;
            $val = $cRow[$col];
            if ($col === 'AccountBalance' || $col === 'Income') {
              $safeVal = number_format((float)$val, 2);
            } else {
              $safeVal = htmlspecialchars((string)$val, ENT_QUOTES, 'UTF-8');
            }
            echo "<div class='label'>{$pretty}</div><div class='value'>{$safeVal}</div>";
          }
        ?>
      </div>
    </div>
  </div>

  <div class="footer-links">
    <a class="footer-btn" href="customer_dashboard.php">Back to Dashboard</a>
    <a class="footer-btn" href="view_offers.php">View Offers</a>
    <a class="footer-btn" href="my_current_loan.php">My Current Loan</a>
    <a class="footer-btn" href="make_payment.php">Make Payment</a>
  </div>
</body>
</html>
