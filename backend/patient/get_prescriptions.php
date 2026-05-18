<?php
require "../config/db_connect.php";
header("Content-Type: application/json");

$patient_id = $_GET['patient_id'];

$sql = "
SELECT p.prescription_id, p.status, d.doctor_id
FROM prescriptions p
JOIN doctors d ON d.doctor_id = p.doctor_id
WHERE p.patient_id = ?
ORDER BY p.prescription_id DESC
";

$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $patient_id);
$stmt->execute();

$result = $stmt->get_result();

$data = [];

while($row = $result->fetch_assoc()) {
    $data[] = $row;
}

echo json_encode($data);
?>