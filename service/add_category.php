<?php

require_once 'boot.php';

$categoryName = $_POST['category_name'];
$categoryType = $_POST['action'];
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
  $stmt = pdo()->prepare("INSERT INTO categories (type, name, image) VALUES (:type, :name, :image)");
  $stmt->execute([
    'type' => $categoryType,
    'name' => $categoryName,
    'image' => $uploadPath
  ]);
}

header('Location: ../assets/admin.php');
