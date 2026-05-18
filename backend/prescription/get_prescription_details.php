<?php
require "../config/database.php";

header("Content-Type: application/json");

$data = json_decode(file_get_contents("php://input"), true);

$id = $data['id'];

$sql = "
SELECT m.name as medicine_name, pi.dosage, pi.quantity
FROM prescription_items pi
JOIN medicines m ON m.medicine_id = pi.medicine_id
WHERE pi.prescription_id = ?
";

$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $id);
$stmt->execute();

$result = $stmt->get_result();

$output = [];

while($row = $result->fetch_assoc()) {
    $output[] = $row;
}

echo json_encode($output);
?>