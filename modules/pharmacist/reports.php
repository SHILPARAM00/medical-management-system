<?php
session_start();
require_once "../../config/db_connect.php";

if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'pharmacist') {
    header("Location: ../../login.php");
    exit;
}

$name = $_SESSION['name'];
$currentPage = basename($_SERVER['PHP_SELF']);

/* ===== COUNTS ===== */
$total = $conn->query("SELECT COUNT(*) as t FROM medicines")->fetch_assoc()['t'];

$low = $conn->query("SELECT COUNT(*) as t FROM medicines WHERE stock < 10")->fetch_assoc()['t'];

$exp = $conn->query("
SELECT COUNT(*) as t 
FROM medicines 
WHERE expiry_date BETWEEN CURDATE() AND DATE_ADD(CURDATE(), INTERVAL 30 DAY)
")->fetch_assoc()['t'];

$dispensed = $conn->query("
SELECT COUNT(*) as t 
FROM prescription 
WHERE status='dispensed'
")->fetch_assoc()['t'];

/* ===== CHART DATA ===== */
$labels = [];
$data = [];

for($i=6; $i>=0; $i--){
    $date = date("Y-m-d", strtotime("-$i days"));
    $day = date("D", strtotime($date));

    $count = $conn->query("
        SELECT COUNT(*) as t 
        FROM prescription 
        WHERE DATE(created_at) = '$date'
    ")->fetch_assoc()['t'];

    $labels[] = $day;
    $data[] = $count;
}
?>

<!DOCTYPE html>
<html>
<head>
<title>Reports</title>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

<style>
body{
    margin:0;
    font-family:Arial;
    background:#f4f7fb;
}
.container{
    display:flex;
}

/* SIDEBAR */
.sidebar{
    width:230px;
    background:white;
    height:100vh;
    padding:20px;
    box-shadow:2px 0 10px rgba(0,0,0,0.05);
}
.sidebar img{
    width:50px;
}
.menu a{
    display:block;
    padding:12px;
    margin:5px 0;
    text-decoration:none;
    color:#444;
    border-radius:8px;
}
.menu a:hover{
    background:#e6f0fa;
}
.active{
    background:#e6f0fa;
}

/* MAIN */
.main{
    flex:1;
    padding:20px;
}

.header{
    display:flex;
    justify-content:space-between;
    align-items:center;
}

/* CARDS */
.cards{
    display:grid;
    grid-template-columns:repeat(4,1fr);
    gap:15px;
    margin-top:20px;
}
.card{
    background:white;
    padding:20px;
    border-radius:12px;
    box-shadow:0 5px 15px rgba(0,0,0,0.05);
}
.card h4{
    margin:0;
    color:#555;
}
.card h2{
    margin-top:10px;
    color:#2b6cb0;
}

/* CHART */
.chart-box{
    background:white;
    padding:20px;
    border-radius:12px;
    margin-top:20px;
    box-shadow:0 5px 15px rgba(0,0,0,0.05);
    width:70%;
}

/* TABLE */
.table-box{
    background:white;
    padding:20px;
    border-radius:12px;
    margin-top:20px;
    box-shadow:0 5px 15px rgba(0,0,0,0.05);
}

table{
    width:100%;
    border-collapse:collapse;
}
th,td{
    padding:12px;
    border-bottom:1px solid #eee;
    text-align:left;
}
th{
    background:#2b6cb0;
    color:white;
}
</style>
</head>

<body>

<div class="container">

<!-- SIDEBAR -->
<div class="sidebar">
<img src="../../frontend/assets/images/logo.png.jpeg">

<h3>Pharmacist Panel</h3>

<div class="menu">
<a href="dashboard.php" >🏠 Dashboard</a>
<a href="medicines.php">💊 Medications</a>
<a href="prescriptions.php" >📄 Prescriptions</a>
<a href="inventory.php" >📦 Inventory</a>
<a href="purchase_orders.php" >🛒 Purchase Orders</a>
<a href="suppliers.php">🚚 Suppliers</a>
<a href="reports.php" class="active">📊 Reports</a>
<a href="profile.php">👤 Profile</a>
<a href="../../logout.php">🚪 Logout</a>
</div>
</div>

<!-- MAIN -->
<div class="main">

<div class="header">
<div>
<h2>📊 Reports & Analytics</h2>
<h3><?php echo $name; ?></h3>
</div>
</div>

<!-- STATS -->
<div class="cards">

<div class="card">
<h4>Total Medicines</h4>
<h2><?php echo $total; ?></h2>
</div>

<div class="card">
<h4>Low Stock</h4>
<h2><?php echo $low; ?></h2>
</div>

<div class="card">
<h4>Expiring Soon</h4>
<h2><?php echo $exp; ?></h2>
</div>

<div class="card">
<h4>Dispensed</h4>
<h2><?php echo $dispensed; ?></h2>
</div>

</div>

<!-- CHART -->
<div class="chart-box">
<h3>Last 7 Days Prescriptions</h3>
<canvas id="chart" style="max-height:220px;"></canvas>
</div>

<!-- RECENT REPORT TABLE -->
<div class="table-box">
<h3>Quick Summary</h3>

<table>
<tr>
<th>Category</th>
<th>Count</th>
</tr>

<tr>
<td>Total Medicines</td>
<td><?php echo $total; ?></td>
</tr>

<tr>
<td>Low Stock Medicines</td>
<td><?php echo $low; ?></td>
</tr>

<tr>
<td>Expiring Medicines</td>
<td><?php echo $exp; ?></td>
</tr>

<tr>
<td>Dispensed Prescriptions</td>
<td><?php echo $dispensed; ?></td>
</tr>

</table>
</div>

</div>
</div>

<script>
new Chart(document.getElementById("chart"), {
    type: "bar",
    data: {
        labels: <?php echo json_encode($labels); ?>,
        datasets: [{
            label: "Prescriptions",
            data: <?php echo json_encode($data); ?>,
            borderWidth: 1
        }]
    },
    options: {
        responsive:true,
        maintainAspectRatio:false
    }
});
</script>

</body>
</html>