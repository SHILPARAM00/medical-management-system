<?php
session_start();
require_once "../../config/db_connect.php";

if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'pharmacist') {
    header("Location: ../../login.php");
    exit;
}

$name = $_SESSION['name'];
$currentPage = basename($_SERVER['PHP_SELF']);

// FETCH DATA
$data = $conn->query("SELECT * FROM medicines ORDER BY expiry_date ASC");

// COUNTS
$total = $conn->query("SELECT COUNT(*) as t FROM medicines")->fetch_assoc()['t'];
$low = $conn->query("SELECT COUNT(*) as t FROM medicines WHERE stock < 10")->fetch_assoc()['t'];
$expiring = $conn->query("
    SELECT COUNT(*) as t 
    FROM medicines 
    WHERE expiry_date BETWEEN CURDATE() AND DATE_ADD(CURDATE(), INTERVAL 30 DAY)
")->fetch_assoc()['t'];
?>

<!DOCTYPE html>
<html>
<head>
<title>Inventory</title>

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
.sidebar img{width:50px}
.menu a{
    display:block;
    padding:12px;
    margin:5px 0;
    text-decoration:none;
    color:#444;
    border-radius:8px;
}
.menu a:hover{background:#e6f0fa}
.active{background:#e6f0fa}

/* MAIN */
.main{
    flex:1;
    padding:20px;
}

/* HEADER */
.header{
    display:flex;
    justify-content:space-between;
    align-items:center;
}

/* SEARCH */
.search{
    padding:10px;
    width:250px;
    border:1px solid #ccc;
    border-radius:8px;
}

/* CARDS */
.cards{
    display:grid;
    grid-template-columns:repeat(3,1fr);
    gap:15px;
    margin-top:20px;
}
.stat{
    background:white;
    padding:20px;
    border-radius:12px;
    box-shadow:0 5px 15px rgba(0,0,0,0.05);
}
.stat h4{
    margin:0;
    color:#666;
}
.stat h2{
    margin-top:10px;
    color:#2b6cb0;
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

/* STATUS */
.badge{
    padding:6px 10px;
    border-radius:20px;
    font-size:12px;
    color:white;
}
.low{background:#e53e3e;}
.expiring{background:#dd6b20;}
.ok{background:#38a169;}
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
<a href="inventory.php" class="active">📦 Inventory</a>
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
<h2>📦 Inventory Management</h2>
<h3><?php echo $name; ?></h3>
</div>

<input type="text" id="search" class="search" placeholder="Search medicine..." onkeyup="searchMedicine()">
</div>

<!-- CARDS -->
<div class="cards">
<div class="stat">
<h4>Total Medicines</h4>
<h2><?php echo $total; ?></h2>
</div>

<div class="stat">
<h4>Low Stock</h4>
<h2><?php echo $low; ?></h2>
</div>

<div class="stat">
<h4>Expiring Soon</h4>
<h2><?php echo $expiring; ?></h2>
</div>
</div>

<!-- TABLE -->
<div class="card">

<table id="inventoryTable">
<tr>
<th>ID</th>
<th>Name</th>
<th>Stock</th>
<th>Expiry</th>
<th>Status</th>
</tr>

<?php while($m = $data->fetch_assoc()){ 

    $status = "Available";
    $class = "ok";

    if($m['stock'] < 10){
        $status = "Low Stock";
        $class = "low";
    }

    if(strtotime($m['expiry_date']) <= strtotime("+30 days")){
        $status = "Expiring Soon";
        $class = "expiring";
    }
?>

<tr>
<td><?php echo $m['medicine_id']; ?></td>
<td><?php echo $m['name']; ?></td>
<td><?php echo $m['stock']; ?></td>
<td><?php echo $m['expiry_date']; ?></td>

<td>
<span class="badge <?php echo $class; ?>">
<?php echo $status; ?>
</span>
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
    let rows = document.querySelectorAll("#inventoryTable tr");

    rows.forEach((row,index)=>{
        if(index===0) return;

        let text = row.innerText.toLowerCase();
        row.style.display = text.includes(input) ? "" : "none";
    });
}
</script>

</body>
</html>