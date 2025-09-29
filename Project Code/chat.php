<?php
require "dbconnect.php";
if (!isset($_SESSION['user_id'])) { header("Location: login.html"); exit; }

if (!isset($_GET['offer_id'])) die("Missing offer_id");
$offerId = (int)$_GET['offer_id'];
$uid = (int)$_SESSION['user_id'];
$role = $_SESSION['role'];

$infoSql = "SELECT 
              LO.OfferID, LO.Status AS OfferStatus, LO.ApplicationID, LO.StaffID,
              LR.CustomerID, LR.Purpose, LR.AmountRequested, LR.Term,
              BS.Bank, BS.Branch
            FROM LoanOffer LO
            JOIN LoanRequest LR ON LO.ApplicationID = LR.ApplicationID
            LEFT JOIN BankStaff BS ON LO.StaffID = BS.StaffID
            WHERE LO.OfferID = $offerId
            LIMIT 1";
$infoRes = $mysqli->query($infoSql);
if (!$infoRes || $infoRes->num_rows !== 1) { if ($infoRes) $infoRes->free(); die("Offer not found"); }
$offer = $infoRes->fetch_assoc();
$infoRes->free();

/* when and who can see chat */
$canView = false;
$canSend = false;

if ($role === 'customer') {
  $cRes = $mysqli->query("SELECT CustomerID FROM Customer WHERE ID=$uid LIMIT 1");
  $myCid = ($cRes && $cRes->num_rows===1) ? (int)$cRes->fetch_assoc()['CustomerID'] : 0;
  if ($cRes) $cRes->free();
  $canView = ($myCid > 0 && $myCid === (int)$offer['CustomerID']);
  $canSend = $canView && ($offer['OfferStatus'] !== 'Accepted');
} elseif ($role === 'bank') {
  $sRes = $mysqli->query("SELECT StaffID FROM BankStaff WHERE ID=$uid LIMIT 1");
  $mySid = ($sRes && $sRes->num_rows===1) ? (int)$sRes->fetch_assoc()['StaffID'] : 0;
  if ($sRes) $sRes->free();
  $canView = ($mySid > 0 && $mySid === (int)$offer['StaffID']);
  $canSend = $canView && ($offer['OfferStatus'] !== 'Accepted');
}
if (!$canView) die("You are not allowed to view this chat.");

