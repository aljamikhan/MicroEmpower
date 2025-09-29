<?php
require "dbconnect.php";
if (!isset($_SESSION['user_id']) || $_SESSION['role']!=='customer') { header("Location: login.html"); exit; }
$uid = (int)$_SESSION['user_id'];


$stmt = $mysqli->prepare("SELECT CustomerID FROM Customer WHERE ID=? LIMIT 1");
$stmt->bind_param('i', $uid);
$stmt->execute();
$stmt->bind_result($cid);
if (!$stmt->fetch()) { die("Customer not found"); }
$stmt->close();

/* Installments */
$sql = "
  SELECT RS.ScheduleID, RS.LoanID, RS.DueDate, RS.InterestDue, RS.PrincipalDue, IFNULL(P.SumPenalty,0) AS Penalties
  FROM RepaymentSchedule RS
  JOIN LoanContract LC ON RS.LoanID=LC.LoanID
  JOIN LoanRequest LR ON LC.ApplicationID=LR.ApplicationID
  LEFT JOIN (SELECT ScheduleID, SUM(Amount) AS SumPenalty FROM Penalty GROUP BY ScheduleID) P
    ON P.ScheduleID=RS.ScheduleID
  WHERE LR.CustomerID=? AND RS.Status='DUE'
  ORDER BY RS.DueDate ASC";
$stmt = $mysqli->prepare($sql);
$stmt->bind_param('i', $cid);
$stmt->execute();
$res = $stmt->get_result();

$rows = [];
while ($r = $res->fetch_assoc()) { $rows[] = $r; }
$stmt->close();

