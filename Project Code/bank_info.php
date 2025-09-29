<?php
require "dbconnect.php";
if (!isset($_SESSION['user_id']) || $_SESSION['role']!=='bank') { header("Location: login.html"); exit; }

$uid = (int)$_SESSION['user_id'];
$res = $mysqli->query("SELECT U.UserName, U.Email, U.ContactInfo, B.Bank, B.Branch
                       FROM Users U JOIN BankStaff B ON U.ID=B.ID WHERE U.ID=$uid LIMIT 1");
$bankLabels = [
  'UserName' => 'User Name',
  'Email' => 'Email',
  'ContactInfo' => 'Contact',
  'Bank' => 'Bank',
  'Branch' => 'Branch',
];
?>
<!DOCTYPE html>
<html>
<head>
  <meta charset="utf-8">
  <title>Bank Information</title>
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
      margin: 0 auto; padding: 24px; max-width: 600px;
      background: var(--bg); color: var(--text); font-family: system-ui, -apple-system, Segoe UI, Roboto, Arial, sans-serif;
    }
    h2 { margin: 0 0 16px 0; letter-spacing: .2px; }
    .card {
      background: var(--card);
      border: 1px solid var(--border);
      border-radius: var(--radius);
      box-shadow: var(--shadow);
      padding: 28px 18px 18px 18px;
      margin: 0 auto;
      max-width: 420px;
      text-align: left;
    }
    .card h3 { margin: 0 0 12px 0; font-size: 20px; text-align: center; }
    .kv {
      display:grid; grid-template-columns: 140px 1fr; gap: 8px 12px; align-items: center;
      margin-bottom: 10px;
    }
    .kv .label {
      color: var(--muted); font-size: 15px;
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
    .bank-logo {
      width: 54px;
      height: 54px;
      border-radius: 50%;
      box-shadow: 0 2px 12px #e3e9f7;
      display: flex;
      align-items: center;
      justify-content: center;
      margin: 0 auto 18px auto;
      background: linear-gradient(135deg, #f7f8fa 0%, #aee1f9 100%);
      border: 2px solid #e3e9f7;
    }
    @media (max-width: 600px) {
      body { padding: 8px; }
      .card { padding: 12px 4px; max-width: 98vw; }
      .bank-logo { width: 38px; height: 38px; }
      .kv .label { font-size: 13px; }
      .kv .value { font-size: 0.98rem; }
      .footer-btn { font-size: 0.98rem; padding: 9px 18px; }
      .footer-links { gap: 8px; }
    }
  </style>
</head>
<body>
  <div class="card">
    <div class="bank-logo">
      <svg width="38" height="38" viewBox="0 0 38 38" fill="none">
        <ellipse cx="19" cy="19" rx="17" ry="17" fill="#aee1f9"/>
        <rect x="10" y="16" width="18" height="6" rx="3" fill="#7ec4e8"/>
        <rect x="15" y="22" width="8" height="3" rx="1.5" fill="#bfc8e6"/>
        <circle cx="19" cy="19" r="4" fill="#e3e9f7"/>
      </svg>
    </div>
    <h3>Bank Information</h3>
    <div class="kv">
      <?php
      if ($res && $res->num_rows===1) {
        $r = $res->fetch_assoc();
        foreach ($bankLabels as $col => $pretty) {
          if (!array_key_exists($col, $r)) continue;
          $val = $r[$col];
          $safeVal = htmlspecialchars((string)$val, ENT_QUOTES, 'UTF-8');
          echo "<div class='label'>{$pretty}</div><div class='value'>{$safeVal}</div>";
        }
        $res->free();
      } else {
        echo "<div class='label'>Record not found.</div><div class='value'></div>";
      }
      ?>
    </div>
    <div class="footer-links">
      <a class="footer-btn" href="bank_dashboard.php">Back to Dashboard</a>
    </div>
  </div>
</body>
</html>
