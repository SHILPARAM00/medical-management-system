<?php
session_start();
require_once "../../config/db_connect.php";

header("Content-Type: application/json");

/* AUTH CHECK */
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'patient') {
    echo json_encode(["status" => "error", "message" => "Unauthorized"]);
    exit;
}

$user_id = $_SESSION['user_id'];

/* GET PATIENT ID */
$stmt = $conn->prepare("SELECT patient_id FROM patients WHERE user_id=?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$res = $stmt->get_result();
$patient = $res->fetch_assoc();

if (!$patient) {
    echo json_encode(["status" => "error", "message" => "Patient not found"]);
    exit;
}

$patient_id = $patient['patient_id'];

/* INPUT */
$data = json_decode(file_get_contents("php://input"), true);

$doctor_id = $data['doctor_id'] ?? '';
$appointment_date = $data['appointment_date'] ?? '';

if (!$doctor_id || !$appointment_date) {
    echo json_encode(["status" => "error", "message" => "All fields required"]);
    exit;
}

/* INSERT APPOINTMENT */
$stmt = $conn->prepare("
INSERT INTO appointments (patient_id, doctor_id, appointment_date, status, created_at)
VALUES (?, ?, ?, 'pending', NOW())
");

$stmt->bind_param("iis", $patient_id, $doctor_id, $appointment_date);

if ($stmt->execute()) {
    echo json_encode([
        "status" => "success",
        "message" => "Appointment booked successfully"
    ]);
} else {
    echo json_encode([
        "status" => "error",
        "message" => "Failed to book appointment"
    ]);
}
?>