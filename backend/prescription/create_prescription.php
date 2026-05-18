<?php
require "../config/database.php";

header("Content-Type: application/json");

$data = json_decode(file_get_contents("php://input"), true);

/* =========================
   STEP 1: GET DATA
========================= */
$doctor_id = $data['doctor_id'];
$patient_id = $data['patient_id'];
$notes = $data['notes'];
$medicines = $data['medicines'];

/* =========================
   STEP 2: CREATE PRESCRIPTION
========================= */
$stmt = $conn->prepare("
    INSERT INTO prescriptions (doctor_id, patient_id, notes, status)
    VALUES (?, ?, ?, 'pending')
");

$stmt->bind_param("iis", $doctor_id, $patient_id, $notes);

if($stmt->execute()) {

    $prescription_id = $stmt->insert_id;

    /* =========================
       STEP 3: INSERT MEDICINES
    ========================= */

    $itemStmt = $conn->prepare("
        INSERT INTO prescription_items 
        (prescription_id, medicine_id, dosage, quantity)
        VALUES (?, ?, ?, ?)
    ");

    foreach($medicines as $m) {

        $medicine_id = $m['medicine_id'];
        $dosage = $m['dosage'];
        $quantity = $m['quantity'];

        $itemStmt->bind_param(
            "iisi",
            $prescription_id,
            $medicine_id,
            $dosage,
            $quantity
        );

        $itemStmt->execute();
    }

    /* =========================
       SUCCESS RESPONSE
    ========================= */
    echo json_encode([
        "status" => "success",
        "prescription_id" => $prescription_id
    ]);

} else {

    echo json_encode([
        "status" => "error",
        "message" => "Failed to create prescription"
    ]);
}

$conn->close();
?>