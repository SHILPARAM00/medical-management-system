<?php
require_once "../../config/db_connect.php";

$data = json_decode(file_get_contents("php://input"), true);

$stmt = $conn->prepare("
UPDATE prescription 
SET medication_name=?, dosage=?, status=? 
WHERE prescription_id=?
");

$stmt->bind_param(
"sssi",
$data['medicine'],
$data['dosage'],
$data['status'],
$data['id']
);

$stmt->execute();

echo "updated";