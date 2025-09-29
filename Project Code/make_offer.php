<?php
require "dbconnect.php";
if (!isset($_SESSION['user_id']) || $_SESSION['role']!=='bank') { header("Location: login.html"); exit; }
if (!isset($_GET['application_id'])) die("Missing application_id");

$appId = (int)$_GET['application_id'];
?>
<!DOCTYPE html>
<html>
<head>
  <meta charset="utf-8">
  <title>Make Offer</title>
  <style>
    body{font-family:system-ui,-apple-system,Segoe UI,Roboto,Arial,sans-serif;margin:20px}
    .card{max-width:520px;border:1px solid #e9e9ef;border-radius:12px;padding:16px}
    .row{margin-bottom:10px}
    label{display:block;margin-bottom:6px;color:#555}
    input{width:100%;padding:8px;border:1px solid #ddd;border-radius:8px}
    .btn{padding:8px 14px;border-radius:10px;border:1px solid #e0e0ea;background:#fff;cursor:pointer}
    .btn.primary{background:#2f6fed;color:#fff;border-color:transparent}
  </style>
</head>
<body>
  <h3>Make Offer for Application #<?php echo $appId; ?></h3>
  <div class="card">
    <form method="post" action="save_offer.php">
      <input type="hidden" name="application_id" value="<?php echo $appId; ?>">
      <div class="row">
        <label>Annual Interest Rate (%)</label>
        <input type="number" name="rate" step="0.01" min="0" required>
      </div>
      <div class="row">
        <label>Repayment Term (months)</label>
        <input type="number" name="term" min="1" required>
      </div>
      <button class="btn primary" type="submit">Submit Offer</button>
      <a class="btn" href="view_applications.php">Cancel</a>
    </form>
  </div>
</body>
</html>
