<?php
session_start();
require_once "../../config/db_connect.php";

/* SECURITY */
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../../login.php");
    exit;
}

/* ================= ADD DOCTOR ================= */
if (isset($_POST['add_doctor'])) {

    $name = $_POST['name'];
    $email = $_POST['email'];
    $password = password_hash($_POST['password'], PASSWORD_DEFAULT);

    $specialization = $_POST['specialization'];
    $experience = $_POST['experience'];
    $fee = $_POST['fee'];
    $phone = $_POST['phone'];

    $conn->query("
        INSERT INTO users (name, email, password, role)
        VALUES ('$name', '$email', '$password', 'doctor')
    ");

    $user_id = $conn->insert_id;

    $conn->query("
        INSERT INTO doctors (user_id, specialization, experience, fee, phone)
        VALUES ('$user_id', '$specialization', '$experience', '$fee', '$phone')
    ");

    header("Location: doctors.php");
    exit;
}

/* ================= DELETE ================= */
if (isset($_GET['delete_id'])) {
    $id = $_GET['delete_id'];
    $conn->query("DELETE FROM doctors WHERE doctor_id=$id");
    header("Location: doctors.php");
    exit;
}

/* ================= UPDATE ================= */
if (isset($_POST['update_doctor'])) {
    $id = $_POST['doctor_id'];
    $specialization = $_POST['specialization'];
    $experience = $_POST['experience'];
    $fee = $_POST['fee'];
    $phone = $_POST['phone'];

    $conn->query("
        UPDATE doctors SET
        specialization='$specialization',
        experience='$experience',
        fee='$fee',
        phone='$phone'
        WHERE doctor_id=$id
    ");

    header("Location: doctors.php");
    exit;
}

/* FETCH */
$sql = "
SELECT d.doctor_id,d.specialization,d.experience,d.fee,d.phone,
u.name,u.email
FROM doctors d
JOIN users u ON d.user_id = u.user_id
";
$result = $conn->query($sql);
?>

<!DOCTYPE html>
<html>
<head>
<title>Doctors Management</title>

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

h2{
    margin-bottom:20px;
}

.top-btn{
    background:#2b6cb0;
    color:white;
    padding:10px 16px;
    border:none;
    border-radius:8px;
    cursor:pointer;
    margin-bottom:15px;
}

/* TABLE */
table{
    width:100%;
    border-collapse:collapse;
    background:white;
    border-radius:12px;
    overflow:hidden;
    box-shadow:0 5px 15px rgba(0,0,0,0.05);
}

th,td{
    padding:12px;
    text-align:left;
    border-bottom:1px solid #eee;
    font-size:14px;
}

th{
    background:#2b6cb0;
    color:white;
}

/* BUTTON */
.btn{
    padding:6px 10px;
    border:none;
    border-radius:6px;
    cursor:pointer;
    text-decoration:none;
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

/* MODAL */
.modal{
    display:none;
    position:fixed;
    top:0;
    left:0;
    width:100%;
    height:100%;
    background:rgba(0,0,0,0.5);
    justify-content:center;
    align-items:center;
}

.modal-box{
    background:white;
    width:350px;
    padding:25px;
    border-radius:12px;
}

.modal-box h3{
    margin-bottom:15px;
}

.modal-box input{
    width:100%;
    padding:10px;
    margin-bottom:10px;
    border:1px solid #ccc;
    border-radius:6px;
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
        <a href="doctors.php" class="active">👨‍⚕️ Doctors</a>
        <a href="patients.php">👥 Patients</a>
        <a href="pharmacists.php">💊 Pharmacists</a>
        <a href="appointments.php">📅 Appointments</a>
        <a href="profile.php">👤 Profile</a>
        <a href="../../logout.php">🚪 Logout</a>
    </div>

</div>

<!-- MAIN -->
<div class="main">

<h2>Doctors Management</h2>

<button class="top-btn" onclick="document.getElementById('addForm').style.display='flex'">
+ Add Doctor
</button>

<table>
<tr>
    <th>ID</th>
    <th>Name</th>
    <th>Email</th>
    <th>Specialization</th>
    <th>Experience</th>
    <th>Fee</th>
    <th>Phone</th>
    <th>Actions</th>
</tr>

<?php while($row = $result->fetch_assoc()): ?>
<tr>
    <td><?= $row['doctor_id'] ?></td>
    <td><?= $row['name'] ?></td>
    <td><?= $row['email'] ?></td>
    <td><?= $row['specialization'] ?></td>
    <td><?= $row['experience'] ?></td>
    <td><?= $row['fee'] ?></td>
    <td><?= $row['phone'] ?></td>
    <td>
        <button class="btn edit"
        onclick="openEdit(
            <?= $row['doctor_id'] ?>,
            '<?= $row['specialization'] ?>',
            '<?= $row['experience'] ?>',
            '<?= $row['fee'] ?>',
            '<?= $row['phone'] ?>'
        )">Edit</button>

        <a class="btn delete"
        href="?delete_id=<?= $row['doctor_id'] ?>"
        onclick="return confirm('Delete doctor?')">Delete</a>
    </td>
</tr>
<?php endwhile; ?>

</table>

</div>
</div>

<!-- ADD FORM -->
<div class="modal" id="addForm">
<div class="modal-box">
<h3>Add Doctor</h3>

<form method="POST">
<input name="name" placeholder="Name" required>
<input name="email" placeholder="Email" required>
<input type="password" name="password" placeholder="Password" required>
<input name="specialization" placeholder="Specialization" required>
<input name="experience" placeholder="Experience" required>
<input name="fee" placeholder="Fee" required>
<input name="phone" placeholder="Phone" required>

<button class="btn edit" name="add_doctor">Save</button>
<button type="button" class="btn delete" onclick="closeModal('addForm')">Close</button>
</form>
</div>
</div>

<!-- EDIT FORM -->
<div class="modal" id="editForm">
<div class="modal-box">
<h3>Edit Doctor</h3>

<form method="POST">
<input type="hidden" name="doctor_id" id="edit_id">
<input name="specialization" id="edit_spec" required>
<input name="experience" id="edit_exp" required>
<input name="fee" id="edit_fee" required>
<input name="phone" id="edit_phone" required>

<button class="btn edit" name="update_doctor">Update</button>
<button type="button" class="btn delete" onclick="closeModal('editForm')">Close</button>
</form>
</div>
</div>

<script>
function openEdit(id,spec,exp,fee,phone){
    document.getElementById("editForm").style.display="flex";
    document.getElementById("edit_id").value=id;
    document.getElementById("edit_spec").value=spec;
    document.getElementById("edit_exp").value=exp;
    document.getElementById("edit_fee").value=fee;
    document.getElementById("edit_phone").value=phone;
}

function closeModal(id){
    document.getElementById(id).style.display="none";
}
</script>

</body>
</html>