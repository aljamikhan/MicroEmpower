<?php
require "dbconnect.php";
if (!isset($_SESSION['user_id']) || $_SESSION['role']!=='bank') { header("Location: login.html"); exit; }
?>
<!DOCTYPE html>
<html>
<head>
  <meta charset="utf-8">
  <title>Bank Dashboard</title>
  <style>
    body {
      margin: 0;
      min-height: 100vh;
      font-family: 'Segoe UI', Arial, sans-serif;
      background: linear-gradient(120deg, #acb8d2ff 0%, #727989ff 100%);
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
      opacity: 0.45;
      filter: blur(18px);
    }
    .bg-gradient1 {
      top: -120px;
      left: -80px;
      width: 420px;
      height: 420px;
      background: radial-gradient(circle, #aee1f9 0%, #f7f8fa 80%);
      border-radius: 50%;
    }
    .bg-gradient2 {
      bottom: -100px;
      right: -120px;
      width: 480px;
      height: 380px;
      background: radial-gradient(circle, #f9e0ae 0%, #f7f8fa 80%);
      border-radius: 50%;
    }
    .bg-gradient3 {
      top: 60%;
      left: 10vw;
      width: 220px;
      height: 180px;
      background: radial-gradient(circle, #c6e2d9 0%, #f7f8fa 80%);
      border-radius: 50%;
    }
    .bg-gradient4 {
      top: 10vh;
      right: 12vw;
      width: 180px;
      height: 140px;
      background: radial-gradient(circle, #f9b6ae 0%, #f7f8fa 80%);
      border-radius: 50%;
    }
    .bg-line {
      position: absolute;
      z-index: 1;
      pointer-events: none;
      opacity: 0.22;
    }
    .dashboard-outer {
      width: 100vw;
      min-height: 100vh;
      display: flex;
      align-items: center;
      justify-content: center;
      background: radial-gradient(circle at 60% 40%, #f7f8fa 0%, #e9ecf2 60%, #f4f6fb 100%);
      position: relative;
      overflow: hidden;
    }
    .dashboard-grid {
      display: grid;
      grid-template-columns: repeat(auto-fit, minmax(260px, 1fr));
      gap: 32px;
      max-width: 700px;
      margin: 0 auto;
      z-index: 2;
    }
    .dashboard-card {
      background: rgba(255,255,255,0.92);
      border-radius: 28px;
      box-shadow: 0 8px 32px rgba(80,90,120,0.09), 0 1.5px 8px rgba(80,90,120,0.07);
      padding: 32px 24px 24px 24px;
      text-align: center;
      backdrop-filter: blur(10px);
      border: 1.5px solid #e3e9f7;
      transition: transform 0.22s, box-shadow 0.22s;
      position: relative;
      overflow: hidden;
    }
    .dashboard-card:hover {
      transform: translateY(-6px) scale(1.04);
      box-shadow: 0 16px 48px #bfc8e6, 0 2px 8px #e3e9f7;
      border-color: #bfc8e6;
    }
    .card-logo {
      width: 54px;
      height: 54px;
      border-radius: 50%;
      box-shadow: 0 2px 12px #e3e9f7;
      display: flex;
      align-items: center;
      justify-content: center;
      margin: 0 auto 18px auto;
      position: relative;
      background: linear-gradient(135deg, #f7f8fa 0%, #e3e9f7 100%);
    }
    .logo-info { background: linear-gradient(135deg, #f7f8fa 0%, #aee1f9 100%); }
    .logo-apps { background: linear-gradient(135deg, #f7f8fa 0%, #f9e0ae 100%); }
    .logo-reports { background: linear-gradient(135deg, #f7f8fa 0%, #c6e2d9 100%); }
    .logo-logout { background: linear-gradient(135deg, #f7f8fa 0%, #b6d0f9 100%); }
    .card-title {
      font-size: 1.25rem;
      font-weight: 700;
      color: #3a4660;
      letter-spacing: 0.7px;
      margin-bottom: 8px;
      text-shadow: 0 2px 16px #e3e9f7;
    }
    .card-desc {
      font-size: 1.01rem;
      color: #4a5a7b;
      margin-bottom: 18px;
      min-height: 32px;
    }
    .card-link {
      display: inline-block;
      padding: 11px 32px;
      font-size: 1.08rem;
      font-weight: 600;
      color: #fff;
      border-radius: 999px;
      text-decoration: none;
      box-shadow: 0 2px 16px #e3e9f7, 0 1px 4px #bfc8e6;
      transition: background 0.2s, box-shadow 0.2s, transform 0.2s;
      border: none;
      margin-top: 8px;
      position: relative;
      overflow: hidden;
    }
    .card-link.info {
      background: linear-gradient(90deg, #7ec4e8 0%, #aee1f9 100%);
    }
    .card-link.apps {
      background: linear-gradient(90deg, #f9c97a 0%, #f9e0ae 100%);
    }
    .card-link.reports {
      background: linear-gradient(90deg, #7ed6b7 0%, #c6e2d9 100%);
    }
    .card-link.logout {
      background: linear-gradient(90deg, #7eaef9 0%, #b6d0f9 100%);
    }
    .card-link:hover {
      filter: brightness(1.08) saturate(1.2);
      transform: translateY(-2px) scale(1.04);
      box-shadow: 0 8px 32px #e3e9f7, 0 2px 8px #bfc8e6;
    }
    @media (max-width: 900px) {
      .dashboard-grid { grid-template-columns: 1fr 1fr; }
    }
    @media (max-width: 600px) {
      .dashboard-grid { grid-template-columns: 1fr; gap: 18px; }
      .dashboard-card { padding: 18px 8px; }
      .card-logo { width: 38px; height: 38px; }
      .card-title { font-size: 1.08rem; }
      .card-link { font-size: 0.98rem; padding: 9px 18px; }
    }
  </style>
</head>
<body>
  <!-- Gradient shapes -->
  <div class="bg-gradient-shape bg-gradient1"></div>
  <div class="bg-gradient-shape bg-gradient2"></div>
  <div class="bg-gradient-shape bg-gradient3"></div>
  <div class="bg-gradient-shape bg-gradient4"></div>
  <!-- Designer transparent lines -->
  <svg class="bg-line" style="top:8vh;left:6vw;width:420px;height:80px;" viewBox="0 0 420 80" fill="none"><path d="M10 70 Q120 10 410 60" stroke="#7ec4e8" stroke-width="3" stroke-linecap="round" opacity="0.5"/><path d="M30 60 Q180 40 390 30" stroke="#f9e0ae" stroke-width="2" stroke-linecap="round" opacity="0.4"/></svg>
  <svg class="bg-line" style="bottom:8vh;right:6vw;width:320px;height:60px;" viewBox="0 0 320 60" fill="none"><path d="M10 50 Q120 10 310 40" stroke="#c6e2d9" stroke-width="3" stroke-linecap="round" opacity="0.5"/><path d="M30 40 Q180 30 290 20" stroke="#f9b6ae" stroke-width="2" stroke-linecap="round" opacity="0.4"/></svg>
  <div class="dashboard-outer">
    <div class="dashboard-grid">
      <div class="dashboard-card">
        <div class="card-logo logo-info">
          <svg width="38" height="38" viewBox="0 0 38 38" fill="none"><rect x="8" y="8" width="22" height="22" rx="8" fill="#7ec4e8"/><rect x="14" y="14" width="10" height="10" rx="5" fill="#aee1f9"/></svg>
        </div>
        <div class="card-title">Bank Information</div>
        <div class="card-desc">View and update your bank profile and branch details.</div>
        <a class="card-link info" href="bank_info.php">Select</a>
      </div>
      <div class="dashboard-card">
        <div class="card-logo logo-apps">
          <svg width="38" height="38" viewBox="0 0 38 38" fill="none"><rect x="6" y="12" width="26" height="14" rx="4" fill="#f9c97a"/><rect x="14" y="20" width="10" height="4" rx="2" fill="#f9e0ae"/></svg>
        </div>
        <div class="card-title">View Loan Applications</div>
        <div class="card-desc">Review and process customer loan applications.</div>
        <a class="card-link apps" href="view_applications.php">Select</a>
      </div>
      <div class="dashboard-card">
        <div class="card-logo logo-reports">
          <svg width="38" height="38" viewBox="0 0 38 38" fill="none"><rect x="8" y="8" width="22" height="22" rx="4" fill="#7ed6b7"/><rect x="14" y="14" width="10" height="4" rx="2" fill="#c6e2d9"/><rect x="14" y="24" width="10" height="4" rx="2" fill="#c6e2d9"/></svg>
        </div>
        <div class="card-title">Reports</div>
        <div class="card-desc">Access analytics and generate financial reports.</div>
        <a class="card-link reports" href="reports.php">Select</a>
      </div>
      <div class="dashboard-card">
        <div class="card-logo logo-logout">
          <svg width="38" height="38" viewBox="0 0 38 38" fill="none"><rect x="10" y="10" width="18" height="18" rx="6" fill="#7eaef9"/><rect x="17" y="17" width="4" height="4" rx="2" fill="#b6d0f9"/></svg>
        </div>
        <div class="card-title">Logout</div>
        <div class="card-desc">Sign out of your account securely.</div>
        <a class="card-link logout" href="logout.php">Select</a>
      </div>
    </div>
  </div>
</body>
</html>
