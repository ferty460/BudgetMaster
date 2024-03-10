<?php

require_once 'boot.php';

$role = $_GET['role'];
$id = $_GET['id'];

if ($role !== 'admin') {
  $stmt = pdo()->prepare("UPDATE `users` SET role = :role WHERE id = :id");
  $stmt->execute([
    'role' => 'admin',
    'id' => $id,
  ]);
} else {
  $stmt = pdo()->prepare("UPDATE `users` SET role = :role WHERE id = :id");
  $stmt->execute([
    'role' => 'user',
    'id' => $id,
  ]);
}

header("Location: ../assets/main.php");
