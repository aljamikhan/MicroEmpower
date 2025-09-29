<?php
require "dbconnect.php";
if (!isset($_SESSION['user_id']) || $_SESSION['role']!=='bank') { header("Location: login.html"); exit; }
?>
<!DOCTYPE html>
<html>
<head>
  <meta charset="utf-8">
  <title>Loan Applications</title>
  <style>
    body { font-family: system-ui, -apple-system, Segoe UI, Roboto, Arial, sans-serif; margin: 20px; }
    h3 { margin-top: 0; }
    table { border-collapse: collapse; width: 100%; background: #fff; }
    th, td { border: 1px solid #e6e6ef; padding: 10px; text-align: left; }
    th { background: #f6f7fb; }
    .actions { display: flex; gap: 8px; flex-wrap: wrap; }
    .btn {
      border: 1px solid #e0e0ea; background: #fff; padding: 6px 12px; border-radius: 8px; cursor: pointer;
      text-decoration: none; display: inline-block; font-size: 14px; color: inherit;
    }
    .btn:hover { background: #f6f7fb; }
    .btn[disabled] { opacity: .45; cursor: not-allowed; }
    .note { color: #666; font-size: 12px; margin-top: 10px; }
    .chip { display:inline-block; padding:2px 8px; border-radius:999px; background:#eef2ff; color:#2f46c0; border:1px solid #e6e8ff; font-size:12px; }
  </style>
</head>
<body>
  <h3>Loan Applications</h3>
<?php
$sql = "
  SELECT 
    LR.ApplicationID, LR.ApplicationDate, LR.AmountRequested, LR.Term, LR.Purpose, LR.Status,
    C.CustomerID, U.UserName, U.Email,

    (SELECT COUNT(*) FROM LoanOffer LO 
      WHERE LO.ApplicationID = LR.ApplicationID 
        AND LO.Status = 'Accepted') AS AcceptedOffers,

    (SELECT COUNT(*) FROM LoanOffer LO WHERE LO.ApplicationID = LR.ApplicationID) AS TotalOffers,
    (SELECT COUNT(*) FROM LoanOffer LO WHERE LO.ApplicationID = LR.ApplicationID AND LO.Status='Proposed') AS ProposedOffers,
    (SELECT COUNT(*) FROM LoanOffer LO WHERE LO.ApplicationID = LR.ApplicationID AND LO.Status='Rejected') AS RejectedOffers
  FROM LoanRequest LR
  JOIN Customer C ON LR.CustomerID = C.CustomerID
  JOIN Users U ON C.ID = U.ID
  ORDER BY LR.ApplicationID DESC";
$res = $mysqli->query($sql);

if (!$res || $res->num_rows===0) {
  echo "<p>No applications.</p><p><a class='btn' href='bank_dashboard.php'>Back</a></p>";
  if ($res) $res->free();
  exit;
}
?>
  <table>
    <tr>
      <th>AppID</th>
      <th>Date</th>
      <th>Customer</th>
      <th>Email</th>
      <th>Amount</th>
      <th>Term</th>
      <th>Purpose</th>
      <th>Status</th>
      <th>Offers</th>
      <th>Actions</th>
    </tr>
<?php
while ($r = $res->fetch_assoc()) {
  $appId   = (int)$r['ApplicationID'];
  $date    = htmlspecialchars((string)$r['ApplicationDate'], ENT_QUOTES, 'UTF-8');
  $name    = htmlspecialchars((string)$r['UserName'], ENT_QUOTES, 'UTF-8');
  $email   = htmlspecialchars((string)$r['Email'], ENT_QUOTES, 'UTF-8');
  $purpose = htmlspecialchars((string)$r['Purpose'], ENT_QUOTES, 'UTF-8');
  $status  = htmlspecialchars((string)$r['Status'], ENT_QUOTES, 'UTF-8');

  $amount  = number_format((float)$r['AmountRequested'], 2);
  $term    = (int)$r['Term'];

  $acceptedOffers = (int)$r['AcceptedOffers'];
  $totalOffers    = (int)$r['TotalOffers'];
  $proposedOffers = (int)$r['ProposedOffers'];
  $rejectedOffers = (int)$r['RejectedOffers'];

  $disableMakeOffer = ($acceptedOffers > 0);

  if ($totalOffers === 0) {
    $offerHint = "None yet";
  } else {
    $parts = [];
    $parts[] = "{$totalOffers} total";
    if ($acceptedOffers > 0) $parts[] = "{$acceptedOffers} accepted";
    if ($proposedOffers > 0) $parts[] = "{$proposedOffers} proposed";
    if ($rejectedOffers > 0) $parts[] = "{$rejectedOffers} rejected";
    $offerHint = implode(" • ", $parts);
  }

  echo "<tr>";
  echo "<td>{$appId}</td>";
  echo "<td>{$date}</td>";
  echo "<td>{$name}</td>";
  echo "<td>{$email}</td>";
  echo "<td>{$amount}</td>";
  echo "<td>{$term}</td>";
  echo "<td>{$purpose}</td>";
  echo "<td>{$status}</td>";
  echo "<td><span class='chip'>{$offerHint}</span></td>";
  echo "<td>
          <div class='actions'>
            <form style='display:inline' method='get' action='compute_risk.php'>
              <input type='hidden' name='customer_id' value='{$r['CustomerID']}'>
              <button class='btn' type='submit'>Risk score</button>
            </form>
            <form style='display:inline' method='get' action='make_offer.php'>
              <input type='hidden' name='application_id' value='{$appId}'>
              <button class='btn' type='submit' ".($disableMakeOffer ? "disabled title='An offer has already been accepted for this application.'" : "").">Make offer</button>
            </form>
            <form style='display:inline' method='get' action='bank_offers_for_app.php'>
              <input type='hidden' name='application_id' value='{$appId}'>
              <button class='btn' type='submit'>Offers / Chat</button>
            </form>
          </div>
        </td>";
  echo "</tr>";
}
$res->free();
?>
  </table>

  <p class="note">
    <strong>Make offer</strong> is disabled when an offer has been Accepted for the application.  
    Use <strong>Offers/Chat</strong> to open chat with your Customer.
  </p>

  <p><a class="btn" href="bank_dashboard.php">Back</a></p>
</body>
</html>
