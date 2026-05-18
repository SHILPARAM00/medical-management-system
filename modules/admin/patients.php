<?php
session_start();
require_once "../../config/db_connect.php";

/* SECURITY */
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../../login.php");
    exit;
}

$name = $_SESSION['name'] ?? "Admin";

/* FETCH PATIENTS WITH USER DETAILS */
$sql = "
    SELECT 
        p.patient_id,
        u.name,
        u.email,
        p.age,
        p.gender,
        p.phone,
        p.address,
        p.emergency_phone,
        p.profile_image
    FROM patients p
    JOIN users u ON p.user_id = u.user_id
";

$result = $conn->query($sql);
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Patients - Admin</title>

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

/* IMAGE */
.img{
    width:40px;
    height:40px;
    border-radius:50%;
    object-fit:cover;
}

/* BUTTON */
.btn{
    padding:6px 10px;
    border:none;
    border-radius:6px;
    cursor:pointer;
    font-size:12px;
}

.edit{
    background:#38a169;
    color:white;
}

.delete{
    background:#e53e3e;
    color:white;
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
        <a href="patients.php" class="active">👥 Patients</a>
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
            <h2>Patients List</h2>
            <p style="color:#666;">Manage all registered patients</p>
        </div>

        <div class="profile-box">
            <?php echo strtoupper(substr($name,0,1)); ?>
        </div>
    </div>

    <!-- TABLE -->
    <table>
        <tr>
            <th>ID</th>
            <th>Photo</th>
            <th>Name</th>
            <th>Email</th>
            <th>Age</th>
            <th>Gender</th>
            <th>Phone</th>
            <th>Emergency</th>
            <th>Address</th>
            <th>Actions</th>
        </tr>

        <?php if ($result && $result->num_rows > 0): ?>
            <?php while($row = $result->fetch_assoc()): ?>
                <tr>
                    <td><?= $row['patient_id'] ?></td>

                    <td>
                        <img class="img" src="../../uploads/<?= $row['profile_image'] ?>" 
                        onerror="this.src='https://i.pravatar.cc/40'">
                    </td>

                    <td><?= $row['name'] ?></td>
                    <td><?= $row['email'] ?></td>
                    <td><?= $row['age'] ?></td>
                    <td><?= $row['gender'] ?></td>
                    <td><?= $row['phone'] ?></td>
                    <td><?= $row['emergency_phone'] ?></td>
                    <td><?= $row['address'] ?></td>

                    <td>
                        <button class="btn edit">Edit</button>
                        <button class="btn delete">Delete</button>
                    </td>
                </tr>
            <?php endwhile; ?>
        <?php else: ?>
            <tr>
                <td colspan="10" style="text-align:center;padding:20px;">
                    No patients found
                </td>
            </tr>
        <?php endif; ?>

    </table>

</div>
</div>

</body>
</html>