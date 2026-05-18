<?php
require "../config/database.php";

header("Content-Type: application/json");

$data = json_decode(file_get_contents("php://input"), true);

$prescription_id = $data['id'];

/* JOIN to get full details */
$sql = "
SELECT 
    pi.item_id,
    m.name AS medicine_name,
    pi.dosage,
    pi.quantity
FROM prescription_items pi
JOIN medicines m ON pi.medicine_id = m.medicine_id
WHERE pi.prescription_id = ?
";

$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $prescription_id);
$stmt->execute();

$result = $stmt->get_result();

$items = [];

while($row = $result->fetch_assoc()) {
    $items[] = $row;
}

echo json_encode($items);
?>