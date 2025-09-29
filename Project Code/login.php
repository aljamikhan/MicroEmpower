<?php
require "dbconnect.php";

if (!isset($_POST['email'], $_POST['password'])) die("Invalid request");

$email = $mysqli->real_escape_string(trim($_POST['email']));
$pass  = $_POST['password'];

$res = $mysqli->query("SELECT ID, Password, Role FROM Users WHERE Email='$email' LIMIT 1");
if ($res && $res->num_rows === 1) {
  $row = $res->fetch_assoc();
  if (password_verify($pass, $row['Password'])) {
    $_SESSION['user_id'] = (int)$row['ID'];
    $_SESSION['role'] = $row['Role'];
    if ($row['Role'] === 'customer') header("Location: customer_dashboard.php");
    else if ($row['Role'] === 'bank') header("Location: bank_dashboard.php");
    else echo "Unknown role.";
    $res->free();
    exit;
  }
  $res->free();
}
echo "Invalid credentials. <a href='login.html'>Try again</a>";
