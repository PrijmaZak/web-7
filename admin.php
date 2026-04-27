<?php
$host = 'localhost';
$dbname = 'u82352';
$username = 'u82352';
$password = '9562557';

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8mb4", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("Ошибка подключения к БД: " . $e->getMessage());
}

if (empty($_SERVER['PHP_AUTH_USER']) || $_SERVER['PHP_AUTH_USER'] != 'admin' || $_SERVER['PHP_AUTH_PW'] != '123') {
    header('HTTP/1.1 401 Unauthorized');
    header('WWW-Authenticate: Basic realm="Admin Panel"');
    die('Авторизуйтесь для доступа к панели');
}

$allowed_languages = ['Pascal', 'C', 'C++', 'JavaScript', 'PHP', 'Python', 'Java', 'Go'];

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['save_admin'])) {
    $id = (int)$_POST['user_id'];
    $fio = $_POST['fio'] ?? '';
    $phone = $_POST['phone'] ?? '';
    $email = $_POST['email'] ?? '';
    $birth = $_POST['birth_date'] ?? '2000-01-01';
    $gender = $_POST['gender'] ?? 'male'; 
    $bio = $_POST['biography'] ?? '';
    $langs = $_POST['languages'] ?? [];

    try {
        $stmt = $pdo->prepare("UPDATE applications SET fio=?, phone=?, email=?, birth_date=?, gender=?, biography=? WHERE id=?");
        $stmt->execute([$fio, $phone, $email, $birth, $gender, $bio, $id]);

        $pdo->prepare("DELETE FROM application_languages WHERE application_id = ?")->execute([$id]);
        foreach ($langs as $lang) {
            $pdo->prepare("INSERT INTO application_languages (application_id, language_id) VALUES (?, (SELECT id FROM languages WHERE name = ?))")->execute([$id, $lang]);
        }
        header('Location: admin.php?success=1');
    } catch (Exception $e) {
        die("Ошибка при сохранении: " . $e->getMessage());
    }
    exit;
}

if (isset($_POST['del_id'])) {
    $id = (int)$_POST['del_id'];
    $pdo->prepare("DELETE FROM application_languages WHERE application_id = ?")->execute([$id]);
    $pdo->prepare("DELETE FROM users WHERE application_id = ?")->execute([$id]);
    $pdo->prepare("DELETE FROM applications WHERE id = ?")->execute([$id]);
    header('Location: admin.php?deleted=1');
    exit;
}

