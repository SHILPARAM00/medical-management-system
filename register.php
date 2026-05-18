<?php
require_once "config/db_connect.php";

$error = "";
$success = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {

    $name = $_POST['name'] ?? '';
    $email = $_POST['email'] ?? '';
    $phone = $_POST['phone'] ?? '';
    $emergency_phone = $_POST['emergency_phone'] ?? '';
    $dob = $_POST['dob'] ?? '';
    $gender = $_POST['gender'] ?? '';
    $password = $_POST['password'] ?? '';

    if (!$name || !$email || !$phone || !$password || !$dob || !$gender) {
        $error = "Please fill all required fields!";
    } else {

        $check = $conn->prepare("SELECT user_id FROM users WHERE email=?");
        $check->bind_param("s", $email);
        $check->execute();
        $check->store_result();

        if ($check->num_rows > 0) {
            $error = "Email already exists!";
        } else {

            $hashed = password_hash($password, PASSWORD_DEFAULT);

            $stmt = $conn->prepare("INSERT INTO users (name,email,password,role) VALUES (?,?,?,'patient')");
            $stmt->bind_param("sss", $name, $email, $hashed);

            if ($stmt->execute()) {

                $user_id = $stmt->insert_id;
                $age = date_diff(date_create($dob), date_create('today'))->y;

                $stmt2 = $conn->prepare("
                    INSERT INTO patients (user_id, age, gender, phone, address, emergency_phone)
                    VALUES (?, ?, ?, ?, 'Not provided', ?)
                ");

                $stmt2->bind_param("iisss", $user_id, $age, $gender, $phone, $emergency_phone);

                if ($stmt2->execute()) {
                    $success = "Registration successful! Redirecting to login...";
                    header("refresh:2;url=login.php");
                } else {
                    $error = "Something went wrong!";
                }
            }
        }
    }
}
?>

<!DOCTYPE html>
<html>
<head>
<title>Register - MedSync</title>

<style>
body{
    margin:0;
    font-family:Arial;
    min-height:100vh;
    background:url('frontend/assets/images/hero.png.jpeg') no-repeat center center/cover;
    display:flex;
    justify-content:flex-end;
    align-items:center;
    background-position:top center;
    overflow-y:auto;
}

body::before{
    content:"";
    position:fixed;
    width:100%;
    height:100%;
    background:linear-gradient(
        to right,
        rgba(255,255,255,0.1),
        rgba(255,255,255,0.75)
    );
    z-index:0;
}

.container{
    position:relative;
    z-index:2;
    width:420px;
    max-height:90vh;
    overflow-y:auto;
    margin-right:80px;
    padding:25px;
    background:rgba(255,255,255,0.15);
    border-radius:15px;
    backdrop-filter:blur(4px);
    box-shadow:0 5px 25px rgba(0,0,0,0.1);
    animation:fadeUp 1s ease;
}

.logo{
    text-align:center;
    margin-bottom:15px;
}

.logo img{
    width:90px;
    height:90px;
    object-fit:contain;
    display:block;
    margin:auto;
}

.title{
    text-align:center;
    margin-bottom:15px;
}

.title h2{
    color:#2b6cb0;
    margin:5px 0;
}

.title p{
    color:#555;
}

input, select{
    width:100%;
    height:45px;
    margin:8px 0;
    padding:10px;
    border:1px solid #ccc;
    border-radius:8px;
    box-sizing:border-box;
    font-size:14px;
}

button{
    width:100%;
    padding:12px;
    background:#2b6cb0;
    color:white;
    border:none;
    border-radius:8px;
    font-weight:bold;
    cursor:pointer;
    margin-top:10px;
    font-size:15px;
}

button:hover{
    background:#1e4f8a;
}

.msg{
    text-align:center;
    margin:10px 0;
    font-weight:bold;
}

.error{color:red;}
.success{color:green;}

.links{
    text-align:center;
    margin-top:15px;
}

.links a{
    color:#2b6cb0;
    text-decoration:none;
    font-weight:bold;
}

@keyframes fadeUp{
    from{
        opacity:0;
        transform:translateY(30px);
    }
    to{
        opacity:1;
        transform:translateY(0);
    }
}
</style>
</head>

<body>

<div class="container">

    <div class="logo">
        <img src="frontend/assets/images/logo.png.jpeg" alt="Logo">
    </div>

    <div class="title">
        <h2>Create Account</h2>
        <p>Join MedSync Healthcare</p>
    </div>

    <?php if($error): ?>
        <p class="msg error"><?php echo $error; ?></p>
    <?php endif; ?>

    <?php if($success): ?>
        <p class="msg success"><?php echo $success; ?></p>
    <?php endif; ?>

    <?php if(!$success): ?>
    <form method="POST">

        <input type="text" name="name" placeholder="Full Name" required>
        <input type="email" name="email" placeholder="Email" required>
        <input type="text" name="phone" placeholder="Phone Number" required>
        <input type="text" name="emergency_phone" placeholder="Emergency Phone">

        <input type="date" name="dob" required>

        <select name="gender" required>
            <option value="">Select Gender</option>
            <option value="male">Male</option>
            <option value="female">Female</option>
            <option value="other">Other</option>
        </select>

        <input type="password" name="password" placeholder="Password" required>

        <button type="submit">Register</button>

    </form>
    <?php endif; ?>

    <div class="links">
        Already have an account? <a href="login.php">Login</a>
    </div>

</div>

</body>
</html>