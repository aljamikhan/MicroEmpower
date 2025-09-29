<!DOCTYPE html>
<html>
<head>
  <meta charset="utf-8">
  <title>My Current Loans</title>
  <style>
    body {
      margin: 0;
      min-height: 100vh;
      font-family: 'Segoe UI', Arial, sans-serif;
      background: linear-gradient(120deg, #f7f8fa 0%, #e9ecf2 100%);
      display: flex;
      align-items: center;
      justify-content: center;
      position: relative;
      overflow: hidden;
    }
    .bg-gradient-shape {
      position: absolute;
      z-index: 0;
      pointer-events: none;
      opacity: 0.22;
      filter: blur(24px);
    }
    .bg-gradient1 {
      top: -120px;
      left: -80px;
      width: 420px;
      height: 420px;
      background: radial-gradient(circle, #e3e9f7 0%, #f7f8fa 80%);
      border-radius: 50%;
    }
    .bg-gradient2 {
      bottom: -100px;
      right: -120px;
      width: 480px;
      height: 380px;
      background: radial-gradient(circle, #bfc8e6 0%, #f7f8fa 80%);
      border-radius: 50%;
    }
    .container {
      background: rgba(255,255,255,0.98);
      border-radius: 22px;
      box-shadow: 0 6px 24px rgba(80,90,120,0.07), 0 1.5px 8px rgba(80,90,120,0.04);
      padding: 44px 38px 38px 38px;
      min-width: 320px;
      max-width: 900px;
      width: 100%;
      text-align: center;
      backdrop-filter: blur(6px);
      border: 1.5px solid #e3e9f7;
      z-index: 2;
      position: relative;
      box-sizing: border-box;
    }
    h3 {
      font-size: 2rem;
      font-weight: 700;
      margin: 0 0 28px 0;
      color: #3a4660;
      letter-spacing: 0.7px;
      text-shadow: 0 2px 16px #e3e9f7;
      word-break: break-word;
    }
    table {
      width: 100%;
      border-collapse: collapse;
      margin: 0 auto 18px auto;
      background: rgba(255,255,255,0.98);
      border-radius: 16px;
      overflow: hidden;
      box-shadow: 0 2px 12px #e3e9f7;
    }
    th, td {
      padding: 12px 10px;
      text-align: center;
      font-size: 1.08rem;
      border-bottom: 1px solid #e3e9f7;
    }
    th {
      background: linear-gradient(90deg, #e3e9f7 0%, #f7f8fa 100%);
      color: #727989;
      font-weight: 700;
      font-size: 1.08rem;
      border-bottom: 2px solid #bfc8e6;
    }
    tr:last-child td {
      border-bottom: none;
    }
    td {
      color: #3a4660;
    }
    .back-btn {
      display: inline-block;
      padding: 11px 32px;
      font-size: 1.08rem;
      font-weight: 600;
      color: #fff;
      background: #bfc8e6;
      border-radius: 999px;
      text-decoration: none;
      box-shadow: 0 2px 16px #e3e9f7, 0 1px 4px #bfc8e6;
      transition: background 0.2s, box-shadow 0.2s, transform 0.2s;
      border: none;
      margin-top: 18px;
      position: relative;
      overflow: hidden;
      cursor: pointer;
    }
    .back-btn:hover {
      background: #727989;
      transform: translateY(-2px) scale(1.04);
      box-shadow: 0 8px 32px #e3e9f7, 0 2px 8px #bfc8e6;
    }
    @media (max-width: 700px) {
      .container { padding: 18px 4px; min-width: 0; max-width: 98vw; }
      h3 { font-size: 1.2rem; }
      th, td { font-size: 0.98rem; padding: 8px 4px; }
      .back-btn { font-size: 1rem; padding: 9px 18px; }
    }
  </style>
</head>
<body>
  <div class="bg-gradient-shape bg-gradient1"></div>
  <div class="bg-gradient-shape bg-gradient2"></div>
  <div class="container">
    <h3>My Current Loans</h3>
    <?php
    require "dbconnect.php";
    if (!isset($_SESSION['user_id']) || $_SESSION['role']!=='customer') { header("Location: login.html"); exit; }
    $uid = (int)$_SESSION['user_id'];

   
    $res = $mysqli->query("SELECT CustomerID FROM Customer WHERE ID=$uid LIMIT 1");
    if (!$res || $res->num_rows!==1) die("Customer not found");
    $cid = (int)$res->fetch_assoc()['CustomerID'];
    if ($res) $res->free();

    /* Penaltyyyyyyy */
    $over = $mysqli->query("SELECT RS.ScheduleID
                            FROM RepaymentSchedule RS
                            JOIN LoanContract LC ON RS.LoanID=LC.LoanID
                            JOIN LoanRequest LR ON LC.ApplicationID=LR.ApplicationID
                            WHERE LR.CustomerID=$cid AND RS.Status='DUE' AND RS.DueDate < CURDATE()");
    while ($over && $row = $over->fetch_assoc()) {
      $sid = (int)$row['ScheduleID'];
      $chk = $mysqli->query("SELECT PenaltyID FROM Penalty WHERE ScheduleID=$sid LIMIT 1");
      if ($chk && $chk->num_rows===0) {
        // adding the Penalty
        $mysqli->query("INSERT INTO Penalty (PenaltyType, Amount, ScheduleID) VALUES ('Overdue', 100.00, $sid)");

        // Minus riusk -5
        $today = date("Y-m-d");
        $cur = 100.0;
        $r = $mysqli->query("SELECT ScoreValue FROM RiskScore WHERE CustomerID=$cid ORDER BY CalculatedDate DESC LIMIT 1");
        if ($r && $r->num_rows===1) $cur = (float)$r->fetch_assoc()['ScoreValue'];
        if ($r) $r->free();
        $new = $cur - 5;
        $label = ($new>=90?'Good':($new>=70?'Moderate':'Risky'));
        $mysqli->query("INSERT INTO RiskScore (ScoreValue,RiskValue,CalculatedDate,CustomerID) VALUES ($new,'$label','$today',$cid)");
      }
      if ($chk) $chk->free();
    }
    if ($over) $over->free();

    /* List of loans i must pay, or S. A. ALAM */
    $list = $mysqli->query("SELECT 
                              LC.LoanID, LC.PrincipalAmount, LC.StartDate, LC.CurrentInterestRate, LC.Status,
                              BS.Bank AS BankName, BS.Branch AS BranchName
                            FROM LoanContract LC
                            JOIN LoanRequest LR ON LC.ApplicationID = LR.ApplicationID
                            LEFT JOIN LoanOffer LO ON LC.OfferID = LO.OfferID
                            LEFT JOIN BankStaff BS ON LO.StaffID = BS.StaffID
                            WHERE LR.CustomerID = $cid
                            ORDER BY LC.LoanID DESC");
    if (!$list || $list->num_rows===0) {
      echo "<div style='margin:32px 0;font-size:1.1rem;color:#727989;'>No loans yet.</div>";
      echo "<a class='back-btn' href='customer_dashboard.php'>Back to Dashboard</a>";
      if ($list) $list->free();
      exit;
    }

    echo "<table>";
    echo "<tr>
            <th>LoanID</th>
            <th>Bank</th>
            <th>Principal</th>
            <th>Start</th>
            <th>Rate%</th>
            <th>Status</th>
            <th>Due Amount (Unpaid)</th>
          </tr>";
    while ($loan = $list->fetch_assoc()){
      $lid = (int)$loan['LoanID'];

      // calculating (interest + principal + penalties)
      $sum = 0.0;
      $due = $mysqli->query("SELECT (RS.InterestDue + RS.PrincipalDue + IFNULL(P.SumPenalty,0)) AS total
                             FROM RepaymentSchedule RS
                             LEFT JOIN (
                               SELECT ScheduleID, SUM(Amount) AS SumPenalty FROM Penalty GROUP BY ScheduleID
                             ) P ON RS.ScheduleID=P.ScheduleID
                             WHERE RS.LoanID=$lid AND RS.Status='DUE'");
      while ($due && $row = $due->fetch_assoc()) { $sum += (float)$row['total']; }
      if ($due) $due->free();


      $bank = '';
      if (!empty($loan['BankName'])) {
        $bank = htmlspecialchars($loan['BankName'], ENT_QUOTES, 'UTF-8');
        if (!empty($loan['BranchName'])) {
          $bank .= " (" . htmlspecialchars($loan['BranchName'], ENT_QUOTES, 'UTF-8') . ")";
        }
      } else {
        $bank = "—";
      }

      echo "<tr>
        <td>{$loan['LoanID']}</td>
        <td>{$bank}</td>
        <td>".number_format((float)$loan['PrincipalAmount'],2)."</td>
        <td>{$loan['StartDate']}</td>
        <td>{$loan['CurrentInterestRate']}</td>
        <td>{$loan['Status']}</td>
        <td>".number_format($sum,2)."</td>
      </tr>";
    }
    echo "</table>";
    echo "<a class='back-btn' href='customer_dashboard.php'>Back to Dashboard</a>";
    $list->free();
    ?>
  </div>
</body>
</html>
