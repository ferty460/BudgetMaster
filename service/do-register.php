<?php

require_once 'boot.php';

if (empty($_POST['password']) || strlen($_POST['password']) < 6) {
    flash("Пароль должен быть длиннее 6 символов");
    header("Location: {$_SERVER['HTTP_REFERER']}");
    die;
}

// Проверка капчи
if($_POST["captcha_code"] != $_SESSION["captcha_code"]) {
    flash("Вы робот или введите капчу еще раз");
    header("Location: {$_SERVER['HTTP_REFERER']}");
    die;
}

// Проверим, не занят ли email пользователя
$stmt = pdo()->prepare("SELECT * FROM `users` WHERE `email` = :email");
$stmt->execute(['email' => $_POST['email']]);
if ($stmt->rowCount() > 0) {
    flash("Этот email пользователя уже занят");
    header("Location: {$_SERVER['HTTP_REFERER']}"); // Возврат на форму регистрации
    die; 
}

// Добавим пользователя в базу
$stmt = pdo()->prepare("INSERT INTO `users` (`username`, `password`, `email`, `role`) VALUES (:username, :password, :email, :role)");
$stmt->execute([
    'username' => $_POST['username'],
    'email' => $_POST['email'],
    'password' => password_hash($_POST['password'], PASSWORD_DEFAULT),
    'role' => 'user'
]);
$userId = pdo()->lastInsertId();

// Добавим пользователю дефолтный счет
$stmt = pdo()->prepare("INSERT INTO `accounts` (`name`, `balance`, `user`) VALUES (:name, :balance, :user)");
$stmt->execute([
    'name' => "Основной",
    'balance' => 0,
    'user' => $userId
]);

header('Location: ../assets/login.php');