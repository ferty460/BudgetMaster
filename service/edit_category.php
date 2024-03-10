<?php

require_once 'boot.php';

$tempName = $_FILES['category_image']['tmp_name'];
$imageName = $_FILES['category_image']['name'];
$imageSize = $_FILES['category_image']['size'];
$imageType = $_FILES['category_image']['type'];

if ($imageSize > 100000000) { // 100MB
    die('File size is too large.');
}

$uniqueName = uniqid('', true) . '.' . pathinfo($imageName, PATHINFO_EXTENSION);
$uploadPath = '../assets/images/uploads/' . $uniqueName;

// Перемещение файла в указанную папку
if (move_uploaded_file($tempName, $uploadPath)) {
    $stmt = pdo()->prepare("UPDATE `categories` SET name = :name, image = :image WHERE id = :id");
    $stmt->execute([
        'name' => $_POST['category_name'],
        'image' => $uploadPath,
        'id' => $_POST['category_id'],
    ]);
}

header("Location: ../assets/admin.php");
