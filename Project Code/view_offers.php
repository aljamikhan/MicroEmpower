<?php
require "dbconnect.php";
if (!isset($_SESSION['user_id']) || $_SESSION['role']!=='customer') { header("Location: login.html"); exit; }

$uid = (int)$_SESSION['user_id'];

$res = $mysqli->query("SELECT CustomerID FROM Customer WHERE ID=$uid LIMIT 1");
if (!$res || $res->num_rows!==1) die("Customer not found");
$cid = (int)$res->fetch_assoc()['CustomerID'];
if ($res) $res->free();

$sql = "
  SELECT 
    LR.ApplicationID,
    LR.ApplicationDate,
    LR.AmountRequested,
    LR.Term AS AppTerm,
    LR.Purpose,
    LR.Status AS AppStatus,
    LO.OfferID,
    LO.OfferDate,
    LO.InterestRate,
    LO.RepaymentTerm,
    LO.Status AS OfferStatus,
    BS.Bank   AS BankName,
    BS.Branch AS BranchName
  FROM LoanRequest LR
  LEFT JOIN LoanOffer LO ON LO.ApplicationID = LR.ApplicationID
  LEFT JOIN BankStaff BS ON LO.StaffID = BS.StaffID
  WHERE LR.CustomerID = $cid
  ORDER BY LR.ApplicationID DESC, LO.OfferID DESC";
$list = $mysqli->query($sql);