/* Xssssssssssss */
function e($s){ return htmlspecialchars((string)$s, ENT_QUOTES, 'UTF-8'); }
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <title>Make Payment • Due Installments</title>
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <style>
    :root{
      --bg1:#eef2f7;
      --bg2:#e6edf7;
      --text:#0f172a;
      --muted:#5b6b84;
      --line:#e5eaf2;
      --card:#ffffff;
      --accent:#3b82f6;
      --accent2:#22d3ee;
      --radius:16px;
      --shadow:0 10px 28px rgba(15,23,42,.07), 0 1px 0 rgba(15,23,42,.06);
    }
    *{box-sizing:border-box}
    body{
      margin:0; min-height:100vh; font-family:"Segoe UI", Arial, sans-serif; color:var(--text);
      background:
        radial-gradient(1200px 800px at 12% 8%, #bfe3ff40 0%, transparent 55%),
        radial-gradient(1100px 700px at 88% 92%, #b9fff340 0%, transparent 60%),
        linear-gradient(120deg, var(--bg1), var(--bg2));
      display:flex; align-items:center; justify-content:center;
      position:relative; overflow-x:hidden;
    }
    /* soft mesh haze */
    .haze{
      position:fixed; inset:-25vmax; pointer-events:none; z-index:0;
      background: conic-gradient(from 0deg at 50% 50%, #aee1f955, #c6e2d955, #f9e0ae55, #f9b6ae55, #aee1f955);
      filter: blur(120px) saturate(110%); opacity:.12;
      animation: spin 60s linear infinite;
    }
    @keyframes spin { to { transform: rotate(360deg); } }

    .wrap{ width:min(1100px, 94vw); margin:40px auto; position:relative; z-index:1; }
    .header{
      display:flex; align-items:flex-end; justify-content:space-between; gap:12px; margin-bottom:14px;
    }
    h1{
      margin:0; font-size:clamp(1.15rem, 1rem + 1.2vw, 1.6rem); font-weight:700;
      letter-spacing:.2px;
    }
    .sub{ margin:6px 0 0; color:var(--muted); font-size:.95rem; }

    .card{
      background:var(--card); border:1px solid var(--line); border-radius:var(--radius);
      box-shadow:var(--shadow); padding:18px;
    }

    .table-wrap{ overflow:auto; border-radius:12px; border:1px solid var(--line); }
    table{
      width:100%; border-collapse:separate; border-spacing:0; background:#fff;
    }
    thead th{
      position:sticky; top:0; background:linear-gradient(180deg,#f7fafc,#f0f5fb);
      text-align:left; font-weight:600; color:#334155; padding:12px 14px; border-bottom:1px solid var(--line);
    }
    tbody td{
      padding:12px 14px; border-bottom:1px solid var(--line); vertical-align:middle; color:#1f2937;
    }
    tbody tr:nth-child(odd) td{ background:#fbfdff; }
    tbody tr:hover td{ background:#f5faff; }

    .num{ text-align:right; font-variant-numeric: tabular-nums; }
    .date{ white-space:nowrap; }

    .pay-form{
      display:flex; align-items:center; gap:8px; flex-wrap:wrap; justify-content:flex-end;
    }
    .pay-form input[type="number"]{
      width:110px; padding:8px 10px; border:1px solid #cfdaeb; border-radius:10px;
      background:#f7fbff; font-size:.95rem;
    }
    .pay-form select{
      padding:8px 10px; border:1px solid #cfdaeb; border-radius:10px;
      background:#fff;
    }
    .btn{
      padding:9px 14px; border:0; border-radius:10px; cursor:pointer;
      font-weight:600; color:#fff;
      background:linear-gradient(135deg, var(--accent), var(--accent2));
      box-shadow:0 6px 18px rgba(59,130,246,.25);
      transition: transform .05s ease, box-shadow .2s ease;
    }
    .btn:hover{ box-shadow:0 10px 24px rgba(34,211,238,.28); }
    .btn:active{ transform:translateY(1px); }

    .toolbar{
      display:flex; justify-content:space-between; align-items:center; gap:10px; margin-bottom:10px;
    }
    .back{
      display:inline-flex; align-items:center; gap:8px; text-decoration:none; color:#0f172a;
      padding:8px 12px; border-radius:10px; border:1px solid #dbe4f2; background:#fff;
      box-shadow:0 1px 0 rgba(15,23,42,.05);
    }
    .badge{
      padding:6px 10px; border:1px solid #dbe4f2; border-radius:999px; background:#f3f7fc; color:#4b5563; font-size:.85rem;
    }

    /* Empty state */
    .empty{
      text-align:center; padding:36px 20px; color:#4b5563;
    }
    .empty h2{ margin:0 0 6px; font-size:1.15rem; }
    .empty p{ margin:0 0 12px; color:#64748b; }
  </style>
</head>
<body>
  <div class="haze" aria-hidden="true"></div>

  <div class="wrap">
    <div class="header">
      <div>
        <h1>Make Payment</h1>
        <p class="sub">Review your due installments and pay securely.</p>
      </div>
      <a class="back" href="customer_dashboard.php">
        <!-- left arrow -->
        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" aria-hidden="true"><path d="M8 12h12M8 12l4-4M8 12l4 4" stroke="#334155" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round"/></svg>
        Back
      </a>
    </div>

    <div class="card">
      <?php if (count($rows) === 0): ?>
        <div class="empty">
          <h2>No due installments right now</h2>
          <p>You’re all caught up. Check back later or view your loan details from the dashboard.</p>
          <a class="back" href="customer_dashboard.php">← Back to Dashboard</a>
        </div>
      <?php else: ?>
        <div class="toolbar">
          <span class="badge">Due Payments: <?php echo count($rows); ?></span>
        </div>
        <div class="table-wrap">
          <table role="table">
            <thead>
              <tr>
                <th>Schedule ID</th>
                <th>Loan ID</th>
                <th>Due Date</th>
                <th class="num">Interest</th>
                <th class="num">Principal</th>
                <th class="num">Penalty</th>
                <th class="num">Total</th>
                <th style="text-align:right;">Pay</th>
              </tr>
            </thead>
            <tbody>
              <?php foreach ($rows as $row):
                $interest = (float)$row['InterestDue'];
                $principal = (float)$row['PrincipalDue'];
                $penalty = (float)$row['Penalties'];
                $total = $interest + $principal + $penalty;
                $val = number_format($total, 2, '.', '');
              ?>
              <tr>
                <td><?php echo e($row['ScheduleID']); ?></td>
                <td><?php echo e($row['LoanID']); ?></td>
                <td class="date"><?php echo e($row['DueDate']); ?></td>
                <td class="num"><?php echo number_format($interest,2); ?></td>
                <td class="num"><?php echo number_format($principal,2); ?></td>
                <td class="num"><?php echo number_format($penalty,2); ?></td>
                <td class="num"><strong><?php echo number_format($total,2); ?></strong></td>
                <td>
                  <form class="pay-form" method="post" action="process_payment.php" onsubmit="return confirmPay(this);">
                    <input type="hidden" name="schedule_id" value="<?php echo e($row['ScheduleID']); ?>">
                    <input type="number" step="0.01" min="0" name="amount" value="<?php echo e($val); ?>" required>
                    <select name="method" required>
                      <option value="Cash">Cash</option>
                      <option value="Card">Card</option>
                      <option value="Mobile">Mobile</option>
                    </select>
                    <button class="btn" type="submit">Pay</button>
                  </form>
                </td>
              </tr>
              <?php endforeach; ?>
            </tbody>
          </table>
        </div>
      <?php endif; ?>
    </div>
  </div>

  <script>
    function confirmPay(form){
      const id = form.querySelector('input[name="schedule_id"]')?.value || '';
      const amt = form.querySelector('input[name="amount"]')?.value || '';
      return confirm(`Proceed with payment?\nSchedule #${id}\nAmount: ${amt}`);
    }
  </script>
</body>
</html>
