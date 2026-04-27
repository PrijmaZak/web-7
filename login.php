<?php
session_start();
header('Content-Type: text/html; charset=UTF-8');

if (isset($_SESSION['user_id'])) {
    header('Location: index.php');
    exit;
}
?>
<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <title>Вход — Задание 5</title>
    <style>
        body{font-family:'Segoe UI',sans-serif;background:linear-gradient(135deg,#667eea,#764ba2);margin:0;padding:40px 0;}
        .container{max-width:400px;margin:0 auto;background:white;border-radius:20px;box-shadow:0 15px 35px rgba(0,0,0,0.2);overflow:hidden;}
        header{background:#333;color:white;padding:30px;text-align:center;}
        .form-body{padding:40px;}
        label{display:block;margin:15px 0 5px;font-weight:600;}
        input{width:100%;padding:12px;border:2px solid #ddd;border-radius:8px;font-size:16px;box-sizing:border-box;margin-bottom:15px;}
        input:focus{border-color:#667eea;outline:none;}
        button{background:#667eea;color:white;padding:15px 40px;font-size:18px;border:none;border-radius:8px;cursor:pointer;width:100%;}
        button:hover{background:#764ba2;}
        .error{color:#e74c3c;margin-top:10px;text-align:center;}
    </style>
</head>
<body>
<div class="container">
    <header><h1>Вход в систему</h1></header>
    <div class="form-body">
        <?php if (isset($login_error)) echo '<div class="error">'.$login_error.'</div>'; ?>
        <form method="post" action="index.php">
            <input type="hidden" name="login_form" value="1">
            <label>Логин</label>
            <input type="text" name="login" required>
            <label>Пароль</label>
            <input type="password" name="password" required>
            <button type="submit">Войти</button>
        </form>
    </div>
</div>
</body>
</html>









