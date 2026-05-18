<?php
session_start();
require_once "../../config/db_connect.php";

if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'pharmacist') {
    header("Location: ../../login.php");
    exit;
}

$name = $_SESSION['name'];
$currentPage = basename($_SERVER['PHP_SELF']);

/* COUNTS */
$totalMed = $conn->query("SELECT COUNT(*) as total FROM medicines")->fetch_assoc()['total'] ?? 0;
$lowStock = $conn->query("SELECT COUNT(*) as total FROM medicines WHERE stock <= 10")->fetch_assoc()['total'] ?? 0;
$expiring = $conn->query("SELECT COUNT(*) as total FROM medicines WHERE expiry_date <= DATE_ADD(CURDATE(), INTERVAL 30 DAY)")->fetch_assoc()['total'] ?? 0;
$todayPres = $conn->query("SELECT COUNT(*) as total FROM prescription WHERE DATE(created_at)=CURDATE()")->fetch_assoc()['total'] ?? 0;

/* RECENT PRESCRIPTIONS */
$recent = $conn->query("
    SELECT p.*, u.name as doctor_name
    FROM prescription p
    LEFT JOIN doctors d ON p.doctor_id = d.doctor_id
    LEFT JOIN users u ON d.user_id = u.user_id
    ORDER BY p.created_at DESC
    LIMIT 5
");

/* LOW STOCK LIST */
$lowList = $conn->query("
    SELECT name, stock
    FROM medicines
    WHERE stock <= 10
    LIMIT 5
");

/* EXPIRY LIST */
$expList = $conn->query("
    SELECT name, expiry_date
    FROM medicines
    WHERE expiry_date <= DATE_ADD(CURDATE(), INTERVAL 30 DAY)
    LIMIT 5
");
?>

<!DOCTYPE html>
<html>
<head>
<title>Pharmacist Dashboard</title>
<link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" rel="stylesheet">
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

<style>
*{
    margin:0;
    padding:0;
    box-sizing:border-box;
}
body{
    font-family:Arial, sans-serif;
    background:#f4f7fb;
}
.container{
    display:flex;
}

/* SIDEBAR */
.sidebar{
    width:250px;
    min-height:100vh;
    background:white;
    padding:20px;
    box-shadow:2px 0 10px rgba(0,0,0,0.05);
}
.logo{
    display:flex;
    align-items:center;
    gap:10px;
    margin-bottom:25px;
}
.logo img{
    width:45px;
}
.logo h2{
    font-size:18px;
    color:#1d4ed8;
}
.menu a{
    display:block;
    padding:13px 15px;
    text-decoration:none;
    color:#444;
    border-radius:10px;
    margin-bottom:8px;
    font-size:15px;
}
.menu a:hover,
.active{
    background:#e8f1ff;
    color:#1d4ed8;
}

/* MAIN */
.main{
    flex:1;
    padding:25px;
}

/* HEADER */
.header{
    display:flex;
    justify-content:space-between;
    align-items:center;
    margin-bottom:20px;
}
.header h1{
    font-size:28px;
}
.profile{
    display:flex;
    align-items:center;
    gap:15px;
}
.profile i{
    font-size:18px;
    color:#1d4ed8;
}
.profile img{
    width:40px;
    height:40px;
    border-radius:50%;
}

/* STATS */
.cards{
    display:grid;
    grid-template-columns:repeat(4,1fr);
    gap:20px;
    margin-bottom:20px;
}
.card{
    background:white;
    padding:20px;
    border-radius:15px;
    box-shadow:0 4px 15px rgba(0,0,0,0.05);
}
.card h4{
    color:#666;
    font-size:15px;
}
.card h2{
    margin-top:10px;
    color:#111;
}

/* CONTENT GRID */
.grid{
    display:grid;
    grid-template-columns:1.5fr 1fr;
    gap:20px;
}
.box{
    background:white;
    border-radius:15px;
    padding:20px;
    box-shadow:0 4px 15px rgba(0,0,0,0.05);
}
.box h3{
    margin-bottom:15px;
    color:#1d4ed8;
}
.item{
    display:flex;
    justify-content:space-between;
    padding:10px 0;
    border-bottom:1px solid #eee;
}
.item:last-child{
    border-bottom:none;
}
.small{
    font-size:13px;
    color:#666;
}
</style>
</head>
<body>

<div class="container">

<!-- SIDEBAR -->
<div class="sidebar">

<div class="logo">
<img src="../../frontend/assets/images/logo.png.jpeg">
<h2>Medical System</h2>
</div>

<div class="menu">
<a href="dashboard.php" class="active">🏠 Dashboard</a>
<a href="medicines.php">💊 Medications</a>
<a href="prescriptions.php">📄 Prescriptions</a>
<a href="inventory.php">📦 Inventory</a>
<a href="purchase_orders.php">🛒 Purchase Orders</a>
<a href="suppliers.php">🚚 Suppliers</a>
<a href="reports.php">📊 Reports</a>
<a href="profile.php">👤 Profile</a>
<a href="../../logout.php">🚪 Logout</a>
</div>

</div>

<!-- MAIN -->
<div class="main">

<!-- HEADER -->
<div class="header">
<div>
<p style="color:#666;">Welcome back,</p>
<h1>Pharmacist <?php echo $name; ?></h1>
</div>

<div class="profile">
<i class="fa fa-bell"></i>
<i class="fa fa-user-circle"></i>
</div>
</div>

<!-- CARDS -->
<div class="cards">

<div class="card">
<h4>Total Medications</h4>
<h2><?php echo $totalMed; ?></h2>
</div>

<div class="card">
<h4>Low Stock Items</h4>
<h2><?php echo $lowStock; ?></h2>
</div>

<div class="card">
<h4>Expiring Soon</h4>
<h2><?php echo $expiring; ?></h2>
</div>

<div class="card">
<h4>Today's Prescriptions</h4>
<h2><?php echo $todayPres; ?></h2>
</div>

</div>

<!-- GRID -->
<div class="grid">

<!-- LEFT -->
<div>

<div class="box">
<h3>Inventory Overview</h3>
<div style="width:280px; margin:auto;">
    <canvas id="chart"></canvas>
</div>
</div>

<div class="box" style="margin-top:20px;">
<h3>Low Stock Alerts</h3>

<?php while($row = $lowList->fetch_assoc()): ?>
<div class="item">
<span><?php echo $row['name']; ?></span>
<span><?php echo $row['stock']; ?> left</span>
</div>
<?php endwhile; ?>

</div>

</div>

<!-- RIGHT -->
<div>

<div class="box">
<h3>Recent Prescriptions</h3>

<?php while($r = $recent->fetch_assoc()): ?>
<a href="process_prescription.php?id=<?php echo $r['prescription_id']; ?>" style="text-decoration:none;color:black;">
<div class="item">
<div>
<b><?php echo $r['medicine_name']; ?></b><br>
<span class="small"><?php echo $r['doctor_name'] ?? 'Doctor'; ?></span>
</div>
<div class="small">
<?php echo date("h:i A", strtotime($r['created_at'])); ?>
</div>
</div>
</a>
<?php endwhile; ?>

</div>

<div class="box" style="margin-top:20px;">
<h3>Expiring Soon</h3>

<?php while($e = $expList->fetch_assoc()): ?>
<div class="item">
<span><?php echo $e['name']; ?></span>
<span class="small"><?php echo $e['expiry_date']; ?></span>
</div>
<?php endwhile; ?>

</div>

</div>

</div>

</div>
</div>

<script>
new Chart(document.getElementById("chart"),{
    type:"doughnut",
    data:{
        labels:["Available","Low Stock","Expiring"],
        datasets:[{
            data:[
                <?php echo $totalMed; ?>,
                <?php echo $lowStock; ?>,
                <?php echo $expiring; ?>
            ]
        }]
    },
    options:{
        responsive:true
    }
});
</script>

</body>
</html>