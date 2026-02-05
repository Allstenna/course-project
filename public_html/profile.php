<?php
// 1. Начинаем сессию и подключаемся к базе
session_start();
require '../db.php';

// 2. Проверка доступа: Если не вошел — отправляем на вход
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

$user_id = $_SESSION['user_id'];

$stmt = $pdo->prepare("SELECT avatar_url FROM users WHERE id = ?");
$stmt->execute([$user_id]);
$user_avatar = $stmt->fetchColumn();

// 3. БЕЗОПАСНЫЙ ЗАПРОС (Anti-IDOR)
// Мы выбираем только те заказы, где user_id совпадает с текущим пользователем.
// Используем JOIN, чтобы получить название товара и цену из таблицы products.
if ($_SESSION['user_role'] === 'admin'){
    $sql = "
        SELECT 
            orders.id as order_id,
            orders.created_at,
            users.email,
            products.title,
            products.price
        FROM orders
        JOIN users ON orders.user_id = users.id
        JOIN products ON orders.product_id = products.id
        ORDER BY orders.id DESC
        
    ";
    $stmt = $pdo->query($sql);
    $orders = $stmt->fetchAll();
}else{
    $sql = "
        SELECT 
            orders.id as order_id, 
            orders.created_at, 
            orders.status, 
            products.title, 
            products.price,
            products.image_url
        FROM orders 
        JOIN products ON orders.product_id = products.id 
        WHERE orders.user_id = ? 
        ORDER BY orders.created_at DESC
    ";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$user_id]);
    $my_orders = $stmt->fetchAll();
}
?>

<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Личный кабинет</title>
    <!-- Подключаем Bootstrap для красоты -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        .btn-opacity {
            opacity: 0;
            transition: opacity 0.3s ease;
        }
        .btn-opacity:hover {
            opacity: 1;
        }
    </style>
