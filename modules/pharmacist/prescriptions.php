<?php
session_start();
require_once "../../config/db_connect.php";

if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'pharmacist') {
    header("Location: ../../login.php");
    exit;
}

$name = $_SESSION['name'];
$currentPage = basename($_SERVER['PHP_SELF']);

$prescriptions = $conn->query("
SELECT p.*, u.name as doctor_name
FROM prescription p
LEFT JOIN doctors d ON p.doctor_id = d.doctor_id
LEFT JOIN users u ON d.user_id = u.user_id
ORDER BY p.created_at DESC
");
?>

<!DOCTYPE html>
<html>
<head>
<title>Prescriptions</title>
<link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" rel="stylesheet">

<style>
body{margin:0;font-family:Arial;background:#f4f7fb;}
.container{display:flex}
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
.main{flex:1;padding:20px}
.card{
    background:white;
    padding:20px;
    border-radius:12px;
    margin-top:20px;
    box-shadow:0 5px 15px rgba(0,0,0,0.05);
}
.header{
    display:flex;
    justify-content:space-between;
    align-items:center;
}
.search{
    padding:10px;
    width:250px;
    border:1px solid #ccc;
    border-radius:8px;
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
.status{
    padding:5px 10px;
    border-radius:20px;
    font-size:12px;
    color:white;
}
.pending{background:#dd6b20;}
.sent{background:#3182ce;}
.completed{background:#38a169;}
.dispensed{background:#38a169;}
.btn{
    padding:6px 12px;
    background:#2b6cb0;
    color:white;
    text-decoration:none;
    border-radius:6px;
    font-size:12px;
}
.done{
    color:green;
    font-weight:bold;
}
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
<a href="prescriptions.php" class="active">📄 Prescriptions</a>
<a href="inventory.php">📦 Inventory</a>
<a href="purchase_orders.php">🛒 Purchase Orders</a>
<a href="suppliers.php">🚚 Suppliers</a>
<a href="reports.php">📊 Reports</a>
<a href="profile.php">👤 Profile</a>
<a href="../../logout.php">🚪 Logout</a>
</div>
</div>

<div class="main">

<div class="header">
<div>
<h2>📄 Prescriptions</h2>
<h3><?php echo $name; ?></h3>
</div>

<input type="text" class="search" id="search" placeholder="Search..." onkeyup="searchTable()">
</div>

<div class="card">

<table id="prescriptionTable">
<tr>
<th>ID</th>
<th>Patient ID</th>
<th>Medicine</th>
<th>Doctor</th>
<th>Dosage</th>
<th>Duration</th>
<th>Status</th>
<th>Action</th>
</tr>

<?php if($prescriptions->num_rows > 0): ?>
<?php while($p = $prescriptions->fetch_assoc()): ?>

<?php
$status = strtolower(trim($p['status'] ?? 'pending'));
if($status == '') $status = 'pending';
?>

<tr>
<td><?php echo $p['prescription_id']; ?></td>
<td><?php echo $p['patient_id']; ?></td>
<td><?php echo $p['medicine_name']; ?></td>
<td><?php echo $p['doctor_name'] ?: 'N/A'; ?></td>
<td><?php echo $p['dosage']; ?></td>
<td><?php echo $p['duration']; ?></td>

<td>
<span class="status <?php echo $status; ?>">
<?php echo ucfirst($status); ?>
</span>
</td>

<td>
<?php if($status == 'pending'){ ?>
<a href="process_prescription.php?id=<?php echo $p['prescription_id']; ?>" class="btn">Process</a>
<?php } else { ?>
<span class="done">✔ Done</span>
<?php } ?>
</td>

</tr>

<?php endwhile; ?>
<?php else: ?>

<tr>
<td colspan="8" style="text-align:center;padding:20px;">No prescriptions found</td>
</tr>

<?php endif; ?>

</table>

</div>
</div>
</div>

<script>
function searchTable(){
    let input = document.getElementById("search").value.toLowerCase();
    let rows = document.querySelectorAll("#prescriptionTable tr");

    rows.forEach((row,index)=>{
        if(index===0) return;
        let text = row.innerText.toLowerCase();
        row.style.display = text.includes(input) ? "" : "none";
    });
}
</script>

</body>
</html>