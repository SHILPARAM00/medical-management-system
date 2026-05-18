<?php
session_start();
require_once "../../config/db_connect.php";

$user_id = $_SESSION['user_id'];

$name = $_POST['name'];
$email = $_POST['email'];
$password = $_POST['password'];

// IF PASSWORD EMPTY → DON'T UPDATE PASSWORD
if(empty($password)){

    $stmt = $conn->prepare("UPDATE users SET name=?, email=? WHERE user_id=?");
    $stmt->bind_param("ssi", $name, $email, $user_id);

}else{

    $hashed = password_hash($password, PASSWORD_DEFAULT);

    $stmt = $conn->prepare("UPDATE users SET name=?, email=?, password=? WHERE user_id=?");
    $stmt->bind_param("sssi", $name, $email, $hashed, $user_id);
}

$stmt->execute();

// UPDATE SESSION NAME
$_SESSION['name'] = $name;

header("Location: ../../modules/pharmacist/profile.php");
?>