<?php

require_once 'boot.php';

// Перемещение файла в указанную папку
$stmt = pdo()->prepare("UPDATE `users` SET username = :name, email = :email WHERE id = :id");
$stmt->execute([
    'name' => $_POST['username'],
    'email' => $_POST['email'],
    'id' => $_SESSION['user_id']
]);

header("Location: ../assets/profile.php");