<?php
session_start();
require_once "../../config/db_connect.php";

if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'pharmacist') {
    header("Location: ../../login.php");
    exit;
}

$name = $_SESSION['name'];
$currentPage = basename($_SERVER['PHP_SELF']);

$medicines = $conn->query("SELECT * FROM medicines ORDER BY created_at DESC");
?>

<!DOCTYPE html>
<html>
<head>
<title>Medicines</title>
<link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" rel="stylesheet">

<style>
body{margin:0;font-family:Arial;background:#f4f7fb;}
.container{display:flex}

/* SIDEBAR */
.sidebar{
    width:230px;background:white;height:100vh;padding:20px;
    box-shadow:2px 0 10px rgba(0,0,0,0.05);
}
.sidebar img{width:50px}

.menu a{
    display:block;padding:12px;margin:5px 0;
    text-decoration:none;color:#444;border-radius:8px;
}
.menu a:hover{background:#e6f0fa}
.active{background:#e6f0fa}

/* MAIN */
.main{flex:1;padding:20px}

.header{
    display:flex;
    justify-content:space-between;
    align-items:center;
    flex-wrap:wrap;
    gap:10px;
}

.search{
    padding:10px;
    border:1px solid #ccc;
    border-radius:8px;
    width:260px;
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

/* BUTTONS */
.btn{
    padding:6px 12px;
    border-radius:6px;
    text-decoration:none;
    color:white;
    font-size:13px;
    margin-right:5px;
}

.edit{background:#2b6cb0}
.delete{background:#e53e3e}
.add{background:#38a169;padding:10px 15px;border-radius:8px}

/* BADGES */
.badge{
    padding:5px 10px;
    border-radius:20px;
    font-size:12px;
    color:white;
}

.low{background:#e53e3e;}
.normal{background:#38a169;}
.expire{background:#dd6b20;}
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
<a href="medicines.php" class="active">💊 Medications</a>
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

<div class="header">
<div>
<h2>💊 Medicines Management</h2>
<p>Welcome, <?php echo $name; ?></p>
</div>

<div>
<input type="text" id="search" class="search" placeholder="Search medicine..." onkeyup="searchMedicine()">
<a href="add_medicine.php" class="btn add">+ Add Medicine</a>
</div>
</div>

<div class="card">

<table id="medicineTable">
<tr>
<th>Name</th>
<th>Price</th>
<th>Stock</th>
<th>Expiry</th>
<th>Status</th>
<th>Action</th>
</tr>

<?php while($m = $medicines->fetch_assoc()){ 

$status = "normal";
$statusText = "Available";

if($m['stock'] < 10){
    $status = "low";
    $statusText = "Low Stock";
}

if(strtotime($m['expiry_date']) <= strtotime("+30 days")){
    $status = "expire";
    $statusText = "Expiring";
}
?>

<tr>

<td><?php echo $m['name']; ?></td>
<td>₹<?php echo $m['price']; ?></td>
<td><?php echo $m['stock']; ?></td>
<td><?php echo $m['expiry_date']; ?></td>

<td>
<span class="badge <?php echo $status; ?>">
<?php echo $statusText; ?>
</span>
</td>

<td>
<a href="edit_medicine.php?id=<?php echo $m['medicine_id']; ?>" class="btn edit">Edit</a>

<a href="../../backend/medicines/delete_medicine.php?id=<?php echo $m['medicine_id']; ?>" 
class="btn delete"
onclick="return confirm('Delete this medicine?')">
Delete
</a>
</td>

</tr>

<?php } ?>

</table>

</div>

</div>
</div>

<script>
function searchMedicine(){
    let input = document.getElementById("search").value.toLowerCase();
    let rows = document.querySelectorAll("#medicineTable tr");

    rows.forEach((row,index)=>{
        if(index===0) return;

        let text = row.innerText.toLowerCase();
        row.style.display = text.includes(input) ? "" : "none";
    });
}
</script>

</body>
</html>