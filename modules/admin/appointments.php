<?php
session_start();
require_once "../../config/db_connect.php";

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../../login.php");
    exit;
}

$name = $_SESSION['name'] ?? "Admin";

/* ACTIONS */
if (isset($_GET['pay_id'])) {
    $id = (int)$_GET['pay_id'];
    $conn->query("UPDATE bills SET payment_status='paid' WHERE appointment_id=$id");
    header("Location: appointments.php");
    exit;
}

if (isset($_GET['pres_id'])) {
    $id = (int)$_GET['pres_id'];

    $getPatient = $conn->query("SELECT patient_id FROM appointment WHERE appointment_id = $id");
    if ($getPatient && $getPatient->num_rows > 0) {
        $patient = $getPatient->fetch_assoc();
        $patient_id = $patient['patient_id'];

        $check = $conn->query("SELECT prescription_id FROM prescription WHERE patient_id = $patient_id");
        if ($check && $check->num_rows > 0) {
            $conn->query("UPDATE prescription SET status='sent' WHERE patient_id = $patient_id");
        }
    }

    header("Location: appointments.php");
    exit;
}

/* FILTER */
$filter_status = $_GET['status'] ?? '';
$filter_payment = $_GET['payment'] ?? '';

$where = " WHERE 1=1 ";

if ($filter_status != '') {
    $where .= " AND a.status='" . $conn->real_escape_string($filter_status) . "' ";
}

if ($filter_payment != '') {
    $where .= " AND COALESCE(b.payment_status,'unpaid')='" . $conn->real_escape_string($filter_payment) . "' ";
}

/* FETCH */
$sql = "
SELECT 
    a.appointment_id,
    a.patient_id,
    a.appointment_date,
    a.appointment_time,
    a.location,
    a.status AS appointment_status,
    pu.name AS patient_name,
    du.name AS doctor_name,
    d.specialization,
    b.total_amount,
    b.payment_status,
    p.status AS prescription_status
FROM appointment a
LEFT JOIN users pu ON a.patient_id = pu.user_id
LEFT JOIN doctors d ON a.doctor_id = d.doctor_id
LEFT JOIN users du ON d.user_id = du.user_id
LEFT JOIN bills b ON a.appointment_id = b.appointment_id
LEFT JOIN prescription p ON p.patient_id = a.patient_id
$where
ORDER BY a.appointment_date DESC
";

$result = $conn->query($sql);
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Appointments - Admin</title>

<style>
*{
    margin:0;
    padding:0;
    box-sizing:border-box;
}

body{
    font-family:Arial;
    background:#f4f7fb;
}

.container{
    display:flex;
}

/* SIDEBAR */
.sidebar{
    width:260px;
    background:#fff;
    min-height:100vh;
    padding:20px;
    box-shadow:2px 0 10px rgba(0,0,0,0.05);
}

.logo-box{
    display:flex;
    align-items:center;
    gap:10px;
    margin-bottom:25px;
    padding-bottom:15px;
    border-bottom:1px solid #eee;
}

.logo-box img{
    width:45px;
    height:45px;
    border-radius:10px;
    object-fit:cover;
}

.logo-text{
    font-size:16px;
    font-weight:bold;
    color:#2b6cb0;
    line-height:1.3;
}

.menu a{
    display:block;
    padding:12px;
    margin-bottom:8px;
    text-decoration:none;
    color:#333;
    border-radius:8px;
}

