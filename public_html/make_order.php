<?php
    session_start();
    require '../db.php';
    
    // 1. Проверка: Вошел ли пользователь?
    if (!isset($_SESSION['user_id'])) {
        die("Сначала войдите в систему! <a href='login.php'>Вход</a>");
    }
    
    // 2. Получаем ID товара из ссылки (например, make_order.php?id=5)
    $product_id = (int)$_GET['id'];
    $user_id = $_SESSION['user_id'];
    
    if ($product_id > 0) {
        $check = $pdo->prepare("SELECT id FROM products WHERE id = ?");
        $check->execute([$product_id]);
        $exists = $check->fetch();
    
        if (!$exists) {
            die("Ошибка: Попытка заказать несуществующий товар! Ваш IP записан.");
        }
    
        // НОВЫЙ КОД ПРОВЕРКИ: нельзя заказывать тот же товар чаще чем раз в 5 минут
        $last_order_check = $pdo->prepare("
            SELECT created_at 
            FROM orders 
            WHERE user_id = ? AND product_id = ? 
            ORDER BY created_at DESC 
            LIMIT 1
        ");
        $last_order_check->execute([$user_id, $product_id]);
        $last_order = $last_order_check->fetch();
    
        if ($last_order) {
            $last_order_time = strtotime($last_order['created_at']);
            $current_time = time();
            $time_diff = $current_time - $last_order_time;
    
            if ($time_diff < 300) { // 300 секунд = 5 минут
                $remaining = 300 - $time_diff;
                die("Вы не можете заказывать этот товар так часто. Попробуйте через " . $remaining . " секунд. <a href='index.php'>Назад</a>");
            }
        }
    
        $stmt = $pdo->prepare("INSERT INTO orders (user_id, product_id) VALUES (?, ?)");
        try {
            $stmt->execute([$user_id, $product_id]);
            echo "Заказ успешно оформлен! Менеджер свяжется с вами. <a href='index.php'>Вернуться</a>";
        } catch (PDOException $e) {
            echo "Ошибка: " . $e->getMessage();
        }
    } else {
        echo "Неверный товар.";
    }
?>