<?php
header("Content-Type: application/json");
include("../config/db_connect.php");

if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    echo json_encode(["status" => "error", "message" => "Invalid request"]);
    exit;
}

$appointment_id = intval($_POST['appointment_id'] ?? 0);

if ($appointment_id <= 0) {
    echo json_encode(["status" => "error", "message" => "Invalid appointment ID"]);
    exit;
}

$conn->begin_transaction();

try {
    $sql = "SELECT slot_id, status 
            FROM appointments 
            WHERE appointment_id = ? FOR UPDATE";

    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $appointment_id);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows !== 1) {
        throw new Exception("Appointment not found");
    }

    $data = $result->fetch_assoc();

    if ($data['status'] === 'cancelled') {
        throw new Exception("Already cancelled");
    }

    $slot_id = $data['slot_id'];

    // Update appointment
    $update = "UPDATE appointments 
               SET status = 'cancelled' 
               WHERE appointment_id = ?";
    $stmt = $conn->prepare($update);
    $stmt->bind_param("i", $appointment_id);
    $stmt->execute();

    // Free slot
    $slot_update = "UPDATE doctor_slots 
                    SET is_available = 1 
                    WHERE slot_id = ?";
    $stmt = $conn->prepare($slot_update);
    $stmt->bind_param("i", $slot_id);
    $stmt->execute();

    $conn->commit();

    echo json_encode([
        "status" => "success",
        "message" => "Appointment cancelled"
    ]);

} catch (Exception $e) {
    $conn->rollback();

    echo json_encode([
        "status" => "error",
        "message" => $e->getMessage()
    ]);
}
?>