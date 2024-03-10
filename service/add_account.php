<?php

require_once 'boot.php';

// Добавим счет пользователю
$stmt = pdo()->prepare("INSERT INTO `accounts` (`name`, `balance`, `user`) VALUES (:name, :balance, :user)");
$stmt->execute([
    'name' => $_POST['name'],
    'balance' => $_POST['balance'],
    'user' => $_SESSION['user_id'],
]);

header('Location: ../assets/accounts.php');