<?php
session_start();
require_once 'inc/db.php';
$conn->set_charset('utf8mb4');

$error = '';

if($_SERVER['REQUEST_METHOD'] === 'POST'){
    $username = trim($_POST['username'] ?? '');
    $password = trim($_POST['password'] ?? '');

    if($username && $password){
        // استفاده از Prepared Statements برای جلوگیری از SQL Injection
        $stmt = $conn->prepare("SELECT id, username, password_hash, name FROM admin WHERE username=? LIMIT 1");
        $stmt->bind_param("s", $username);
        $stmt->execute();
        $res = $stmt->get_result();
        $admin = $res->fetch_assoc();

        if($admin && password_verify($password, $admin['password_hash'])){
            // ورود موفق
            $_SESSION['admin_id'] = $admin['id'];
            $_SESSION['admin_name'] = $admin['name'];
            header("Location: teacher_dashboard.php");
            exit;
        } else {
            // نمایش پیام عمومی بدون اطلاعات حساس
            $error = 'نام کاربری یا رمز عبور اشتباه است.';
        }
    } else {
        $error = 'لطفاً نام کاربری و رمز عبور را وارد کنید.';
    }
}
?>

<!DOCTYPE html>
<html lang="fa" dir="rtl">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>ورود معلم</title>
<style>
@font-face {
    font-family: 'BNazanin';
    src: url('fonts/B_Nazanin.woff2') format('woff2'),
         url('fonts/B_Nazanin.woff') format('woff');
    font-weight: normal;
    font-style: normal;
}

body {
    font-family: "B Nazanin" !important;
    margin: 0;
    padding: 0;
    background: linear-gradient(120deg, #264653, #2a9d8f);
    display: flex;
    justify-content: center;
    align-items: center;
    height: 100vh;
}

.login-card {
    font-family: "B Nazanin" !important;
    background: #ffffff;
    border-radius: 20px;
    padding: 40px 30px;
    width: 90%;
    max-width: 400px;
    box-shadow: 0 20px 40px rgba(0,0,0,0.3);
    text-align: center;
}

h2 {
    margin-bottom: 30px;
    color: #264653;
    font-size: 28px;
    font-weight: bold;
}

input {
    font-family: "B Nazanin" !important;
    width: 100%;
    padding: 12px 15px;
    margin: 12px 0;
    border: 2px solid #2a9d8f;
    border-radius: 10px;
    outline: none;
    font-size: 16px;
    transition: border 0.3s, box-shadow 0.3s;
}

input:focus {
    border-color: #264653;
    box-shadow: 0 0 12px rgba(38,70,83,0.4);
}

button {
    font-family: "B Nazanin" !important;
    width: 100%;
    padding: 12px;
    background: #264653;
    border: none;
    border-radius: 12px;
    font-size: 18px;
    color: #fff;
    font-weight: bold;
    cursor: pointer;
    transition: background 0.3s, transform 0.2s;
    margin-top: 15px;
}

button:hover {
    background: #1b3a4b;
    transform: scale(1.05);
}

.error {
    color: #fff;
    background: #e63946;
    border-radius: 10px;
    padding: 10px;
    margin-bottom: 20px;
    font-size: 14px;
    text-align: center;
}

@media (max-width: 480px) {
    .login-card {
        padding: 30px 20px;
    }

    h2 {
        font-size: 24px;
    }

    input, button {
        font-size: 16px;
    }
}
</style>
</head>
<body>

<div class="login-card">
    <h2>ورود معلم</h2>
    <?php if($error): ?><div class="error"><?= htmlspecialchars($error) ?></div><?php endif; ?>
    <form method="post" style="display:flex; flex-direction:column; align-items:center; width:100%;">
        <input type="text" name="username" placeholder="نام کاربری" required>
        <input type="password" name="password" placeholder="رمز عبور" required>
        <button type="submit">ورود</button>
    </form>
</div>

</body>
</html>
