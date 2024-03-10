<?php

require_once 'boot.php';

$category = substr($_POST['category'], 3);

$type = $_POST['action'];
$amount = $_POST['amount'];
$account = $_POST['account'];

// Получаем текущий баланс счета
$stmt = pdo()->prepare('SELECT balance FROM accounts WHERE id = :account');
$stmt->execute(['account' => $account]);
$row = $stmt->fetch();
$currentBalance = $row['balance'];

if ($_POST['action'] === 'expenses' && $amount > $currentBalance) {
    flash("Ошибка! У вас недостаточно средств.");
    header("Location: {$_SERVER['HTTP_REFERER']}");
    die();
}

// Добавим операцию для счета
$stmt = pdo()->prepare("INSERT INTO `operations` (`type`, `amount`, `date`, `comment`, `account`, `category`, `user`) VALUES (:type, :amount, :date, :comment, :account, :category, :user)");
$stmt->execute([
    'type' => $_POST['action'],
    'amount' => $_POST['amount'],
    'date' => $_POST['operation-date'],
    'comment' => $_POST['comment'],
    'account' => $_POST['account'],
    'category' => $category,
    'user' => $_SESSION['user_id']
]);

// Изменяем баланс счета в зависимости от типа операции
if ($type === 'income') {
    $newBalance = $currentBalance + $amount;
} else {
    $newBalance = $currentBalance - $amount;
}

// Обновляем баланс счета
$stmt = pdo()->prepare('UPDATE accounts SET balance = :balance WHERE id = :account');
$stmt->execute(['balance' => $newBalance, 'account' => $account]);

header('Location: ../assets/my_wallet.php');