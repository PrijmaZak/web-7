<!DOCTYPE html>
<html lang="ru">
<head>
  <meta charset="UTF-8">
  <title>Анкета</title>
  <style>
    body { font-family: sans-serif; background: #f7fafc; display: flex; justify-content: center; padding: 20px; }
    .card { background: white; padding: 30px; border-radius: 8px; box-shadow: 0 4px 6px rgba(0,0,0,0.1); width: 440px; }
    input, select, textarea { width: 100%; margin-bottom: 15px; padding: 10px; border: 1px solid #e2e8f0; border-radius: 4px; box-sizing: border-box; }
    .btn-main { background: #5a67d8; color: white; border: none; padding: 12px; width: 100%; cursor: pointer; border-radius: 4px; font-weight: bold; }
    .msg-ok { background: #f0fff4; color: #276749; padding: 10px; margin-bottom: 15px; border: 1px solid #c6f6d5; border-radius: 4px; }
    .nav { margin-top: 25px; border-top: 1px solid #edf2f7; padding-top: 15px; display: flex; flex-direction: column; gap: 10px; text-align: center; }
    .link { color: #5a67d8; text-decoration: none; font-size: 14px; font-weight: bold; }
  </style>
</head>
<body>
<div class="card">
  <h2>Анкета</h2>

  <?php if (!empty($messages)) foreach($messages as $m) echo "<div class='msg-ok'>$m</div>"; ?>

  <?php if (!$is_logged && !empty($_COOKIE['login'])): ?>
    <div class="msg-ok" style="background:#fffaf0; border-color:#feebc8; color:#9c4221;">
      Новый аккаунт: <b><?= htmlspecialchars($_COOKIE['login']) ?></b> / Пароль: <b><?= htmlspecialchars($_COOKIE['pass']) ?></b>
    </div>
  <?php endif; ?>

  <form method="POST">
    <input type="text" name="fio" placeholder="ФИО" value="<?= htmlspecialchars($values['fio'] ?? '') ?>" required>
    <input type="tel" name="phone" placeholder="Телефон" value="<?= htmlspecialchars($values['phone'] ?? '') ?>">
    <input type="email" name="email" placeholder="E-mail" value="<?= htmlspecialchars($values['email'] ?? '') ?>">
    <input type="date" name="birth_date" value="<?= htmlspecialchars($values['birth_date'] ?? '') ?>">
    
    <select name="gender">
      <option value="male" <?= ($values['gender'] ?? '') == 'male' ? 'selected' : '' ?>>Мужской</option>
      <option value="female" <?= ($values['gender'] ?? '') == 'female' ? 'selected' : '' ?>>Женский</option>
    </select>

    <select name="languages[]" multiple size="4">
      <?php foreach ($allowed_languages as $l): ?>
        <option value="<?= $l ?>" <?= (isset($values['languages']) && in_array($l, $values['languages'])) ? 'selected' : '' ?>><?= $l ?></option>
      <?php endforeach; ?>
    </select>

    <textarea name="biography" placeholder="О себе..."><?= htmlspecialchars($values['biography'] ?? '') ?></textarea>
    
    <label style="font-size: 13px;"><input type="checkbox" name="contract" style="width:auto" required checked> Я принимаю условия соглашения</label>

    <button type="submit" class="btn-main"><?= $is_logged ? 'Сохранить изменения' : 'Зарегистрироваться' ?></button>
  </form>

  <div class="nav">
    <?php if ($is_logged): ?>
      <span>Вы вошли как: <b><?= $_SESSION['login'] ?></b></span>
      <a href="logout.php" class="link">Выйти (Новая регистрация)</a>
    <?php else: ?>
      <a href="login.php" class="link">У меня есть аккаунт (Вход)</a>
      <a href="logout.php" class="link" style="color:#718096">Очистить и зарегистрироваться</a>
    <?php endif; ?>
    <a href="admin.php" class="link" style="color:#a0aec0">Админ-панель</a>
  </div>
</div>
</body>
</html>