$apps = $pdo->query("SELECT a.*, u.login, u.password_raw FROM applications a JOIN users u ON a.id = u.application_id ORDER BY a.id DESC")->fetchAll(PDO::FETCH_ASSOC);
$stats = $pdo->query("SELECT l.name, COUNT(al.application_id) as count FROM languages l LEFT JOIN application_languages al ON l.id = al.language_id GROUP BY l.id")->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <title>Админ-панель</title>
    <style>
        body { font-family: sans-serif; background: #f0f2f5; margin: 0; padding: 20px; color: #333; }
        .container { max-width: 1000px; margin: 0 auto; background: white; padding: 25px; border-radius: 8px; box-shadow: 0 2px 10px rgba(0,0,0,0.1); }
        
        .nav { margin-bottom: 25px; padding-bottom: 15px; border-bottom: 2px solid #5a67d8; display: flex; gap: 15px; }
        .nav a { text-decoration: none; color: #5a67d8; font-weight: bold; border: 1px solid #5a67d8; padding: 8px 16px; border-radius: 4px; transition: 0.3s; }
        .nav a:hover { background: #5a67d8; color: white; }

        .stats-box { background: #edf2f7; padding: 15px; border-radius: 6px; margin-bottom: 25px; border-left: 5px solid #5a67d8; }
        .stats-box h3 { margin-top: 0; font-size: 16px; }

        .user-card { border: 1px solid #e2e8f0; border-radius: 6px; padding: 15px; margin-bottom: 15px; background: #fff; }
        .user-header { display: flex; justify-content: space-between; align-items: center; border-bottom: 1px solid #f0f0f0; padding-bottom: 10px; margin-bottom: 10px; }
        
        .info-row { font-size: 14px; margin: 5px 0; }
        .info-row b { color: #2d3748; }

        .edit-form { display: none; margin-top: 15px; background: #f8fafc; padding: 20px; border: 1px solid #cbd5e0; border-radius: 6px; }
        input, select, textarea { width: 100%; padding: 10px; margin-top: 5px; margin-bottom: 15px; border: 1px solid #cbd5e0; border-radius: 4px; box-sizing: border-box; }
        
        .btn { cursor: pointer; padding: 8px 16px; border-radius: 4px; font-weight: bold; border: none; }
        .btn-edit { background: #5a67d8; color: white; }
        .btn-del { background: #e53e3e; color: white; }
        .btn-save { background: #38a169; color: white; width: 100%; margin-top: 10px; }
        
        .login-info { color: #2b6cb0; font-family: monospace; font-size: 15px; }
        .pass-info { color: #c53030; font-family: monospace; font-size: 15px; }
    </style>
    <script>
        function toggleEdit(id) {
            var el = document.getElementById('form-' + id);
            el.style.display = (el.style.display === 'none' || el.style.display === '') ? 'block' : 'none';
        }
    </script>
</head>
<body>

<div class="container">
    <div class="nav">
        <a href="login.php">Вход</a>
        <a href="logout.php">Регистрация</a>
    </div>

    <h2>Управление пользователями</h2>

    <div class="stats-box">
        <h3>Статистика по языкам:</h3>
        <?php foreach($stats as $s): ?>
            <span style="margin-right:15px;"><?=htmlspecialchars($s['name'])?>: <b><?=$s['count']?></b></span>
        <?php endforeach; ?>
    </div>

    <?php foreach($apps as $a): 
        $stmt = $pdo->prepare("SELECT l.name FROM application_languages al JOIN languages l ON al.language_id = l.id WHERE al.application_id = ?");
        $stmt->execute([$a['id']]);
        $user_langs = $stmt->fetchAll(PDO::FETCH_COLUMN);
    ?>
        <div class="user-card">
            <div class="user-header">
                <span>ID: <b><?=$a['id']?></b> | Логин: <span class="login-info"><?=$a['login']?></span> | Пароль: <span class="pass-info"><?=$a['password_raw']?></span></span>
                <div>
                    <button class="btn btn-edit" onclick="toggleEdit(<?=$a['id']?>)">Изменить данные</button>
                    <form method="POST" style="display:inline;">
                        <input type="hidden" name="del_id" value="<?=$a['id']?>">
                        <button type="submit" class="btn btn-del" onclick="return confirm('Удалить этого пользователя?')">Удалить</button>
                    </form>
                </div>
            </div>

            <div class="info-row"><b>ФИО:</b> <?=htmlspecialchars($a['fio'] ?: 'Не заполнено')?></div>
            <div class="info-row"><b>Языки:</b> <?=implode(', ', $user_langs) ?: 'Нет'?></div>

            <div class="edit-form" id="form-<?=$a['id']?>">
                <form method="POST">
                    <input type="hidden" name="user_id" value="<?=$a['id']?>">
                    
                    <label>ФИО:</label>
                    <input type="text" name="fio" value="<?=htmlspecialchars($a['fio'] ?? '')?>">
                    
                    <div style="display: flex; gap: 10px;">
                        <div style="flex: 1;">
                            <label>Телефон:</label>
                            <input type="tel" name="phone" value="<?=htmlspecialchars($a['phone'] ?? '')?>">
                        </div>
                        <div style="flex: 1;">
                            <label>Email:</label>
                            <input type="email" name="email" value="<?=htmlspecialchars($a['email'] ?? '')?>">
                        </div>
                    </div>

                    <div style="display: flex; gap: 10px;">
                        <div style="flex: 1;">
                            <label>Дата рождения:</label>
                            <input type="date" name="birth_date" value="<?=$a['birth_date']?>">
                        </div>
                        <div style="flex: 1;">
                            <label>Пол:</label>
                            <select name="gender">
                                <option value="male" <?=$a['gender']=='male'?'selected':''?>>Мужской</option>
                                <option value="female" <?=$a['gender']=='female'?'selected':''?>>Женский</option>
                            </select>
                        </div>
                    </div>

                    <label>Любимые языки программирования:</label>
                    <select name="languages[]" multiple size="4">
                        <?php foreach($allowed_languages as $l): ?>
                            <option value="<?=$l?>" <?=(in_array($l, $user_langs))?'selected':''?>><?=$l?></option>
                        <?php endforeach; ?>
                    </select>

                    <label>О себе:</label>
                    <textarea name="biography" rows="3"><?=htmlspecialchars($a['biography'] ?? '')?></textarea>

                    <button type="submit" name="save_admin" class="btn btn-save">Сохранить изменения</button>
                </form>
            </div>
        </div>
    <?php endforeach; ?>
</div>

</body>
</html>