/* Chat */
$msgs = [];
$mRes = $mysqli->query("SELECT MessageID, SenderUserID, Message, SentAt 
                        FROM ChatMessage 
                        WHERE OfferID=$offerId 
                        ORDER BY SentAt ASC, MessageID ASC");
if ($mRes) {
  while ($row = $mRes->fetch_assoc()) {
    $msgs[] = [
      'mid' => (int)$row['MessageID'],
      'from' => (int)$row['SenderUserID'],
      'text' => $row['Message'],
      'at' => $row['SentAt']
    ];
  }
  $mRes->free();
}

/*  */
$bankTitle = $offer['Bank'] ? htmlspecialchars($offer['Bank'], ENT_QUOTES, 'UTF-8') : 'Bank';
if (!empty($offer['Branch'])) $bankTitle .= " (" . htmlspecialchars($offer['Branch'], ENT_QUOTES, 'UTF-8') . ")";
$offerStatus = htmlspecialchars($offer['OfferStatus'], ENT_QUOTES, 'UTF-8');
$appId = (int)$offer['ApplicationID'];
$purpose = htmlspecialchars((string)$offer['Purpose'], ENT_QUOTES, 'UTF-8');
$amount = number_format((float)$offer['AmountRequested'], 2);
$term = (int)$offer['Term'];

/* HHome Button */
$homeHref = ($role === 'customer') ? 'customer_dashboard.php'
          : (($role === 'bank') ? 'bank_dashboard.php' : 'login.html');

$backHref = ($role === 'customer') ? 'view_offers.php'
          : (($role === 'bank') ? ('bank_offers_for_app.php?application_id=' . $appId) : 'login.html');
?>
<!DOCTYPE html>
<html>
<head>
  <meta charset="utf-8">
  <title>Chat for Offer #<?php echo $offerId; ?></title>
  <style>
    :root{
      --bg:#f7f7fb; --card:#fff; --text:#222; --muted:#666; --primary:#2f6fed;
      --border:#e9e9ef; --shadow:0 10px 26px rgba(0,0,0,.07); --radius:14px;
      --you:#e6f7ef; --them:#eef2ff;
    }
    *{box-sizing:border-box}
    body{margin:0 auto; padding:24px; max-width:900px; background:var(--bg); color:var(--text); font-family:system-ui,-apple-system,Segoe UI,Roboto,Arial,sans-serif}
    .header{display:flex; justify-content:space-between; gap:10px; align-items:center; margin-bottom:14px}
    .btn{border:1px solid var(--border); background:var(--card); padding:8px 14px; border-radius:10px; cursor:pointer; text-decoration:none; color:inherit}
    .btn.primary{background:var(--primary); color:#fff; border-color:transparent}
    .btn[disabled]{opacity:.45; cursor:not-allowed}
    .card{background:var(--card); border:1px solid var(--border); border-radius:var(--radius); box-shadow:var(--shadow); padding:16px;}
    .meta{display:flex; flex-wrap:wrap; gap:8px; margin-bottom:12px}
    .chip{background:#fafafe; border:1px solid var(--border); border-radius:999px; padding:6px 10px; font-size:12px}
    .chat{display:flex; flex-direction:column; gap:10px; height:520px; overflow:auto; padding:10px; background:#fcfcff; border:1px solid var(--border); border-radius:12px; margin-bottom:10px}
    .row{display:flex;}
    .bubble{max-width:70%; padding:10px 12px; border-radius:12px; border:1px solid var(--border); box-shadow:0 2px 8px rgba(0,0,0,.04)}
    .me{justify-content:flex-end}
    .me .bubble{background:var(--you)}
    .them .bubble{background:var(--them)}
    .small{font-size:12px; color:var(--muted); margin-top:4px}
    form.send{display:flex; gap:8px}
    textarea{flex:1; border:1px solid var(--border); border-radius:10px; padding:10px; min-height:48px; resize:vertical}
  </style>
</head>
<body>
  <div class="header">
    <h2>Offer #<?php echo $offerId; ?> • Chat</h2>
    <div>
      <a class="btn" href="<?php echo $backHref; ?>">Back</a>
      <a class="btn" href="<?php echo $homeHref; ?>">Home</a>
    </div>
  </div>

  <div class="card" style="margin-bottom:12px">
    <div class="meta">
      <div class="chip">App #<?php echo $appId; ?></div>
      <div class="chip">Purpose: <?php echo $purpose; ?></div>
      <div class="chip">Amount: <?php echo $amount; ?></div>
      <div class="chip">Term: <?php echo $term; ?> mo</div>
      <div class="chip">Status: <?php echo $offerStatus; ?></div>
      <div class="chip">Bank: <?php echo $bankTitle; ?></div>
    </div>
    <div class="chat" id="chatbox">
      <?php if (count($msgs) === 0) { ?>
        <div class="small">Don't Be a S.A. Alam. Start the conversation.</div>
      <?php } else { 
        foreach ($msgs as $m) {
          $isMe = ($m['from'] === $uid);
          $cls = $isMe ? "row me" : "row them";
          $text = nl2br(htmlspecialchars($m['text'], ENT_QUOTES, 'UTF-8'));
          $at = htmlspecialchars($m['at'], ENT_QUOTES, 'UTF-8');
          echo "<div class='$cls'><div class='bubble'>$text<div class='small'>$at</div></div></div>";
        }
      } ?>
    </div>

    <form class="send" method="post" action="chat_send.php">
      <input type="hidden" name="offer_id" value="<?php echo $offerId; ?>">
      <textarea name="message" placeholder="<?php echo $canSend ? 'Type your message…' : 'Chat is read-only after acceptance'; ?>" <?php echo $canSend ? '' : 'disabled'; ?> ></textarea>
      <button class="btn primary" type="submit" <?php echo $canSend ? '' : 'disabled'; ?>>Send</button>
    </form>
  </div>

  <script>
    var box = document.getElementById('chatbox');
    if (box) { box.scrollTop = box.scrollHeight; }
  </script>
</body>
</html>
