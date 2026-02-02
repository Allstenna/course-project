<?php
session_start(); // Добавьте в самое начало
require '../db.php';

// Генерируем CSRF-токен, если его нет
if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}
?>

<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <title>Сброс пароля</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">

<div class="container mt-5 h-100">
    <div class="h-100 row justify-content-center align-items-center">
        <div class="col-md-6">
            <div class="card shadow">
                <div class="card-header bg-primary text-white">
                    <h4 class="mb-0">Сброс пароля</h4>
                </div>
                <div class="card-body">
                    
                    <?php 
                    // Инициализируем переменные сообщений
                    $errorMsg = $_GET['error'] ?? '';
                    $successMsg = $_GET['success'] ?? '';
                    ?>
                    
                    <!-- Блок вывода сообщений -->
                    <?php if($errorMsg): ?>
                        <div class="alert alert-danger"><?= htmlspecialchars($errorMsg) ?></div>
                    <?php endif; ?>
                    
                    <?php if($successMsg): ?>
                        <div class="alert alert-success"><?= htmlspecialchars($successMsg) ?></div>
                    <?php else: ?>

                    <!-- Сама форма -->
                    <form action="update_profile.php" method="POST">
                        <!-- Скрытое поле с секретным кодом -->
                        <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($_SESSION['csrf_token']); ?>">
                        
                        <!-- ВАЖНО: исправьте name второго поля -->
                        <div class="mb-3">
                            <label class="form-label">Старый пароль</label>
                            <input class="form-control" type="password" name="old_password" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Новый пароль</label>
                            <input class="form-control" type="password" name="password" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Подтверждение пароля</label>
                            <input class="form-control" type="password" name="password_confirm" required>
                        </div>
                        <button class="btn btn-primary w-100" type="submit">Изменить</button>
                    </form>

                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>

</body>
</html>