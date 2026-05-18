<?php
require_once "../../config/db_connect.php";

header("Content-Type: application/json");

// CHECK PARAM
if (!isset($_GET['patient_id'])) {
    echo json_encode([]);
    exit;
}

$patient_id = intval($_GET['patient_id']);

// QUERY (JOIN WITH DOCTORS)
$sql = "
SELECT 
    a.appointment_id,
    a.appointment_date,
    a.status,
    d.specialization,
    u.name AS doctor_name
FROM appointments a
JOIN doctors d ON a.doctor_id = d.doctor_id
JOIN users u ON d.user_id = u.user_id
WHERE a.patient_id = ?
ORDER BY a.appointment_date DESC
";

$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $patient_id);
$stmt->execute();

$result = $stmt->get_result();

$data = [];

while ($row = $result->fetch_assoc()) {
    $data[] = $row;
}

echo json_encode($data);
?>