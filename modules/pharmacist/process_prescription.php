<?php
session_start();
require_once "../../config/db_connect.php";

if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'pharmacist') {
    header("Location: ../../login.php");
    exit;
}

$name = $_SESSION['name'];
$currentPage = basename($_SERVER['PHP_SELF']);

// ✅ SAFETY CHECK
if(!isset($_GET['id'])){
    echo "Invalid Access";
    exit;
}

$id = $_GET['id'];

// FETCH DATA
$data = $conn->query("
SELECT p.*, u.name as doctor_name 
FROM prescription p
JOIN doctors d ON p.doctor_id = d.doctor_id
JOIN users u ON d.user_id = u.user_id
WHERE p.prescription_id = $id
")->fetch_assoc();

// SAFETY
if(!$data){
    echo "Invalid Prescription";
    exit;
}

// ✅ FETCH MULTIPLE MEDICINES
$items = $conn->query("
SELECT pi.*, m.name, m.price
FROM prescription_items pi
JOIN medicines m ON pi.medicine_id = m.medicine_id
WHERE pi.prescription_id = $id
");
?>

<!DOCTYPE html>
<html>
<head>
<title>Process Prescription</title>

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

/* CARD */
.card{
    background:white;
    padding:20px;
    border-radius:12px;
    max-width:600px;
    margin-top:20px;
}

.item{
    margin-bottom:10px;
}

.btn{
    padding:10px 20px;
    background:#38a169;
    color:white;
    border:none;
    border-radius:6px;
    text-decoration:none;
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
<a href="dashboard.php">🏠 Dashboard</a>
<a href="medicines.php">💊 Medicines</a>
<a href="prescriptions.php" class="active">📄 Prescriptions</a>
<a href="inventory.php">📦 Inventory</a>
<a href="reports.php">📊 Reports</a>
<a href="profile.php">👤 Profile</a>
<a href="../../logout.php">🚪 Logout</a>
</div>
</div>

<!-- MAIN -->
<div class="main">

<h2>Process Prescription</h2>
<h3><?php echo $name; ?></h3>

<div class="card">

<!-- ✅ MULTI MEDICINE SECTION -->
<h3>Medicines</h3>

<?php 
$total = 0;

if($items->num_rows > 0){

    while($i = $items->fetch_assoc()){ 
        $sub = $i['price'] * $i['quantity'];
        $total += $sub;
?>

<div class="item">
<b><?php echo $i['name']; ?></b><br>
Dosage: <?php echo $i['dosage']; ?><br>
Quantity: <?php echo $i['quantity']; ?><br>
Price: ₹<?php echo $i['price']; ?><br>
Subtotal: ₹<?php echo $sub; ?>
<hr>
</div>

<?php } ?>

<h3>Total: ₹<?php echo $total; ?></h3>

<?php } else { ?>

<p style="color:red;">No medicines found in this prescription</p>

<?php } ?>

<!-- EXISTING DETAILS (UNCHANGED) -->
<div class="item"><b>Doctor:</b> <?php echo $data['doctor_name']; ?></div>

<div class="item"><b>Status:</b> <?php echo $data['status']; ?></div>

<div class="item"><b>Notes:</b> <?php echo $data['notes']; ?></div>

<br>

<?php if($data['status'] == 'pending'){ ?>
<a href="../../backend/pharmacist/dispense_prescription.php?id=<?php echo $data['prescription_id']; ?>" class="btn">
💊 Dispense
</a>
<?php } else { ?>
<p style="color:green;"><b>Already Dispensed</b></p>
<?php } ?>

</div>

</div>
</div>

</body>
</html>