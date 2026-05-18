<?php
session_start();
require_once "../../config/db_connect.php";

if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'patient') {
    header("Location: ../../login.php");
    exit;
}

$patient_id = $_SESSION['user_id'];

/* ================= FILTER ================= */

$search = $_GET['search'] ?? '';
$status = $_GET['status'] ?? '';

$query = "
    SELECT * FROM lab_reports
    WHERE patient_id = $patient_id
";

if($search){
    $query .= " AND report_name LIKE '%$search%'";
}

if($status){
    $query .= " AND status = '$status'";
}

$query .= " ORDER BY report_date DESC";

$reports = $conn->query($query);
?>

<!DOCTYPE html>
<html>
<head>
<title>Lab Reports</title>

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

/* MAIN */
.main{flex:1;padding:20px}

/* HEADER */
.header{
    display:flex;
    justify-content:space-between;
    align-items:center;
}

/* FILTER */
.filter{
    display:flex;
    gap:10px;
    margin-top:20px;
}

input,select{
    padding:8px;
    border-radius:6px;
    border:1px solid #ccc;
}

/* TABLE */
table{
    width:100%;
    border-collapse:collapse;
    margin-top:20px;
    background:white;
    border-radius:10px;
    overflow:hidden;
}

th,td{
    padding:12px;
    text-align:left;
    border-bottom:1px solid #eee;
}

th{
    background:#2b6cb0;
    color:white;
}

/* STATUS */
.status{
    padding:5px 10px;
    border-radius:10px;
    font-size:12px;
}

.completed{background:#d4edda}
.pending{background:#fff3cd}

</style>
</head>

<body>

<div class="container">

<!-- SIDEBAR -->
<div class="sidebar">
<img src="../../frontend/assets/images/logo.png.jpeg">
<h3>Patient Panel</h3>

<div class="menu">
<a href="dashboard.php">🏠 Dashboard</a>
<a href="appointments.php">📅 Appointments</a>
<a href="prescriptions.php">💊 Prescriptions</a>
<a href="lab_reports.php">🧪 Lab Reports</a>
<a href="bills.php">💰 Bills</a>
<a href="profile.php">👤 Profile</a>
<a href="../../logout.php">🚪 Logout</a>
</div>
</div>

<!-- MAIN -->
<div class="main">

<div class="header">
<h2>Lab Reports</h2>
</div>

<!-- FILTER -->
<form method="GET" class="filter">

<input type="text" name="search" placeholder="Search report"
value="<?php echo $search; ?>">

<select name="status">
<option value="">All Status</option>
<option value="completed">Completed</option>
<option value="pending">Pending</option>
</select>

<button type="submit">Filter</button>

</form>

<!-- TABLE -->
<table>

<tr>
<th>ID</th>
<th>Report Name</th>
<th>Date</th>
<th>Status</th>
</tr>

<?php while($r = $reports->fetch_assoc()): ?>

<tr>
<td><?php echo $r['report_id']; ?></td>
<td><?php echo $r['report_name']; ?></td>
<td><?php echo $r['report_date']; ?></td>

<td>
<span class="status <?php echo $r['status']; ?>">
<?php echo ucfirst($r['status']); ?>
</span>
</td>

</tr>

<?php endwhile; ?>

</table>

</div>
</div>

</body>
</html>