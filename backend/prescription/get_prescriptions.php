<?php
require "../config/database.php";

header("Content-Type: application/json");

$sql = "
SELECT p.prescription_id, p.patient_id, p.doctor_id, p.status
FROM prescriptions p
ORDER BY p.prescription_id DESC
";

$result = $conn->query($sql);

$data = [];

while($row = $result->fetch_assoc()) {
    $data[] = $row;
}

echo json_encode($data);
?>