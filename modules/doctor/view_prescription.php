<?php
session_start();
require_once "../../config/db_connect.php";

/* SECURITY */
if (!isset($_SESSION['user_id'])) {
    header("Location: ../../login.php");
    exit;
}

/* VALIDATE ID */
if (!isset($_GET['id']) || empty($_GET['id'])) {
    echo "<h3>Invalid Prescription ID</h3>";
    exit;
}

$prescription_id = intval($_GET['id']);

/* GET PRESCRIPTION DETAILS */
$stmt = $conn->prepare("
SELECT 
    pr.*,
    d.specialization,
    u1.name AS doctor_name,
    u2.name AS patient_name
FROM prescriptions pr
LEFT JOIN doctors d ON pr.doctor_id = d.doctor_id
LEFT JOIN users u1 ON d.user_id = u1.user_id
LEFT JOIN patients p ON pr.patient_id = p.patient_id
LEFT JOIN users u2 ON p.user_id = u2.user_id
WHERE pr.prescription_id = ?
");

$stmt->bind_param("i", $prescription_id);
$stmt->execute();
$data = $stmt->get_result()->fetch_assoc();

/* SAFETY CHECK */
if (!$data) {
    echo "<h3 style='color:red;text-align:center;margin-top:50px;'>
    Prescription not found or deleted
    </h3>";
    exit;
}

/* GET MEDICINES */
$stmt = $conn->prepare("
SELECT 
    pi.quantity,
    pi.dosage,
    m.name AS medicine_name,
    m.stock
FROM prescription_items pi
JOIN medicines m ON pi.medicine_id = m.medicine_id
WHERE pi.prescription_id = ?
");

$stmt->bind_param("i", $prescription_id);
$stmt->execute();
$items = $stmt->get_result();
?>

<!DOCTYPE html>
<html>
<head>
<title>Prescription</title>

<style>
body{
    font-family:Arial;
    background:#f5f5f5;
    margin:0;
}

.container{
    width:800px;
    margin:20px auto;
    background:white;
    padding:30px;
    box-shadow:0 0 10px rgba(0,0,0,0.2);
}

.header{
    text-align:center;
    border-bottom:2px solid black;
    padding-bottom:10px;
}

.section{
    margin-top:15px;
}

table{
    width:100%;
    border-collapse:collapse;
    margin-top:10px;
}

th,td{
    border:1px solid #000;
    padding:8px;
    text-align:center;
}

@media print{
    .no-print{
        display:none;
    }
    body{
        background:white;
    }
}
</style>
</head>

<body>

<div class="container">

<!-- HEADER -->
<div class="header">
    <h2>CITY HOSPITAL</h2>
    <p>Medical Prescription Report</p>
</div>

<!-- DOCTOR -->
<div class="section">
    <b>Doctor:</b> <?= htmlspecialchars($data['doctor_name'] ?? 'N/A') ?> <br>
    <b>Specialization:</b> <?= htmlspecialchars($data['specialization'] ?? 'N/A') ?> <br>
    <b>Date:</b> <?= $data['created_at'] ?>
</div>

<!-- PATIENT -->
<div class="section">
    <b>Patient Name:</b> <?= htmlspecialchars($data['patient_name'] ?? 'N/A') ?> <br>
    <b>Prescription ID:</b> <?= $data['prescription_id'] ?> <br>
    <b>Appointment ID:</b> <?= $data['appointment_id'] ?>
</div>

<!-- NOTES -->
<div class="section">
    <b>Diagnosis / Notes:</b><br>
    <?= nl2br(htmlspecialchars($data['notes'] ?? 'No notes')) ?>
</div>

<!-- MEDICINES -->
<div class="section">
    <h3>Medicines</h3>

    <table>
        <tr>
            <th>Medicine</th>
            <th>Dosage</th>
            <th>Quantity</th>
            <th>Stock</th>
            <th>Status</th>
        </tr>

        <?php if ($items->num_rows > 0) { ?>

            <?php while($item = $items->fetch_assoc()) { ?>
                <tr>
                    <td><?= htmlspecialchars($item['medicine_name']) ?></td>
                    <td><?= $item['dosage'] ?></td>
                    <td><?= $item['quantity'] ?></td>
                    <td><?= $item['stock'] ?></td>

                    <td>
                        <?php if($item['stock'] == 0) { ?>
                            <span style="color:red;">Out of Stock</span>

                        <?php } elseif($item['stock'] < $item['quantity']) { ?>
                            <span style="color:orange;">Not Enough</span>

                        <?php } else { ?>
                            <span style="color:green;">OK</span>
                        <?php } ?>
                    </td>
                </tr>
            <?php } ?>

        <?php } else { ?>
            <tr>
                <td colspan="5">No medicines found</td>
            </tr>
        <?php } ?>

    </table>
</div>

<!-- SIGNATURE -->
<div class="section">
    <br><br>
    <b>Doctor Signature:</b> ____________________
</div>

<br>

<!-- PRINT -->
<div class="no-print">
    <button onclick="window.print()">🖨️ Print Prescription</button>
</div>

</div>

</body>
</html>