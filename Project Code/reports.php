<?php
require "dbconnect.php";
if (!isset($_SESSION['user_id']) || $_SESSION['role']!=='bank') { header("Location: login.html"); exit; }

$r1=$mysqli->query("SELECT COUNT(*) AS c FROM LoanRequest"); $c1=$r1?$r1->fetch_assoc()['c']:0; if($r1) $r1->free();
$r2=$mysqli->query("SELECT COUNT(*) AS c FROM LoanOffer");  $c2=$r2?$r2->fetch_assoc()['c']:0; if($r2) $r2->free();

$r3=$mysqli->query("SELECT COUNT(*) AS c, SUM(PrincipalAmount) AS sumP FROM LoanContract");
$a3=$r3?$r3->fetch_assoc():['c'=>0,'sumP'=>0]; if($r3) $r3->free();
$c3=$a3['c']??0; $sumP=$a3['sumP']??0;

$r4=$mysqli->query("SELECT SUM(AmountPaid) AS paid FROM Payment"); $paid=$r4?$r4->fetch_assoc()['paid']:0; if($r4) $r4->free();

$out=0.0;
$r5=$mysqli->query("SELECT (RS.InterestDue+RS.PrincipalDue+IFNULL(P.SumPenalty,0)) AS total
                                        FROM RepaymentSchedule RS
                                        LEFT JOIN (SELECT ScheduleID, SUM(Amount) AS SumPenalty FROM Penalty GROUP BY ScheduleID) P
                                        ON RS.ScheduleID=P.ScheduleID
                                        WHERE RS.Status='DUE'");
while($r5 && $row=$r5->fetch_assoc()){ $out += (float)$row['total']; }
if ($r5) $r5->free();
?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Reports</title>
    <style>
        body {
            margin: 0;
            min-height: 100vh;
            font-family: 'Segoe UI', Arial, sans-serif;
            background: #f7f7fb;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        .reports-card {
            background: rgba(255,255,255,0.97);
            border-radius: 24px;
            box-shadow: 0 8px 32px rgba(80,90,120,0.09), 0 1.5px 8px rgba(80,90,120,0.07);
            padding: 58px 48px 48px 48px;
            min-width: 360px;
            max-width: 680px;
            text-align: center;
            border: 1.5px solid #e3e9f7;
            position: relative;
        }
        .accent-bar {
            height: 6px;
            width: 60px;
            background: linear-gradient(90deg, #7ec4e8 0%, #c6e2d9 100%);
            border-radius: 999px;
            margin: 0 auto 18px auto;
        }
        .reports-title {
            font-size: 1.35rem;
            font-weight: 700;
            color: #3a4660;
            letter-spacing: 0.7px;
            margin-bottom: 18px;
            text-shadow: 0 2px 16px #e3e9f7;
        }
        .dashboard-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(140px, 1fr));
            gap: 18px;
            margin: 0 0 18px 0;
        }
        .metric-card {
            background: rgba(240,245,255,0.85);
            border-radius: 18px;
            box-shadow: 0 2px 8px #e3e9f7;
            padding: 18px 10px 14px 10px;
            text-align: center;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            min-width: 0;
        }
        .metric-icon {
            margin-bottom: 8px;
        }
        .metric-label {
            font-size: 1.01rem;
            color: #4a5a7b;
            margin-bottom: 6px;
            font-weight: 600;
        }
        .metric-value {
            font-size: 1.18rem;
            font-weight: 700;
            color: #3a4660;
            background: #fff;
            border-radius: 8px;
            padding: 6px 12px;
            box-shadow: 0 1px 4px #e3e9f7;
            margin-bottom: 2px;
        }
        .metric-card.apps .metric-icon svg { filter: drop-shadow(0 2px 8px #7ec4e8aa); }
        .metric-card.offers .metric-icon svg { filter: drop-shadow(0 2px 8px #f9e0aeaa); }
        .metric-card.contracts .metric-icon svg { filter: drop-shadow(0 2px 8px #c6e2d9aa); }
        .metric-card.principal .metric-icon svg { filter: drop-shadow(0 2px 8px #aee1f9aa); }
        .metric-card.paid .metric-icon svg { filter: drop-shadow(0 2px 8px #f9b6aeaa); }
        .metric-card.outstanding .metric-icon svg { filter: drop-shadow(0 2px 8px #c6e2d9aa); }
        .back-btn {
            display: inline-block;
            padding: 10px 32px;
            font-size: 1.08rem;
            font-weight: 600;
            color: #fff;
            border-radius: 999px;
            text-decoration: none;
            box-shadow: 0 2px 16px #e3e9f7, 0 1px 4px #bfc8e6;
            background: linear-gradient(90deg, #7ec4e8 0%, #aee1f9 100%);
            border: none;
            margin-top: 8px;
            position: relative;
            transition: background 0.2s, box-shadow 0.2s, transform 0.2s;
        }
        .back-btn:hover {
            filter: brightness(1.08) saturate(1.2);
            transform: translateY(-2px) scale(1.04);
            box-shadow: 0 8px 32px #e3e9f7, 0 2px 8px #bfc8e6;
        }
        @media (max-width: 600px) {
            .reports-card { padding: 18px 8px; min-width: 0; max-width: 98vw; }
            .reports-title { font-size: 1.08rem; }
            .dashboard-grid { gap: 10px; }
            .metric-label { font-size: 0.98rem; }
            .metric-value { font-size: 1.02rem; padding: 5px 8px; }
            .back-btn { font-size: 0.98rem; padding: 9px 18px; }
        }
    </style>
</head>
<body>
    <div class="reports-card">
        <div class="accent-bar"></div>
        <div class="reports-title">Reports</div>
        <div class="dashboard-grid">
            <div class="metric-card apps">
                <div class="metric-icon">
                    <svg width="32" height="32" viewBox="0 0 32 32"><rect x="6" y="10" width="20" height="12" rx="6" fill="#7ec4e8"/><rect x="12" y="18" width="8" height="4" rx="2" fill="#aee1f9"/></svg>
                </div>
                <div class="metric-label">Applications</div>
                <div class="metric-value"><?php echo $c1; ?></div>
            </div>
            <div class="metric-card offers">
                <div class="metric-icon">
                    <svg width="32" height="32" viewBox="0 0 32 32"><rect x="8" y="8" width="16" height="16" rx="6" fill="#f9e0ae"/><rect x="12" y="12" width="8" height="4" rx="2" fill="#c6e2d9"/></svg>
                </div>
                <div class="metric-label">Offers</div>
                <div class="metric-value"><?php echo $c2; ?></div>
            </div>
            <div class="metric-card contracts">
                <div class="metric-icon">
                    <svg width="32" height="32" viewBox="0 0 32 32"><rect x="10" y="10" width="12" height="12" rx="6" fill="#c6e2d9"/><rect x="14" y="14" width="4" height="4" rx="2" fill="#e3e9f7"/></svg>
                </div>
                <div class="metric-label">Contracts</div>
                <div class="metric-value"><?php echo $c3; ?></div>
            </div>
            <div class="metric-card principal">
                <div class="metric-icon">
                    <svg width="32" height="32" viewBox="0 0 32 32"><ellipse cx="16" cy="16" rx="12" ry="8" fill="#aee1f9"/><rect x="12" y="20" width="8" height="4" rx="2" fill="#7ec4e8"/></svg>
                </div>
                <div class="metric-label">Principal</div>
                <div class="metric-value"><?php echo number_format((float)$sumP,2); ?></div>
            </div>
            <div class="metric-card paid">
                <div class="metric-icon">
                    <svg width="32" height="32" viewBox="0 0 32 32"><rect x="8" y="16" width="16" height="8" rx="4" fill="#f9b6ae"/><rect x="12" y="20" width="8" height="4" rx="2" fill="#f9e0ae"/></svg>
                </div>
                <div class="metric-label">Total Paid</div>
                <div class="metric-value"><?php echo number_format((float)$paid,2); ?></div>
            </div>
            <div class="metric-card outstanding">
                <div class="metric-icon">
                    <svg width="32" height="32" viewBox="0 0 32 32"><ellipse cx="16" cy="20" rx="10" ry="6" fill="#f9e0ae"/><rect x="12" y="24" width="8" height="4" rx="2" fill="#c6e2d9"/></svg>
                </div>
                <div class="metric-label">Outstanding</div>
                <div class="metric-value"><?php echo number_format($out,2); ?></div>
            </div>
        </div>
        <a class="back-btn" href="bank_dashboard.php">Back</a>
    </div>
</body>
</html>
