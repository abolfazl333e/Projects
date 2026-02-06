<?php
session_start();
require_once 'inc/db.php';
$conn->set_charset('utf8mb4');

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username'] ?? '');
    $password = trim($_POST['password'] ?? '');

    if ($username && $password) {
        $stmt = $conn->prepare("SELECT id, name, password_hash FROM students WHERE username = ? LIMIT 1");
        $stmt->bind_param("s", $username);
        $stmt->execute();
        $res = $stmt->get_result();
        $student = $res->fetch_assoc();

        if ($student && password_verify($password, $student['password_hash'])) {
            $_SESSION['student_id'] = $student['id'];
            $_SESSION['student_name'] = $student['name'];
            header("Location: student_dashboard.php");
            exit;
        } else {
            $error = "نام کاربری یا رمز عبور اشتباه است 😕";
        }
    } else {
        $error = "لطفاً همه فیلدها را پر کنید 🌟";
    }
}
?>

<!DOCTYPE html>
<html lang="fa" dir="rtl">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>ورود دانش‌آموز</title>
<style>
@font-face {
    font-family: 'BNazanin';
    src: url('fonts/B_Nazanin.woff2') format('woff2'),
         url('fonts/B_Nazanin.woff') format('woff');
    font-weight: normal;
    font-style: normal;
}

body {
    font-family: 'BNazanin', sans-serif;
    margin: 0;
    padding: 0;
    background: linear-gradient(135deg, #6dd5ed, #2193b0);
    display: flex;
    justify-content: center;
    align-items: center;
    height: 100vh;
}

.login-box {
    font-family: "B Nazanin" !important;
    background: #f0f4f8;
    border-radius: 25px;
    width: 90%;
    max-width: 400px;
    padding: 40px 30px;
    box-shadow: 0 15px 30px rgba(0,0,0,0.2);
    text-align: center;
    position: relative;
    display: flex;
    flex-direction: column;
    align-items: center;
}

.login-box::before {
    content: '';
    position: absolute;
    top: -20px;
    right: -20px;
    width: 100px;
    height: 100px;
    background: url('https://upload.wikimedia.org/wikipedia/commons/0/0e/Emoji_u1f466.svg') no-repeat center/contain;
    opacity: 0.25;
}

h2 {
    margin-bottom: 30px;
    color: #0d3b66;
    font-size: 26px;
}

input {
    font-family: "B Nazanin" !important;
    width: 100%;
    max-width: 300px;
    padding: 12px 15px;
    margin-bottom: 20px;
    border: 2px solid #0077b6;
    border-radius: 12px;
    outline: none;
    font-size: 16px;
    transition: border 0.3s, box-shadow 0.3s;
}

input:focus {
    border-color: #023e8a;
    box-shadow: 0 0 10px rgba(2,62,138,0.3);
}

button {
    font-family: "B Nazanin" !important;
    width: 100%;
    max-width: 300px;
    padding: 12px;
    background: linear-gradient(135deg, #00b4d8, #0077b6);
    border: none;
    border-radius: 12px;
    font-size: 18px;
    color: #fff;
    font-weight: bold;
    cursor: pointer;
    transition: transform 0.2s, background 0.3s;
}

button:hover {
    transform: scale(1.05);
    background: linear-gradient(135deg, #0077b6, #023e8a);
}

.error {
    color: #d90429;
    background: #ffe6e6;
    border-radius: 10px;
    padding: 10px;
    margin-bottom: 20px;
    font-size: 14px;
    width: 100%;
    max-width: 300px;
    text-align: center;
}

@media (max-width: 480px) {
    .login-box {
        padding: 30px 20px;
    }

    h2 {
        font-size: 22px;
    }

    input, button {
        font-size: 16px;
    }
}
</style>
</head>
<body>

<div class="login-box">
    <h2>ورود دانش‌آموز 👦</h2>
    <?php if($error) echo "<div class='error'>{$error}</div>"; ?>
    <form method="post" style="width:100%; display:flex; flex-direction:column; align-items:center;">
        <input type="text" name="username" placeholder="نام کاربری شما" required>
        <input type="password" name="password" placeholder="رمز عبور" required>
        <button type="submit">ورود 🚀</button>
    </form>
</div>

</body>
</html>
