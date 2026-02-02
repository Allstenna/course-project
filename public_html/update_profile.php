<?php
session_start();
require '../db.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!hash_equals($_SESSION['csrf_token'], $_POST['csrf_token'])) {
        die("Ошибка безопасности: Неверный CSRF-токен! Запрос отклонен.");
    }
    
    $passOld = $_POST['old_password'];
    $pass = $_POST['password'];
    $passConfirm = $_POST['password_confirm'];
    
    // 1. ПОЛУЧЕНИЕ EMAIL ИЗ СЕССИИ ИЛИ БД
    $email = null;
    if (isset($_SESSION['user_id'])) {
        $user_id = $_SESSION['user_id'];
        $sql = "SELECT email, password_hash FROM users WHERE id = :id";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([':id' => $user_id]);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if ($result) {
            $email = $result['email'];
            $current_password_hash = $result['password_hash'];
        }
    }
    
    // Если email не найден
    if (!$email) {
        die("Ошибка: Не удалось определить пользователя. Возможно, сессия истекла.");
    }

    // 3. ВАЛИДАЦИЯ
    if (!password_verify($passOld, $current_password_hash)) {
        $errorMsg = "Старый пароль указан неверно";
    } elseif (strlen($pass) < 8) {
        $errorMsg = "Ошибка: Пароль должен содержать минимум 8 символов";
    } elseif (empty($passConfirm) || empty($pass)) {
        $errorMsg = "Заполните все поля!";
    } elseif ($pass !== $passConfirm) {
        $errorMsg = "Пароли не совпадают!";
    } else {
        // 4. ХЕШИРОВАНИЕ И СОХРАНЕНИЕ
        
        // Генерируем безопасный хеш
        $hash = password_hash($pass, PASSWORD_DEFAULT);
        
        // ОБНОВЛЯЕМ пароль для конкретного пользователя
        $sql = "UPDATE users SET password_hash = :hash WHERE email = :email";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([':hash' => $hash, ':email' => $email]);
        
        // Очищаем сессию после успешного сброса
        if (isset($_SESSION['reset_email'])) {
            unset($_SESSION['reset_email']);
        }
        
        // Перенаправляем на страницу успеха
        header("Location: update_profile_success.php");
        exit();
    }
}
?>