.menu a:hover,
.menu a.active{
    background:#e6f0fa;
    color:#2b6cb0;
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

.profile-box{
    width:40px;
    height:40px;
    background:#2b6cb0;
    color:white;
    border-radius:50%;
    display:flex;
    justify-content:center;
    align-items:center;
    font-weight:bold;
}

/* FILTER */
.filter-box{
    background:white;
    padding:15px;
    border-radius:12px;
    box-shadow:0 2px 10px rgba(0,0,0,0.05);
    margin-bottom:20px;
}

select, input{
    padding:10px;
    border:1px solid #ccc;
    border-radius:8px;
    margin-right:10px;
}

/* TABLE */
table{
    width:100%;
    border-collapse:collapse;
    background:#fff;
    border-radius:12px;
    overflow:hidden;
    box-shadow:0 2px 10px rgba(0,0,0,0.05);
}

th, td{
    padding:12px;
    border-bottom:1px solid #eee;
    font-size:13px;
    text-align:left;
}

th{
    background:#2b6cb0;
    color:white;
}

tr:hover{
    background:#f9fcff;
}

/* BADGE */
.badge{
    padding:5px 10px;
    border-radius:20px;
    font-size:12px;
    background:#edf2f7;
}

/* BUTTON */
.btn{
    padding:6px 10px;
    text-decoration:none;
    border-radius:6px;
    font-size:12px;
    color:white;
    display:inline-block;
    margin-top:4px;
}

.pay{ background:#38a169; }
.send{ background:#3182ce; }
.view{ background:#718096; }
</style>
</head>

<body>

<div class="container">

<!-- SIDEBAR -->
<div class="sidebar">

    <div class="logo-box">
        <img src="../../frontend/assets/images/logo.png.jpeg">
        <div class="logo-text">
            Medical Management<br>System
        </div>
    </div>

    <div class="menu">
        <a href="dashboard.php">🏠 Dashboard</a>
        <a href="doctors.php">👨‍⚕️ Doctors</a>
        <a href="patients.php">👥 Patients</a>
        <a href="pharmacists.php">💊 Pharmacists</a>
        <a href="appointments.php" class="active">📅 Appointments</a>
        <a href="../../logout.php">🚪 Logout</a>
    </div>

</div>

<!-- MAIN -->
<div class="main">

    <div class="header">
        <div>
            <h2>Appointments Management</h2>
            <p style="color:#666;">View and manage all hospital appointments</p>
        </div>

        <div class="profile-box">
            <?php echo strtoupper(substr($name,0,1)); ?>
        </div>
    </div>

    <!-- FILTER -->
    <div class="filter-box">
        <form method="GET">
            <select name="status">
                <option value="">All Status</option>
                <option value="pending">Pending</option>
                <option value="completed">Completed</option>
                <option value="cancelled">Cancelled</option>
            </select>

            <select name="payment">
                <option value="">All Payments</option>
                <option value="paid">Paid</option>
                <option value="unpaid">Unpaid</option>
            </select>

            <input type="text" id="searchInput" placeholder="Search patient..." onkeyup="searchTable()">

            <button type="submit" class="btn pay">Apply</button>
        </form>
    </div>

    <!-- TABLE -->
    <table id="appointmentTable">
        <tr>
            <th>ID</th>
            <th>Patient</th>
            <th>Doctor</th>
            <th>Date</th>
            <th>Time</th>
            <th>Status</th>
            <th>Bill</th>
            <th>Prescription</th>
            <th>Actions</th>
        </tr>

        <?php if($result && $result->num_rows > 0): ?>
            <?php while($row = $result->fetch_assoc()): ?>
                <tr>
                    <td><?= $row['appointment_id'] ?></td>
                    <td><?= $row['patient_name'] ?: 'N/A' ?></td>
                    <td><?= $row['doctor_name'] ?: 'N/A' ?></td>
                    <td><?= $row['appointment_date'] ?></td>
                    <td><?= $row['appointment_time'] ?></td>

                    <td>
                        <span class="badge"><?= $row['appointment_status'] ?></span>
                    </td>

                    <td>
                        ₹<?= $row['total_amount'] ?? 0 ?><br>
                        <span class="badge"><?= $row['payment_status'] ?? 'unpaid' ?></span>

                        <?php if(($row['payment_status'] ?? 'unpaid') != 'paid'): ?>
                            <br><a class="btn pay" href="?pay_id=<?= $row['appointment_id'] ?>">Mark Paid</a>
                        <?php endif; ?>
                    </td>

                    <td>
                        <span class="badge"><?= $row['prescription_status'] ?? 'pending' ?></span>

                        <?php if(($row['prescription_status'] ?? '') != 'sent'): ?>
                            <br><a class="btn send" href="?pres_id=<?= $row['appointment_id'] ?>">Send</a>
                        <?php endif; ?>
                    </td>

                    <td>
                        <a class="btn view" href="view_appointment.php?id=<?= $row['appointment_id'] ?>">View</a>
                    </td>
                </tr>
            <?php endwhile; ?>
        <?php else: ?>
            <tr>
                <td colspan="9" style="text-align:center;padding:20px;">No appointments found</td>
            </tr>
        <?php endif; ?>

    </table>

</div>
</div>

<script>
function searchTable() {
    let input = document.getElementById("searchInput").value.toLowerCase();
    let rows = document.querySelectorAll("#appointmentTable tr");

    rows.forEach((row, index) => {
        if(index === 0) return;
        row.style.display = row.innerText.toLowerCase().includes(input) ? "" : "none";
    });
}
</script>

</body>
</html>