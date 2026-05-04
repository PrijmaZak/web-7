<?php
// ЗАЩИТА: Information Disclosure (Скрытие ошибок)
// Мы оборачиваем критические узлы в try-catch, чтобы пользователь не видел системных путей и паролей.
session_start();
header('Content-Type: text/html; charset=UTF-8');

$host = 'localhost'; $dbname = 'u82352'; $username = 'u82352'; $password = '9562557';

try {
    // ЗАЩИТА: SQL Injection (Использование PDO) 
    // Использование подготовленных запросов (Prepared Statements) исключает внедрение кода в SQL[cite: 2].
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8mb4", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    // Вместо вывода $e->getMessage(), выводим нейтральный текст[cite: 2].
    die("Ошибка БД. Пожалуйста, попробуйте позже.");
}

// НОВАЯ ЧАСТЬ: ГЕНЕРАЦИЯ CSRF-ТОКЕНА
// Генерируем уникальный токен для сессии, чтобы предотвратить подделку запросов[cite: 2].
if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

$allowed_languages = ['Pascal', 'C', 'C++', 'JavaScript', 'PHP', 'Python', 'Java', 'Go'];
$is_logged = !empty($_SESSION['login']);

if ($_SERVER['REQUEST_METHOD'] == 'GET') {
    $messages = [];
    if (isset($_COOKIE['save'])) {
        setcookie('save', '', 100000);
        $messages[] = 'Данные успешно сохранены!';
    }

    $values = [];
    if ($is_logged) {
        // ЗАЩИТА: SQL Injection (Prepared Statements) 
        // Данные пользователя (?) передаются отдельно от тела запроса[cite: 2].
        $stmt = $pdo->prepare("
            SELECT a.* FROM applications a
            JOIN users u ON a.id = u.application_id
            WHERE u.login = ?
        ");
        $stmt->execute([$_SESSION['login']]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($row) {
            $values = $row;
            $stmt = $pdo->prepare("SELECT l.name FROM application_languages al JOIN languages l ON al.language_id = >            $stmt->execute([$row['id']]);
            $values['languages'] = $stmt->fetchAll(PDO::FETCH_COLUMN);
        }
    }

    // ЗАЩИТА: Include (Безопасное подключение) 
    // Мы подключаем файл статично, не используя переменные из URL[cite: 2].
    include('form.php');

} else {
// НОВАЯ ЧАСТЬ: ПРОВЕРКА CSRF-ТОКЕНА 
    // Если токен из формы не совпадает с токеном в сессии, запрос отклоняется[cite: 2].
    if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token']) {
        header('HTTP/1.1 403 Forbidden');
        die('Ошибка безопасности: CSRF-токен не валиден.');
    }

    // НОВАЯ ЧАСТЬ: ВАЛИДАЦИЯ И САНИТИЗАЦИЯ ВХОДА 
    // Очищаем данные перед использованием, применяя "Золотое правило"[cite: 2].
    $fio = trim(strip_tags($_POST['fio'] ?? ''));
    $phone = preg_replace('/[^0-9+]/', '', $_POST['phone'] ?? '');
    $email = filter_var($_POST['email'] ?? '', FILTER_SANITIZE_EMAIL);
    $birth_date = $_POST['birth_date'] ?? '2000-01-01';
    $gender = in_array($_POST['gender'] ?? '', ['male', 'female']) ? $_POST['gender'] : 'male';
    $languages = $_POST['languages'] ?? [];
    $biography = htmlspecialchars($_POST['biography'] ?? ''); // Санитизация для защиты от XSS[cite: 2].
    $contract = isset($_POST['contract']) ? 1 : 0;

    if ($is_logged) {
        $stmt = $pdo->prepare("SELECT application_id FROM users WHERE login = ?");
        $stmt->execute([$_SESSION['login']]);
        $app_id = $stmt->fetchColumn();

        // SQL-запрос полностью защищен плейсхолдерами[cite: 2].
        $stmt = $pdo->prepare("UPDATE applications SET fio=?, phone=?, email=?, birth_date=?, gender=?, biography=?,>        $stmt->execute([$fio, $phone, $email, $birth_date, $gender, $biography, $contract, $app_id]);
    } else {
        $stmt = $pdo->prepare("INSERT INTO applications (fio, phone, email, birth_date, gender, biography, contract)>        $stmt->execute([$fio, $phone, $email, $birth_date, $gender, $biography, $contract]);
        $app_id = $pdo->lastInsertId();

        $login = 'user' . rand(1000, 9999);
        $pass = substr(md5(rand()), 0, 8);
        $pdo->prepare("INSERT INTO users (login, password_hash, password_raw, application_id) VALUES (?, ?, ?, ?)")
            ->execute([$login, md5($pass), $pass, $app_id]);

        $_SESSION['login'] = $login;
    }

    // ЗАЩИТА: SQL Injection при работе со связями
    $pdo->prepare("DELETE FROM application_languages WHERE application_id = ?")->execute([$app_id]);
    foreach ($languages as $lang) {
        if (in_array($lang, $allowed_languages)) { // Проверка по белому списку (защита от Include/Inject)[cite: 2].
            $pdo->prepare("INSERT INTO application_languages (application_id, language_id) VALUES (?, (SELECT id FRO>                ->execute([$app_id, $lang]);
        }
    }

    setcookie('save', '1');
    header('Location: index.php');
}
?>
