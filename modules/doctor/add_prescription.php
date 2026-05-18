<?php
session_start();
require_once "../../config/db_connect.php";

if (!isset($_SESSION['user_id'])) {
    header("Location: ../../login.php");
    exit;
}

$doctor_id = $_SESSION['user_id'];
$patient_id = $_GET['patient_id'] ?? 0;

$success = "";
$error = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {

    $medicine = $_POST['medicine'];
    $dosage = $_POST['dosage'];
    $duration = $_POST['duration'];
    $notes = $_POST['notes'];

    if (!$medicine || !$dosage || !$duration) {
        $error = "All fields required!";
    } else {

        $stmt = $conn->prepare("
            INSERT INTO prescription 
            (patient_id, doctor_id, medicine_name, dosage, duration, notes, status, created_at)
            VALUES (?, ?, ?, ?, ?, ?, 'active', NOW())
        ");

        $stmt->bind_param("iissss",
            $patient_id,
            $doctor_id,
            $medicine,
            $dosage,
            $duration,
            $notes
        );

        if ($stmt->execute()) {
            $success = "Prescription added!";
        } else {
            $error = "Error!";
        }
    }
}
?>

<!DOCTYPE html>
<html>
<head>
<title>Add Prescription</title>

<style>
body{
    font-family:Arial;
    background:#f4f7fb;
    display:flex;
    justify-content:center;
    align-items:center;
    height:100vh;
}

.box{
    background:white;
    padding:25px;
    width:400px;
    border-radius:12px;
    box-shadow:0 5px 20px rgba(0,0,0,0.08);
}

input, textarea{
    width:100%;
    padding:10px;
    margin:10px 0;
}

button{
    width:100%;
    padding:12px;
    background:#2b6cb0;
    color:white;
    border:none;
    border-radius:6px;
}

.msg{text-align:center;}
.success{color:green;}
.error{color:red;}
</style>
</head>

<body>

<div class="box">

<h2>Add Prescription</h2>

<?php if($success): ?><p class="success"><?php echo $success; ?></p><?php endif; ?>
<?php if($error): ?><p class="error"><?php echo $error; ?></p><?php endif; ?>

<form method="POST">

<input type="text" name="medicine" placeholder="Medicine Name" required>
<input type="text" name="dosage" placeholder="Dosage (e.g. 500mg)" required>
<input type="text" name="duration" placeholder="Duration (e.g. 5 days)" required>

<textarea name="notes" placeholder="Doctor Notes (optional)"></textarea>

<button type="submit">Save Prescription</button>

</form>

</div>

</body>
</html>