$apps = [];
if ($list && $list->num_rows > 0) {
  while ($row = $list->fetch_assoc()) {
    $appId = (int)$row['ApplicationID'];
    if (!isset($apps[$appId])) {
      $apps[$appId] = [
        'ApplicationID'   => $appId,
        'ApplicationDate' => htmlspecialchars((string)$row['ApplicationDate'], ENT_QUOTES, 'UTF-8'),
        'AmountRequested' => (float)$row['AmountRequested'],
        'AppTerm'         => (int)$row['AppTerm'],
        'Purpose'         => htmlspecialchars((string)$row['Purpose'], ENT_QUOTES, 'UTF-8'),
        'AppStatus'       => htmlspecialchars((string)$row['AppStatus'], ENT_QUOTES, 'UTF-8'),
        'offers'          => [],
        'hasAccepted'     => false
      ];
    }
    if (!empty($row['OfferID'])) {
      $offerStatus = htmlspecialchars((string)$row['OfferStatus'], ENT_QUOTES, 'UTF-8');
      if (strcasecmp($offerStatus, 'Accepted') === 0) $apps[$appId]['hasAccepted'] = true;

      $apps[$appId]['offers'][] = [
        'OfferID'       => (int)$row['OfferID'],
        'OfferDate'     => htmlspecialchars((string)$row['OfferDate'], ENT_QUOTES, 'UTF-8'),
        'InterestRate'  => (float)$row['InterestRate'],
        'RepaymentTerm' => (int)$row['RepaymentTerm'],
        'OfferStatus'   => $offerStatus,
        'BankName'      => htmlspecialchars((string)$row['BankName'], ENT_QUOTES, 'UTF-8'),
        'BranchName'    => htmlspecialchars((string)$row['BranchName'], ENT_QUOTES, 'UTF-8')
      ];
    }
  }
}
if ($list) $list->free();
?>
<!DOCTYPE html>
<html>
<head>
  <meta charset="utf-8">
  <title>View Offers</title>
  <style>
    :root{
      --bg:#f7f7fb; --card:#fff; --text:#222; --muted:#666; --primary:#2f6fed;
      --border:#e9e9ef; --shadow:0 10px 26px rgba(0,0,0,.07); --radius:14px;
      --chip:#eef2ff; --chip-text:#2f46c0;
      --good:#e6f7ef; --good-text:#167c3a;
      --warn:#fff7e6; --warn-text:#a36a00;
      --bad:#ffecec; --bad-text:#b20d30;
    }
    *{box-sizing:border-box}
    body{margin:0 auto; padding:24px; max-width:1100px; background:var(--bg); color:var(--text); font-family:system-ui,-apple-system,Segoe UI,Roboto,Arial,sans-serif}
    .header-row{display:flex; align-items:center; justify-content:space-between; gap:10px; margin-bottom:16px}
    h2{margin:0}
    .btn{border:1px solid var(--border); background:var(--card); padding:8px 14px; border-radius:10px; cursor:pointer; text-decoration:none; color:inherit}
    .btn.primary{background:var(--primary); color:#fff; border-color:transparent}
    .btn.min{padding:6px 10px; font-size:14px}
    .btn[disabled]{opacity:.45; cursor:not-allowed}
    .card{background:var(--card); border:1px solid var(--border); border-radius:var(--radius); box-shadow:var(--shadow); padding:18px; margin-bottom:18px;}
    .app-head{display:flex; align-items:center; justify-content:space-between; gap:12px; margin-bottom:10px}
    .chips{display:flex; flex-wrap:wrap; gap:8px}
    .chip{background:var(--chip); color:var(--chip-text); padding:6px 10px; border-radius:999px; font-size:12px; border:1px solid var(--border)}
    .chip.green{background:var(--good); color:var(--good-text); border-color:transparent}
    .row{display:grid; grid-template-columns:repeat(4,1fr); gap:10px; margin-bottom:14px}
    @media (max-width:900px){ .row{grid-template-columns:1fr 1fr} }
    @media (max-width:560px){ .row{grid-template-columns:1fr} }
    .kv{display:flex; flex-direction:column; gap:4px; background:#fafafe; border:1px dashed var(--border); padding:10px; border-radius:10px}
    .kv .label{font-size:12px; color:var(--muted)}
    .kv .value{font-weight:600}
    .offers-grid{display:grid; grid-template-columns:repeat(2,1fr); gap:12px}
    @media (max-width:900px){ .offers-grid{grid-template-columns:1fr} }
    .offer{border:1px solid var(--border); border-radius:12px; padding:14px; background:#fff; display:flex; flex-direction:column; gap:10px;}
    .offer-head{display:flex; justify-content:space-between; gap:8px}
    .bank-tag{font-weight:700}
    .bank-tag small{color:var(--muted); font-weight:600}
    .status{padding:4px 8px; border-radius:999px; font-size:12px; border:1px solid var(--border); align-self:flex-start}
    .status.good{background:var(--good); color:var(--good-text); border-color:transparent}
    .status.warn{background:var(--warn); color:var(--warn-text); border-color:transparent}
    .status.bad{ background:var(--bad);  color:var(--bad-text);  border-color:transparent}
    .offer-meta{display:grid; grid-template-columns:repeat(3,1fr); gap:8px}
    @media (max-width:560px){ .offer-meta{grid-template-columns:1fr 1fr} }
    .actions{display:flex; gap:8px; flex-wrap:wrap}
    .empty{padding:10px 12px; background:#fafafe; border:1px dashed var(--border); border-radius:10px; color:var(--muted)}
    .footer-links{margin-top:10px}
    .footer-links a{margin-right:10px; color:var(--primary); text-decoration:none}
  </style>
</head>
<body>
  <div class="header-row">
    <h2>Offers for Your Applications</h2>
    <div>
      <form method="get" style="display:inline-block"><button class="btn" type="submit">Refresh</button></form>
      <a class="btn" href="customer_dashboard.php">Back</a>
    </div>
  </div>

  <?php if (empty($apps)) { ?>
    <div class="card empty">No applications or offers yet.</div>
  <?php } else { ?>

    <?php foreach ($apps as $app): 
      $appAccepted = $app['hasAccepted']; ?>
      <div class="card">
        <div class="app-head">
          <div class="chips">
            <div class="chip">App #<?php echo $app['ApplicationID']; ?></div>
            <div class="chip">Submitted: <?php echo $app['ApplicationDate']; ?></div>
            <div class="chip">Status: <?php echo $app['AppStatus']; ?></div>
            <?php if ($appAccepted) { ?>
              <div class="chip green" title="One offer already accepted for this application. Other offers are locked.">Accepted offer exists</div>
            <?php } ?>
          </div>
        </div>

        <div class="row">
          <div class="kv">
            <div class="label">Amount Requested</div>
            <div class="value"><?php echo number_format($app['AmountRequested'],2); ?></div>
          </div>
          <div class="kv">
            <div class="label">Term (months)</div>
            <div class="value"><?php echo $app['AppTerm']; ?></div>
          </div>
          <div class="kv">
            <div class="label">Purpose</div>
            <div class="value"><?php echo $app['Purpose']; ?></div>
          </div>
          <div class="kv">
            <div class="label">Application ID</div>
            <div class="value">#<?php echo $app['ApplicationID']; ?></div>
          </div>
        </div>

        <h4 style="margin:0 0 8px 0;">Offers</h4>

        <?php if (count($app['offers']) === 0) { ?>
          <div class="empty">No offers from banks yet for this application.</div>
        <?php } else { ?>
          <div class="offers-grid">
            <?php foreach ($app['offers'] as $o): 
              $bankDisplay = $o['BankName'] ? $o['BankName'] . ($o['BranchName'] ? " (" . $o['BranchName'] . ")" : "") : "—";
              $status = strtolower($o['OfferStatus']);
              $statusClass = ($status==='accepted' ? 'good' : ($status==='proposed' ? 'warn' : ($status==='rejected' ? 'bad' : '')));

              $isAcceptedThis = ($status === 'accepted');
              $canAct = (!$isAcceptedThis && $status!=='rejected' && !$appAccepted); 
              $lockTitle = $appAccepted && !$isAcceptedThis ? "Another offer was already accepted for this application." : "";
            ?>
              <div class="offer">
                <div class="offer-head">
                  <div class="bank-tag"><?php echo htmlspecialchars($bankDisplay, ENT_QUOTES, 'UTF-8'); ?><br><small>Offer #<?php echo $o['OfferID']; ?> • <?php echo $o['OfferDate']; ?></small></div>
                  <div class="status <?php echo $statusClass; ?>"><?php echo $o['OfferStatus'] ?: '—'; ?></div>
                </div>

                <div class="offer-meta">
                  <div class="kv">
                    <div class="label">Interest Rate (p.a.)</div>
                    <div class="value"><?php echo number_format($o['InterestRate'],2); ?>%</div>
                  </div>
                  <div class="kv">
                    <div class="label">Repayment Term</div>
                    <div class="value"><?php echo (int)$o['RepaymentTerm']; ?> months</div>
                  </div>
                  <div class="kv">
                    <div class="label">Linked App</div>
                    <div class="value">#<?php echo $app['ApplicationID']; ?></div>
                  </div>
                </div>

                <div class="actions">
                  <form method="post" action="accept_offer.php" style="display:inline">
                    <input type="hidden" name="offer_id" value="<?php echo $o['OfferID']; ?>">
                    <input type="hidden" name="application_id" value="<?php echo $app['ApplicationID']; ?>">
                    <button class="btn primary min" type="submit" <?php echo $canAct ? '' : 'disabled'; echo $lockTitle ? ' title="'.$lockTitle.'"' : ''; ?>>Accept Offer</button>
                  </form>
                  <form method="post" action="reject_offer.php" style="display:inline">
                    <input type="hidden" name="offer_id" value="<?php echo $o['OfferID']; ?>">
                    <button class="btn min" type="submit" <?php echo $canAct ? '' : 'disabled'; echo $lockTitle ? ' title="'.$lockTitle.'"' : ''; ?>>Reject</button>
                  </form>
                  <a class="btn min" href="chat.php?offer_id=<?php echo $o['OfferID']; ?>" <?php echo ($isAcceptedThis ? 'disabled title="Chat locked after acceptance"' : ''); ?>>Chat</a>
                </div>
              </div>
            <?php endforeach; ?>
          </div>
        <?php } ?>
      </div>
    <?php endforeach; ?>

  <?php } ?>

  <div class="footer-links">
    <a class="btn" href="customer_dashboard.php">Back to Dashboard</a>
  </div>
</body>
</html>