</head>
<body class="bg-light">

    <!-- Навигация -->
    <nav class="navbar navbar-expand-lg navbar-dark bg-dark mb-4">
        <div class="container">
            <a class="navbar-brand" href="index.php">Мой Проект</a>
            <div class="d-flex">
                <span class="navbar-text text-white me-3">
                    Вы вошли как: <b><?= htmlspecialchars($_SESSION['user_role'] ?? 'User') ?></b>
                </span>
                <a href="logout.php" class="btn btn-outline-light btn-sm">Выйти</a>
            </div>
        </div>
    </nav>

    <div class="container">
        <div class="row">
            <div class="col-md-3">
                <div class="card shadow-sm">
                    <div class="d-flex flex-column align-items-center">
                        <img src="<?= htmlspecialchars($user_avatar) ?>" class="rounded-circle border border-2 border-dark mt-4" style="object-fit: cover; height: 100px;">
                        <button type="button"
                            class="btn start-50 translate-middle btn-opacity"
                            style="position: absolute; top: 75px; width: 40px; height: 40px; padding: 0; border-radius: 50%;"
                            data-bs-toggle="modal" 
                            data-bs-target="#ModalEditImg">
                            <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" fill="#FFF" class="bi bi-pencil" viewBox="0 0 16 16">
                              <path d="M12.146.146a.5.5 0 0 1 .708 0l3 3a.5.5 0 0 1 0 .708l-10 10a.5.5 0 0 1-.168.11l-5 2a.5.5 0 0 1-.65-.65l2-5a.5.5 0 0 1 .11-.168zM11.207 2.5 13.5 4.793 14.793 3.5 12.5 1.207zm1.586 3L10.5 3.207 4 9.707V10h.5a.5.5 0 0 1 .5.5v.5h.5a.5.5 0 0 1 .5.5v.5h.293zm-9.761 5.175-.106.106-1.528 3.821 3.821-1.528.106-.106A.5.5 0 0 1 5 12.5V12h-.5a.5.5 0 0 1-.5-.5V11h-.5a.5.5 0 0 1-.468-.325"/>
                            </svg>
                        </button>
                    </div>
                    <div class="card-body">
                        <p class="fw-bold text-center fs-5">User</p>
                        <ul class="list-group list-group-flush">
                            <li class="list-group-item"><a class="link-underline link-underline-opacity-0 fst-italic" href="change_pass.php" style="color: #000;">Сменить пароль</a></li>
                         </ul>
                    </div>
                </div>
            </div>
            <div class="col-md-9">
                <div class="card shadow-sm">
                    <div class="card-header bg-white">
                        <?php if($_SESSION['user_role'] === 'admin'): ?>
                            <h2>Все заказы</h2>
                            <a href="index.php" class="btn btn-secondary">На главную</a>
                        <?php else: ?>
                            <h2 class="mb-0">Мои заказы</h2>
                        <?php endif; ?>
                    </div>
                    <div class="card-body">
                        
                        <!-- Проверка: Есть ли заказы вообще? -->
                        <?php if($_SESSION['user_role'] === 'admin'): ?>
                            <div class="table-responsive">
                                <table class="table table-bordered mt-3">
                                    <thead>
                                        <tr>
                                            <th>ID Заказа</th>
                                            <th>Дата</th>
                                            <th>Клиент (Email)</th>
                                            <th>Товар</th>
                                            <th>Цена</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($orders as $order): ?>
                                        <tr>
                                            <td><?= $order['order_id'] ?></td>
                                            <td><?= $order['created_at'] ?></td>
                                            <td><?= htmlspecialchars($order['email']) ?></td>
                                            <td><?= htmlspecialchars($order['title']) ?></td>
                                            <td><?= $order['price'] ?> ₽</td>
                                        </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                        <?php else: ?>
                            <?php if (count($my_orders) > 0): ?>
                                
                                <div class="table-responsive">
                                    <table class="table table-hover align-middle">
                                        <thead class="table-light">
                                            <tr>
                                                <th>№ Заказа</th>
                                                <th>Дата</th>
                                                <th>Товар</th>
                                                <th>Цена</th>
                                                <th>Статус</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php foreach ($my_orders as $order): ?>
                                                <tr>
                                                    <!-- ID заказа -->
                                                    <td>#<?= $order['order_id'] ?></td>
                                                    
                                                    <!-- Дата (форматируем красиво) -->
                                                    <td>
                                                        <?= date('d.m.Y H:i', strtotime($order['created_at'])) ?>
                                                    </td>
                                                    
                                                    <!-- Название товара (защита от XSS) -->
                                                    <td>
                                                        <strong><?= htmlspecialchars($order['title']) ?></strong>
                                                    </td>
                                                    
                                                    <!-- Цена -->
                                                    <td><?= number_format($order['price'], 0, '', ' ') ?> ₽</td>
                                                    
                                                    <!-- Статус с цветным бейджиком -->
                                                    <td>
                                                        <?php 
                                                        // Логика цвета для статуса
                                                        $status_color = 'secondary';
                                                        if ($order['status'] == 'new') $status_color = 'primary';
                                                        if ($order['status'] == 'processing') $status_color = 'warning';
                                                        if ($order['status'] == 'done') $status_color = 'success';
                                                        ?>
                                                        <span class="badge bg-<?= $status_color ?>">
                                                            <?= htmlspecialchars($order['status']) ?>
                                                        </span>
                                                    </td>
                                                </tr>
                                            <?php endforeach; ?>
                                        </tbody>
                                    </table>
                                </div>
    
                            <?php else: ?>
                                <!-- Если заказов нет -->
                                <div class="text-center py-5">
                                    <h4 class="text-muted">Вы еще ничего не заказывали.</h4>
                                    <a href="index.php" class="btn btn-primary mt-3">Перейти в каталог</a>
                                </div>
                            <?php endif; ?>
                        <?php endif; ?>

                    </div>
                </div>
            </div>
            <div class="modal fade" id="ModalEditImg" tabindex="-1" role="dialog">
                <div class="modal-dialog modal-dialog-centered" role="document">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h5 class="modal-title" id="exampleModalLabel">Загрузите изображение</h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                        </div>
                        <form action="upload.php" method="POST" enctype="multipart/form-data">
                            <div class="modal-body">
                                <div class="mb-3">
                                    <label class="form-label">Выберите изображение:</label>
                                    <input type="file" name="file" class="form-control" required>
                                </div>
                            </div>
                            <div class="modal-footer">
                                <button type="submit" class="btn btn-success">Загрузить</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>

</body>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</html>