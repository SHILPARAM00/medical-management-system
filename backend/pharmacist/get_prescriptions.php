<?php
require "../config/database.php";

header("Content-Type: application/json");

/*
We are fetching prescriptions with patient + doctor info
(you can enhance later with JOIN tables)
*/

$sql = "SELECT * FROM prescriptions WHERE status='pending' ORDER BY prescription_id DESC";

$result = $conn->query($sql);

$prescriptions = [];

while($row = $result->fetch_assoc()) {
    $prescriptions[] = $row;
}

echo json_encode($prescriptions);
?>