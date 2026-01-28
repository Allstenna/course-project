<?php
session_start();
$_SESSION = array(); // или $_SESSION = array() для очистки всех данных сессии
session_destroy();
header('Location: login.php');
?>