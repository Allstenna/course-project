<?php
require '../db.php';
require 'check_admin.php'; // Защита!

$id = (int)$_GET['id'];
$stmt = $pdo->prepare("SELECT * FROM products WHERE id = ?");
$stmt->execute([$id]);
$product = $stmt->fetch();

if (!$product) die("Товар не найден");

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $sql = "UPDATE products SET title = ?, price = ? WHERE id = ?";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$_POST['title'], $_POST['price'], $id]);
    echo "Обновлено!";
}
?>

<!-- ВАЖНО: В value подставляем старые данные -->

<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <title>Редактировать товар</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="p-4">
    <div class="container">
        <h1>Редактирование товара</h1>
        <a href="index.php" class="btn btn-secondary mb-3">← На главную</a>
        
        <?= $message ?>

        <form method="POST" class="card p-4 shadow-sm">
            <div class="mb-3">
                <label>Название товара:</label>
                <input type="text" class="form-control" name="title" value="<?= htmlspecialchars($product['title']) ?>">
            </div>
            
            <div class="mb-3">
                <label>Цена (руб):</label>
                <input type="number" class="form-control" name="price" value="<?= $product['price'] ?>">
            </div>

            <button type="submit"  class="btn btn-success">Обновить</button>
        </form>
    </div>
</body>
</html>