<?php
session_start();
require '../db.php';
require 'check_admin.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // 1. Проверка токена
    if (!hash_equals($_SESSION['csrf_token'], $_POST['csrf_token'])) {
        die("CSRF Attack blocked");
    }

    // 2. Удаление
    $id = (int)$_POST['id'];
    $stmt = $pdo->prepare("DELETE FROM products WHERE id = ?");
    $stmt->execute([$id]);
    
    header("Location: index.php");
}
?>