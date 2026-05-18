<?php
header("Content-Type: application/json");
include("../config/db_connect.php");

$doctor_id = intval($_GET['doctor_id'] ?? 0);

if ($doctor_id <= 0) {
    echo json_encode(["status" => "error", "message" => "Invalid doctor ID"]);
    exit;
}

$sql = "SELECT slot_id, is_available
        FROM doctor_slots
        WHERE doctor_id = ?";

$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $doctor_id);
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