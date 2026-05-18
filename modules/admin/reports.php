<?php
session_start();
require_once "../../config/db_connect.php";

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../../login.php");
    exit;
}

$doctors = $conn->query("SELECT COUNT(*) AS total FROM doctors")->fetch_assoc()['total'];
$patients = $conn->query("SELECT COUNT(*) AS total FROM patients")->fetch_assoc()['total'];
$prescriptions = $conn->query("SELECT COUNT(*) AS total FROM prescriptions")->fetch_assoc()['total'];

$revenue = $conn->query("
SELECT SUM(total_amount) AS total 
FROM bills 
WHERE payment_status='paid'
")->fetch_assoc()['total'] ?? 0;
?>

<!DOCTYPE html>
<html>
<head>
<title>Reports</title>

<style>
body{
    margin:0;
    font-family:Segoe UI;
    background:#0a0f1c;
    color:white;
    display:flex;
}

/* SIDEBAR (SAME STYLE AS DASHBOARD) */
.sidebar{
    width:250px;
    height:100vh;
    position:fixed;
    background:#0f172a;
    padding:20px;
}

.sidebar h2{
    text-align:center;
    color:#38bdf8;
    margin-bottom:30px;
}

.menu{
    list-style:none;
    padding:0;
}

.menu li{
    padding:12px;
    margin:10px 0;
    background:#1e293b;
    border-radius:10px;
    cursor:pointer;
    transition:0.3s;
}

.menu li:hover{
    background:linear-gradient(90deg,#38bdf8,#6366f1);
    transform:translateX(5px);
}

/* MAIN */
.main{
    margin-left:270px;
    padding:30px;
    width:100%;
}

/* TITLE */
h2{
    text-align:center;
    color:#38bdf8;
    margin-bottom:30px;
}

/* CARDS */
.cards{
    display:grid;
    grid-template-columns:repeat(auto-fit,minmax(240px,1fr));
    gap:20px;
}

.card{
    background:#111827;
    padding:25px;
    border-radius:15px;
    text-align:center;
    box-shadow:0 10px 20px rgba(0,0,0,0.4);
    transition:0.3s;
}

.card:hover{
    transform:translateY(-8px);
}

.value{
    font-size:30px;
    font-weight:bold;
    color:#38bdf8;
}

.revenue{
    color:#22c55e;
}
</style>
</head>

<body>

<!-- SIDEBAR MENU -->
<div class="sidebar">
    <h2>Admin Panel</h2>
    <ul class="menu">
        <li onclick="location.href='dashboard.php'">Dashboard</li>
        <li onclick="location.href='doctors.php'">Doctors</li>
        <li onclick="location.href='patients.php'">Patients</li>
        <li onclick="location.href='prescriptions.php'">Prescriptions</li>
        <li>Reports</li>
        <li onclick="location.href='../../logout.php'">Logout</li>
    </ul>
</div>

<!-- MAIN CONTENT -->
<div class="main">

<h2>Hospital Reports</h2>

<div class="cards">

    <div class="card">
        <h3>Total Doctors</h3>
        <div class="value"><?= $doctors ?></div>
    </div>

    <div class="card">
        <h3>Total Patients</h3>
        <div class="value"><?= $patients ?></div>
    </div>

    <div class="card">
        <h3>Total Prescriptions</h3>
        <div class="value"><?= $prescriptions ?></div>
    </div>

    <div class="card">
        <h3>Total Revenue</h3>
        <div class="value revenue">₹<?= $revenue ?></div>
    </div>

</div>

</div>

</body>
</html>