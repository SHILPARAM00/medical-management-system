<?php
session_start();
require_once "../../config/db_connect.php";

if (!isset($_SESSION['user_id'])) {
    header("Location: ../../login.php");
    exit;
}

$user_id = $_SESSION['user_id'];

$message = "error";

/* =========================
   UPDATE PROFILE DETAILS
========================= */
if ($_SERVER["REQUEST_METHOD"] == "POST") {

    $name = $_POST['name'] ?? '';
    $phone = $_POST['phone'] ?? '';
    $emergency_phone = $_POST['emergency_phone'] ?? '';
    $address = $_POST['address'] ?? '';
    $gender = $_POST['gender'] ?? '';
    $age = $_POST['age'] ?? '';

    /* UPDATE USERS TABLE */
    if (!empty($name)) {
        $stmt = $conn->prepare("UPDATE users SET name=? WHERE user_id=?");
        $stmt->bind_param("si", $name, $user_id);
        $stmt->execute();
    }

    /* UPDATE PATIENT TABLE */
    $stmt2 = $conn->prepare("
        UPDATE patients 
        SET phone=?, emergency_phone=?, address=?, gender=?, age=? 
        WHERE user_id=?
    ");
    $stmt2->bind_param("ssssii", $phone, $emergency_phone, $address, $gender, $age, $user_id);

    if ($stmt2->execute()) {
        $message = "success";
    }

    /* =========================
       PROFILE IMAGE UPLOAD
    ========================= */
    if (isset($_FILES['profile_image']) && $_FILES['profile_image']['name'] != '') {

        $upload_dir = "../../uploads/";

        if (!is_dir($upload_dir)) {
            mkdir($upload_dir, 0777, true);
        }

        $filename = time() . "_" . basename($_FILES['profile_image']['name']);
        $target = $upload_dir . $filename;

        if (move_uploaded_file($_FILES['profile_image']['tmp_name'], $target)) {

            $stmt3 = $conn->prepare("
                UPDATE patients SET profile_image=? WHERE user_id=?
            ");
            $stmt3->bind_param("si", $filename, $user_id);
            $stmt3->execute();
        }
    }

    /* =========================
       REDIRECT WITH STATUS
    ========================= */
    header("Location: ../../modules/patient/profile.php?update=" . $message);
    exit;
}
?>