<?php
session_start();
require_once "../../config/db_connect.php";

if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'pharmacist') {
    header("Location: ../../login.php");
    exit;
}

$name = $_SESSION['name'];
$currentPage = basename($_SERVER['PHP_SELF']);

/* FETCH SUPPLIERS */
$suppliers = $conn->query("SELECT * FROM suppliers ORDER BY supplier_id DESC");
?>

<!DOCTYPE html>
<html>
<head>
<title>Suppliers</title>

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

/* CARD */
.card{
    background:white;
    padding:20px;
    border-radius:12px;
    margin-top:20px;
    box-shadow:0 5px 15px rgba(0,0,0,0.05);
}

/* TABLE */
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

/* BUTTON */
.btn{
    padding:8px 14px;
    border:none;
    border-radius:6px;
    text-decoration:none;
    color:white;
    font-size:13px;
}
.add{
    background:#38a169;
}
.edit{
    background:#2b6cb0;
}
.delete{
    background:#e53e3e;
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
<a href="purchase_orders.php">🛒 Purchase Orders</a>
<a href="suppliers.php"  class="active">🚚 Suppliers</a>
<a href="reports.php">📊 Reports</a>
<a href="profile.php" >👤 Profile</a>
<a href="../../logout.php">🚪 Logout</a>
</div>
</div>

<!-- MAIN -->
<div class="main">

<div class="header">
<div>
<h2>🚚 Suppliers</h2>
<h3><?php echo $name; ?></h3>
</div>

<a href="add_supplier.php" class="btn add">+ Add Supplier</a>
</div>

<div class="card">

<table>
<tr>
<th>ID</th>
<th>Supplier Name</th>
<th>Phone</th>
<th>Address</th>
<th>Action</th>
</tr>

<?php if($suppliers && $suppliers->num_rows > 0): ?>
<?php while($s = $suppliers->fetch_assoc()): ?>

<tr>
<td><?php echo $s['supplier_id']; ?></td>
<td><?php echo $s['name']; ?></td>
<td><?php echo $s['phone']; ?></td>
<td><?php echo $s['address']; ?></td>
<td>
<a href="edit_supplier.php?id=<?php echo $s['supplier_id']; ?>" class="btn edit">Edit</a>
<a href="../../backend/pharmacist/delete_supplier.php?id=<?php echo $s['supplier_id']; ?>" 
class="btn delete"
onclick="return confirm('Delete supplier?')">Delete</a>
</td>
</tr>

<?php endwhile; ?>
<?php else: ?>

<tr>
<td colspan="5" style="text-align:center;">No suppliers found</td>
</tr>

<?php endif; ?>

</table>

</div>

</div>
</div>

</body>
</html>