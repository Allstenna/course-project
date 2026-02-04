<?php
session_start();
require '../db.php';

// index.php (PHP блок сверху)
$search_query = $_GET['q'] ?? '';

// 3. Формируем запрос
$sql = "SELECT * FROM products";
$params = [];
$where_clauses = [];

if (!empty($search_query)) {
    $where_clauses[] = "title LIKE ?";
    $params[] = "%" . $search_query . "%";
}

// Добавляем WHERE, если есть условия
if (!empty($where_clauses)) {
    $sql .= " WHERE " . implode(" AND ", $where_clauses);
}

$sql .= " ORDER BY id DESC";

// 4. Выполняем запрос
$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$products = $stmt->fetchAll();
?>

<!DOCTYPE html>
<html lang="ru" class="vh-auto">
<head>
    <meta charset="UTF-8">
    <title>Главная страница</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>

<!-- Навигация -->
<nav class="navbar navbar-light bg-light px-4 mb-4 shadow-sm">
    <span class="navbar-brand mb-0 h1">Мой Магазин</span>
    <div>
        <?php if (isset($_SESSION['user_id'])): ?>
            <!-- Если вошел -->
            <span class="me-3">Привет!</span>
            <?php if ($_SESSION['user_role'] === 'admin'): ?>
                <a href="admin_panel.php" class="btn btn-outline-danger btn-sm">Админка</a>
                <a href="add_item.php" class="btn btn-success btn-sm">+ Добавить товар</a>
                <a href="admin_orders.php" class="btn btn-primary btn-sm">Список заказов</a>
            <?php endif; ?>
            <a href="profile.php" class="btn btn-primary btn-sm">Личный кабинет</a>
            <a href="logout.php" class="btn btn-dark btn-sm">Выйти</a>
        <?php else: ?>
            <!-- Если гость -->
            <a href="login.php" class="btn btn-primary btn-sm">Войти</a>
            <a href="register.php" class="btn btn-outline-primary btn-sm">Регистрация</a>
        <?php endif; ?>
    </div>
</nav>

<div class="container">
    <h2 class="mb-4">Каталог товаров</h2>
    
        <!-- index.php -->
    <div class="card mb-4 p-3 bg-light">
        <form action="index.php" method="GET" class="row g-3">
            <!-- Поле текстового поиска -->
            <div class="col-md-8">
                <input type="text" name="q" class="form-control" 
                       placeholder="Поиск по названию..." 
                       value="<?= htmlspecialchars($_GET['q'] ?? '') ?>">
            </div>
            
            <!-- Кнопка отправки -->
            <div class="col-md-4">
                <button type="submit" class="btn btn-primary w-100" name="searchBtn">Найти</button>
            </div>
            
            <!-- Ссылка для сброса фильтров -->
            <div class="col-12 text-end">
                <a href="index.php" class="text-muted text-decoration-none small">Сбросить фильтры</a>
            </div>
        </form>
    </div>
    
    <div class="row">
        <?php foreach ($products as $product): ?>
            <div class="col-md-4 mb-4">
                <div class="card h-100 p-2">
                    <!-- Если картинки нет, ставим заглушку -->
                    <?php $img = trim($product['image_url']) ?: 'https://via.placeholder.com/300'; ?>
                    <img src="<?= htmlspecialchars($img) ?>" class="card-img-top" alt="Фото" style="height: 200px; object-fit: cover;">
                    
                    <div class="card-body">
                        <h5 class="card-title"><?= htmlspecialchars($product['title']) ?></h5>
                        <p class="card-text text-truncate"><?= htmlspecialchars($product['description']) ?></p>
                        <p class="card-text fw-bold text-primary"><?= htmlspecialchars($product['price']) ?> ₽</p>
                    </div>
                    <div class="card-footer bg-white border-top-0">
                        <a href="make_order.php?id=<?= (int)$product['id'] ?>" class="btn btn-primary">Купить</a>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>
        
        <?php if (count($products) === 0 && !empty($search_query)): ?>
            <p class="text-muted">Таких товаров нет. Попробуйте изменить поиск.</p>
        <?php endif; ?>
        <?php if (count($products) === 0 && empty($search_query)): ?>
            <p class="text-muted">Товаров пока нет. Зайдите под админом и добавьте их.</p>
        <?php endif; ?>
    </div>
</div>

</body>
</html>