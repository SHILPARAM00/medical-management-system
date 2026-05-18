<?php
session_start();
require_once "../../config/db_connect.php";

if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'pharmacist') {
    header("Location: ../../login.php");
    exit;
}

$name = $_SESSION['name'];
$currentPage = basename($_SERVER['PHP_SELF']);

$orders = $conn->query("
SELECT po.*, s.name AS supplier_name
FROM purchase_orders po
LEFT JOIN suppliers s ON po.supplier_id = s.supplier_id
ORDER BY po.order_id DESC
");
?>

<!DOCTYPE html>
<html>
<head>
<title>Purchase Orders</title>

<style>
body{margin:0;font-family:Arial;background:#f4f7fb;}
.container{display:flex;}
.sidebar{
    width:230px;background:white;height:100vh;padding:20px;
    box-shadow:2px 0 10px rgba(0,0,0,0.05);
}
.sidebar img{width:50px;}
.menu a{
    display:block;padding:12px;margin:5px 0;
    text-decoration:none;color:#444;border-radius:8px;
}
.menu a:hover{background:#e6f0fa;}
.active{background:#e6f0fa;}

.main{flex:1;padding:20px;}
.card{
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
}
th{
    background:#2b6cb0;
    color:white;
}
.btn{
    padding:8px 14px;
    border-radius:6px;
    text-decoration:none;
    color:white;
    font-size:13px;
}
.add{background:#38a169;}
</style>
</head>

<body>

<div class="container">

<div class="sidebar">
<img src="../../frontend/assets/images/logo.png.jpeg">
<h3>Pharmacist Panel</h3>

<div class="menu">
<a href="dashboard.php" >🏠 Dashboard</a>
<a href="medicines.php">💊 Medications</a>
<a href="prescriptions.php" >📄 Prescriptions</a>
<a href="inventory.php" >📦 Inventory</a>
<a href="purchase_orders.php" class="active">🛒 Purchase Orders</a>
<a href="suppliers.php">🚚 Suppliers</a>
<a href="reports.php">📊 Reports</a>
<a href="profile.php">👤 Profile</a>
<a href="../../logout.php">🚪 Logout</a>
</div>
</div>

<div class="main">

<h2>Purchase Orders</h2>
<h3><?php echo $name; ?></h3>

<a href="add_order.php" class="btn add">+ Add Order</a>

<div class="card">

<table>
<tr>
<th>ID</th>
<th>Supplier</th>
<th>Medicine</th>
<th>Quantity</th>
<th>Date</th>
<th>Status</th>
</tr>

<?php if($orders->num_rows > 0): ?>
<?php while($o = $orders->fetch_assoc()): ?>
<tr>
<td><?php echo $o['order_id']; ?></td>
<td><?php echo $o['supplier_name']; ?></td>
<td><?php echo $o['medicine_name']; ?></td>
<td><?php echo $o['quantity']; ?></td>
<td><?php echo $o['order_date']; ?></td>
<td><?php echo $o['status']; ?></td>
</tr>
<?php endwhile; ?>
<?php else: ?>
<tr>
<td colspan="6" style="text-align:center;">No orders found</td>
</tr>
<?php endif; ?>

</table>

</div>

</div>
</div>

</body>
</html>