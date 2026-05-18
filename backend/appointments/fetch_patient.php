<?php
header("Content-Type: application/json");
include("../config/db_connect.php");

$patient_id = intval($_GET['patient_id'] ?? 0);

if ($patient_id <= 0) {
    echo json_encode(["status" => "error", "message" => "Invalid patient ID"]);
    exit;
}

$sql = "SELECT appointment_id, doctor_id, slot_id, status
        FROM appointments
        WHERE patient_id = ?";

$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $patient_id);
$stmt->execute();
$result = $stmt->get_result();

$data = [];

while ($row = $result->fetch_assoc()) {
    $data[] = $row;
}

echo json_encode([
    "status" => "success",
    "data" => $data
]);
?>