<?php
require_once "../../config/db_connect.php";

$data = json_decode(file_get_contents("php://input"), true);

$stmt = $conn->prepare("
INSERT INTO prescription 
(patient_id, medication_name, dosage, duration, status, created_at, doctor_id)
VALUES (?, ?, ?, ?, 'active', NOW(), ?)
");

$stmt->bind_param(
"isssi",
$data['patient_id'],
$data['medicine'],
$data['dosage'],
$data['duration'],
$_SESSION['user_id']
);

$stmt->execute();

echo "success";