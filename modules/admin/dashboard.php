<?php
session_start();
require_once "../../config/db_connect.php";

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../../login.php");
    exit;
}

$name = $_SESSION['name'] ?? "Admin";

/* COUNTS */
$totalUsers = $conn->query("SELECT COUNT(*) as total FROM users")->fetch_assoc()['total'] ?? 0;
$totalDoctors = $conn->query("SELECT COUNT(*) as total FROM doctors")->fetch_assoc()['total'] ?? 0;
$totalPatients = $conn->query("SELECT COUNT(*) as total FROM patients")->fetch_assoc()['total'] ?? 0;
$totalAppointments = $conn->query("SELECT COUNT(*) as total FROM appointment")->fetch_assoc()['total'] ?? 0;

$others = max(0, $totalUsers - ($totalDoctors + $totalPatients));

/* CHART DATA */
$days = ["Mon","Tue","Wed","Thu","Fri","Sat","Sun"];
$activityData = array_fill(0, 7, 0);

$chartQuery = "
    SELECT DATE(appointment_date) as date, COUNT(*) as total
    FROM appointment
    WHERE appointment_date >= DATE_SUB(CURDATE(), INTERVAL 6 DAY)
    GROUP BY DATE(appointment_date)
";

$result = $conn->query($chartQuery);

if ($result) {
    while ($row = $result->fetch_assoc()) {
        $dayIndex = date('N', strtotime($row['date'])) - 1;
        if (isset($activityData[$dayIndex])) {
            $activityData[$dayIndex] = (int)$row['total'];
        }
    }
}
?>

<!DOCTYPE html>
<html>
<head>
<title>Admin Dashboard</title>
<meta charset="UTF-8">
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

<style>
*{
    margin:0;
    padding:0;
    box-sizing:border-box;
}

body{
    font-family:Arial,sans-serif;
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
    transition:0.3s;
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
}

.profile{
    display:flex;
    align-items:center;
    gap:15px;
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

/* CARDS */
.cards{
    display:grid;
    grid-template-columns:repeat(4,1fr);
    gap:20px;
    margin-top:25px;
}

.card{
    background:white;
    padding:20px;
    border-radius:14px;
    box-shadow:0 5px 15px rgba(0,0,0,0.05);
    transition:0.3s;
}

.card:hover{
    transform:translateY(-5px);
}

.card h4{
    color:#666;
    margin-bottom:10px;
}

.card h2{
    color:#2b6cb0;
}

/* GRID */
.grid{
    display:grid;
    grid-template-columns:2fr 1fr;
    gap:20px;
    margin-top:25px;
}

.box{
    background:white;
    padding:20px;
    border-radius:14px;
    box-shadow:0 5px 15px rgba(0,0,0,0.05);
}

.box h3{
    margin-bottom:15px;
    color:#222;
}

.status-item{
    display:flex;
    justify-content:space-between;
    padding:12px 0;
    border-bottom:1px solid #eee;
}

.ok{
    color:green;
    font-weight:bold;
}

canvas{
    max-width:100%;
}

/* QUICK LINKS */
.quick-links{
    display:grid;
    grid-template-columns:repeat(2,1fr);
    gap:15px;
    margin-top:20px;
}

.quick-links a{
    text-decoration:none;
    background:#f8fbff;
    padding:15px;
    border-radius:10px;
    text-align:center;
    color:#2b6cb0;
    font-weight:bold;
    transition:0.3s;
}

.quick-links a:hover{
    background:#e6f0fa;
}
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
        <a href="dashboard.php" class="active">🏠 Dashboard</a>
        <a href="doctors.php">👨‍⚕️ Doctors</a>
        <a href="patients.php">👥 Patients</a>
        <a href="pharmacists.php">💊 Pharmacists</a>
        <a href="appointments.php">📅 Appointments</a>
        <a href="profile.php">👤 Profile</a>
        <a href="../../logout.php">🚪 Logout</a>
    </div>

</div>

<!-- MAIN -->
<div class="main">

<div class="header">
    <div>
        <h2>Welcome Back 👋</h2>
        <p>Administrator Panel</p>
    </div>

    <div class="profile">
        <div class="profile-box">
            <?php echo strtoupper(substr($name,0,1)); ?>
        </div>
    </div>
</div>

<!-- STATS -->
<div class="cards">

    <div class="card">
        <h4>Total Users</h4>
        <h2><?php echo $totalUsers; ?></h2>
    </div>

    <div class="card">
        <h4>Doctors</h4>
        <h2><?php echo $totalDoctors; ?></h2>
    </div>

    <div class="card">
        <h4>Patients</h4>
        <h2><?php echo $totalPatients; ?></h2>
    </div>

    <div class="card">
        <h4>Appointments</h4>
        <h2><?php echo $totalAppointments; ?></h2>
    </div>

</div>

<!-- GRID -->
<div class="grid">

    <!-- LEFT -->
    <div>

        <div class="box">
            <h3>Appointments Overview (Last 7 Days)</h3>
            <canvas id="lineChart"></canvas>
        </div>

        <div class="box" style="margin-top:20px;">
            <h3>Quick Management</h3>

            <div class="quick-links">
                <a href="doctors.php">Manage Doctors</a>
                <a href="patients.php">Manage Patients</a>
                <a href="pharmacists.php">Manage Pharmacists</a>
                <a href="appointments.php">Appointments</a>
            </div>
        </div>

    </div>

    <!-- RIGHT -->
    <div>

        <div class="box">
            <h3>System Status</h3>

            <div class="status-item">
                <span>Database</span>
                <span class="ok">Operational</span>
            </div>

            <div class="status-item">
                <span>Server</span>
                <span class="ok">Operational</span>
            </div>

            <div class="status-item">
                <span>Backup</span>
                <span class="ok">Success</span>
            </div>

            <div class="status-item">
                <span>Email</span>
                <span class="ok">Working</span>
            </div>
        </div>

        <div class="box" style="margin-top:20px;">
            <h3>User Distribution</h3>
            <canvas id="donutChart"></canvas>
        </div>

    </div>

</div>

</div>
</div>

<script>
new Chart(document.getElementById("lineChart"), {
    type: 'line',
    data: {
        labels: <?php echo json_encode($days); ?>,
        datasets: [{
            label: 'Appointments',
            data: <?php echo json_encode($activityData); ?>,
            borderColor: '#2b6cb0',
            backgroundColor: 'rgba(43,108,176,0.15)',
            fill: true,
            tension: 0.4
        }]
    }
});

new Chart(document.getElementById("donutChart"), {
    type: 'doughnut',
    data: {
        labels: ['Doctors','Patients','Others'],
        datasets: [{
            data: [
                <?php echo $totalDoctors; ?>,
                <?php echo $totalPatients; ?>,
                <?php echo $others; ?>
            ],
            backgroundColor: [
                '#2b6cb0',
                '#38a169',
                '#ed8936'
            ]
        }]
    }
});
</script>

</body>
</html>