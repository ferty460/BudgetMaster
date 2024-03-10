<?php

require_once 'boot.php';

$stmt = pdo()->prepare("DELETE FROM categories WHERE id = :id");
$stmt->execute(['id' => $_POST['id']]);

header("Location: ../assets/admin.php");