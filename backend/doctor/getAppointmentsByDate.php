<?php
session_start();
require_once "../../config/db_connect.php";

if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'doctor') {
    exit;
}

$user_id = $_SESSION['user_id'];
$date = $_GET['date'] ?? '';

if(empty($date)){
    echo json_encode([]);
    exit;
}

/* Get doctor_id */
$getDoctor = $conn->query("
    SELECT doctor_id
    FROM doctors
    WHERE user_id = $user_id
");

$doctor = $getDoctor->fetch_assoc();
$doctor_id = $doctor['doctor_id'];

/* Selected date appointments */
$query = $conn->query("
    SELECT a.*, u.name as patient_name
    FROM appointment a
    JOIN users u ON a.patient_id = u.user_id
    WHERE a.doctor_id = $doctor_id
    AND DATE(a.appointment_date) = '$date'
    ORDER BY a.appointment_time ASC
");

$data = [];

while($row = $query->fetch_assoc()){
    $data[] = $row;
}

echo json_encode($data);
?>