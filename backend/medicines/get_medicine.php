<?php
require_once "../../config/db_connect.php";

/* FETCH MEDICINES WITH EXPIRY INFO */
$query = "
SELECT 
    medicine_id,
    name,
    price,
    stock,
    expiry_date,
    DATEDIFF(expiry_date, CURDATE()) AS days_left
FROM medicines
ORDER BY medicine_id DESC
";

$result = $conn->query($query);

$medicines = [];

/* STORE DATA */
while ($row = $result->fetch_assoc()) {
    $medicines[] = $row;
}

/* RETURN JSON */
header('Content-Type: application/json');
echo json_encode($medicines);
?>