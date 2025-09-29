<?php
require "dbconnect.php";
if (!isset($_SESSION['user_id']) || $_SESSION['role']!=='bank') { header("Location: login.html"); exit; }
if (!isset($_GET['application_id'])) die("Missing application_id");

$appId = (int)$_GET['application_id'];
$uid = (int)$_SESSION['user_id'];

$sRes = $mysqli->query("SELECT StaffID FROM BankStaff WHERE ID=$uid LIMIT 1");
$mySid = ($sRes && $sRes->num_rows===1) ? (int)$sRes->fetch_assoc()['StaffID'] : 0;
if ($sRes) $sRes->free();

$sql = "SELECT LO.OfferID, LO.OfferDate, LO.InterestRate, LO.RepaymentTerm, LO.Status,
               BS.Bank, BS.Branch, LO.StaffID
        FROM LoanOffer LO
        LEFT JOIN BankStaff BS ON LO.StaffID = BS.StaffID
        WHERE LO.ApplicationID = $appId
        ORDER BY LO.OfferID DESC";
$res = $mysqli->query($sql);
?>
<!DOCTYPE html>
<html>
<head>
  <meta charset="utf-8">
  <title>Offers for Application #<?php echo $appId; ?></title>
  <style>
    body{font-family:system-ui,-apple-system,Segoe UI,Roboto,Arial,sans-serif; margin:20px; background:#f7f7fb}
    .card{background:#fff; border:1px solid #e9e9ef; border-radius:12px; padding:14px; margin-bottom:12px; box-shadow:0 6px 16px rgba(0,0,0,.06)}
    .row{display:grid; grid-template-columns:repeat(4,1fr); gap:10px}
    @media (max-width:900px){ .row{grid-template-columns:1fr 1fr} }
    @media (max-width:560px){ .row{grid-template-columns:1fr} }
    .kv{background:#fafafe; border:1px dashed #e9e9ef; border-radius:10px; padding:10px}
    .kv .l{font-size:12px; color:#666}
    .kv .v{font-weight:600}
    .actions{margin-top:10px; display:flex; gap:8px; flex-wrap:wrap}
    .btn{border:1px solid #e0e0ea; background:#fff; padding:6px 12px; border-radius:8px; cursor:pointer; text-decoration:none; display:inline-block; color:inherit}
    .btn[disabled]{opacity:.45; cursor:not-allowed}
  </style>
</head>
<body>
  <h3>Offers for Application #<?php echo $appId; ?></h3>
  <p><a class="btn" href="view_applications.php">Back</a></p>

  <?php if (!$res || $res->num_rows===0) { echo "<div class='card'>No offers yet.</div>"; if($res) $res->free(); exit; } ?>

  <?php while ($o = $res->fetch_assoc()) {
    $bank = htmlspecialchars($o['Bank'] ?: 'Bank', ENT_QUOTES, 'UTF-8');
    if (!empty($o['Branch'])) $bank .= " (".htmlspecialchars($o['Branch'], ENT_QUOTES, 'UTF-8').")";
    $accepted = ($o['Status']==='Accepted');
    $mine = ((int)$o['StaffID'] === $mySid);
  ?>
    <div class="card">
      <div class="row">
        <div class="kv"><div class="l">Offer</div><div class="v">#<?php echo (int)$o['OfferID']; ?> • <?php echo htmlspecialchars($o['OfferDate'], ENT_QUOTES, 'UTF-8'); ?></div></div>
        <div class="kv"><div class="l">Bank</div><div class="v"><?php echo $bank; ?></div></div>
        <div class="kv"><div class="l">Rate (p.a.)</div><div class="v"><?php echo number_format((float)$o['InterestRate'],2); ?>%</div></div>
        <div class="kv"><div class="l">Repayment</div><div class="v"><?php echo (int)$o['RepaymentTerm']; ?> months</div></div>
      </div>
      <div class="row" style="margin-top:8px">
        <div class="kv"><div class="l">Status</div><div class="v"><?php echo htmlspecialchars($o['Status'], ENT_QUOTES, 'UTF-8'); ?></div></div>
      </div>
      <div class="actions">
        <a class="btn" href="chat.php?offer_id=<?php echo (int)$o['OfferID']; ?>" <?php echo ($accepted ? "disabled title='Chat locked after acceptance'" : ($mine ? "" : "disabled title='Only the bank that made this offer can chat'")); ?>>Chat</a>
      </div>
    </div>
  <?php } if ($res) $res->free(); ?>
</body>
</html>
