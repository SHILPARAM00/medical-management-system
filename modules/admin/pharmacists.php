<?php
session_start();
require_once "../../config/db_connect.php";

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../../login.php");
    exit;
}

$name = $_SESSION['name'] ?? "Admin";

$pharmacists = $conn->query("
    SELECT p.pharmacist_id, p.phone, u.user_id, u.name, u.email
    FROM pharmacists p
    JOIN users u ON p.user_id = u.user_id
    WHERE u.role = 'pharmacist'
    ORDER BY p.pharmacist_id ASC
");
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Pharmacists - Admin</title>

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

/* SEARCH */
.search{
    padding:10px;
    width:280px;
    border:1px solid #ccc;
    border-radius:8px;
    margin-bottom:20px;
    outline:none;
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
    text-align:left;
    border-bottom:1px solid #eee;
    font-size:14px;
}

th{
    background:#2b6cb0;
    color:white;
}

tr:hover{
    background:#f1f7ff;
}

/* BUTTON */
.btn{
    padding:6px 10px;
    background:#2b6cb0;
    color:white;
    text-decoration:none;
    border-radius:6px;
    font-size:12px;
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
        <a href="dashboard.php">🏠 Dashboard</a>
        <a href="doctors.php">👨‍⚕️ Doctors</a>
        <a href="patients.php">👥 Patients</a>
        <a href="pharmacists.php" class="active">💊 Pharmacists</a>
        <a href="appointments.php">📅 Appointments</a>
        <a href="profile.php">👤 Profile</a>
        <a href="../../logout.php">🚪 Logout</a>
    </div>

</div>

<!-- MAIN -->
<div class="main">

    <div class="header">
        <div>
            <h2>Pharmacists List</h2>
            <p style="color:#666;">Manage all registered pharmacists</p>
        </div>

        <div class="profile-box">
            <?php echo strtoupper(substr($name,0,1)); ?>
        </div>
    </div>

    <input type="text" class="search" id="search" placeholder="Search pharmacist..." onkeyup="filterTable()">

    <table id="pharmaTable">
        <tr>
            <th>ID</th>
            <th>Name</th>
            <th>Email</th>
            <th>Phone</th>
            <th>Action</th>
        </tr>

        <?php if($pharmacists->num_rows > 0): ?>
            <?php while($row = $pharmacists->fetch_assoc()): ?>
                <tr>
                    <td><?php echo $row['pharmacist_id']; ?></td>
                    <td><?php echo $row['name']; ?></td>
                    <td><?php echo $row['email']; ?></td>
                    <td><?php echo $row['phone']; ?></td>
                    <td>
                        <a class="btn" href="view_pharmacist.php?id=<?php echo $row['pharmacist_id']; ?>">
                            View
                        </a>
                    </td>
                </tr>
            <?php endwhile; ?>
        <?php else: ?>
            <tr>
                <td colspan="5" style="text-align:center;padding:20px;">
                    No pharmacists found
                </td>
            </tr>
        <?php endif; ?>
    </table>

</div>
</div>

<script>
function filterTable(){
    let input = document.getElementById('search').value.toLowerCase();
    let rows = document.querySelectorAll('#pharmaTable tr');

    rows.forEach((row,index)=>{
        if(index === 0) return;

        row.style.display =
        row.innerText.toLowerCase().includes(input) ? '' : 'none';
    });
}
</script>

</body>
</html>