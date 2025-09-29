<?php
require "dbconnect.php";
$_SESSION = [];
session_destroy();
header("Location: login.html");
exit;
