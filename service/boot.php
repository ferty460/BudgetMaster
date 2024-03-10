<?php

session_start();

// PDO
function pdo(): PDO
{
    static $pdo;

    if (!$pdo) {
        $config = include __DIR__ . '/config.php';
        $dsn = 'mysql:dbname=' . $config['db_name'] . ';host=' . $config['db_host'];
        $pdo = new PDO($dsn, $config['db_user'], $config['db_pass']);
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    }

    return $pdo;
}

// flash messages
function flash(?string $message = null)
{
    if ($message) {
        $_SESSION['flash'] = $message;
    } else {
        if (!empty($_SESSION['flash'])) { ?>
            <div style="color: red; text-align: center;">
                <?= $_SESSION['flash'] ?>
            </div>
<?php }
        unset($_SESSION['flash']);
    }
}

function check_auth(): bool
{
    return !!($_SESSION['user_id'] ?? false);
}

// получим все счета пользователя
function getAccountsByUserId($id)
{
    $stmt = pdo()->prepare("SELECT * FROM `accounts` WHERE `user` = :id");
    $stmt->execute(['id' => $id]);
    return $stmt->fetchAll();
}

// получим сумму счетов пользователя
function getAccountsSumByUserId($id)
{
    $stmt = pdo()->prepare("SELECT SUM(balance) FROM accounts WHERE user = :id;");
    $stmt->execute(['id' => $id]);
    return $stmt->fetch();
}

// получим сумму счета в процентах
function getPercentageBalance($accountId)
{
    // Получаем сумму всех балансов счетов
    $stmt = pdo()->prepare('SELECT SUM(balance) as totalBalance FROM accounts');
    $stmt->execute();
    $row = $stmt->fetch();
    $totalBalance = $row['totalBalance'];

    // Получаем баланс счета
    $stmt = pdo()->prepare('SELECT balance FROM accounts WHERE id = :accountId');
    $stmt->execute(['accountId' => $accountId]);
    $row = $stmt->fetch();
    $accountBalance = $row['balance'];

    if ($accountBalance === 0) return 0;

    // Вычисляем процент баланса счета от общей суммы балансов
    $percentage = ($accountBalance / $totalBalance) * 100;

    return round($percentage, 1);
}

function getAllCategoriesWithType($type)
{
    $stmt = pdo()->prepare("SELECT * FROM `categories` WHERE type = :type");
    $stmt->execute(['type' => $type]);
    return $stmt->fetchAll();
}

function getCategoryById($id)
{
    $stmt = pdo()->prepare("SELECT * FROM `categories` WHERE id = :id");
    $stmt->execute(['id' => $id]);
    return $stmt->fetch();
}

function getTodayOperationsWithType($type)
{
    $today = date('Y-m-d');

    $stmt = pdo()->prepare("SELECT * FROM operations WHERE date = :today AND type = :type AND user = :user");
    $stmt->execute(['today' => $today, 'type' => $type, 'user' => $_SESSION['user_id']]);

    $operations = $stmt->fetchAll();

    $totalAmount = array_reduce($operations, function ($carry, $item) {
        return $carry + $item['amount'];
    }, 0);

    foreach ($operations as &$operation) {
        $sumStmt = pdo()->prepare("SELECT SUM(amount) FROM operations WHERE category = :categoryId AND type = :type AND date = :today AND user = :user");
        $sumStmt->execute(['categoryId' => $operation['category'], 'type' => $type, 'today' => $operation['date'], 'user' => $operation['user']]);
        $sumResult = $sumStmt->fetchColumn();

        $categoryStmt = pdo()->prepare("SELECT * FROM categories WHERE id = :categoryId");
        $categoryStmt->execute(['categoryId' => $operation['category']]);
        $categoryResult = $categoryStmt->fetch(PDO::FETCH_ASSOC);

        $result = array_merge($categoryResult, ['total_amount' => $sumResult]);

        $operation['category_info'] = $result;
        $operation['percentage'] = round(($operation['amount'] / $totalAmount) * 100, 1);
    }

    return $operations;
}

function getTodayOperationsWithTypeDistinctCategory($type)
{
    $today = date('Y-m-d');

    $stmt = pdo()->prepare("SELECT * FROM operations WHERE date = :today AND type = :type AND user = :user GROUP BY category");
    $stmt->execute(['today' => $today, 'type' => $type, 'user' => $_SESSION['user_id']]);

    $operations = $stmt->fetchAll();

    $totalAmount = array_reduce($operations, function ($carry, $item) {
        return $carry + $item['amount'];
    }, 0);

    foreach ($operations as &$operation) {
        $sumStmt = pdo()->prepare("SELECT SUM(amount) FROM operations WHERE category = :categoryId AND type = :type AND date = :today AND user = :user");
        $sumStmt->execute(['categoryId' => $operation['category'], 'type' => $operation['type'], 'today' => $operation['date'], 'user' => $operation['user']]);
        $sumResult = $sumStmt->fetchColumn();

        $categoryStmt = pdo()->prepare("SELECT * FROM categories WHERE id = :categoryId");
        $categoryStmt->execute(['categoryId' => $operation['category']]);
        $categoryResult = $categoryStmt->fetch(PDO::FETCH_ASSOC);

        $result = array_merge($categoryResult, ['total_amount' => $sumResult]);

        $operation['category_info'] = $result;
        $operation['percentage'] = round(($operation['amount'] / $totalAmount) * 100, 1);
    }

    return $operations;
}

function getLastWeekOperationsWithType($type)
{
    $oneWeekAgo = date('Y-m-d', strtotime('-1 week'));

    $stmt = pdo()->prepare("SELECT * FROM operations WHERE (date BETWEEN :oneWeekAgo AND CURDATE()) AND type = :type AND user = :user");
    $stmt->execute(['oneWeekAgo' => $oneWeekAgo, 'type' => $type, 'user' => $_SESSION['user_id']]);

    $operations = $stmt->fetchAll();

    $totalAmount = array_reduce($operations, function ($carry, $item) {
        return $carry + $item['amount'];
    }, 0);

    foreach ($operations as &$operation) {
        $sumStmt = pdo()->prepare("SELECT SUM(amount) FROM operations WHERE category = :categoryId AND type = :type AND (date BETWEEN :oneWeekAgo AND CURDATE()) AND user = :user");
        $sumStmt->execute(['categoryId' => $operation['category'], 'type' => $operation['type'], 'oneWeekAgo' => $oneWeekAgo, 'user' => $operation['user']]);
        $sumResult = $sumStmt->fetchColumn();

        $categoryStmt = pdo()->prepare("SELECT * FROM categories WHERE id = :categoryId");
        $categoryStmt->execute(['categoryId' => $operation['category']]);
        $categoryResult = $categoryStmt->fetch(PDO::FETCH_ASSOC);

        $result = array_merge($categoryResult, ['total_amount' => $sumResult]);

        $operation['category_info'] = $result;
        $operation['percentage'] = round(($operation['amount'] / $totalAmount) * 100, 1);
    }

    return $operations;
}

function getLastWeekOperationsWithTypeDistinctCategory($type)
{
    $oneWeekAgo = date('Y-m-d', strtotime('-1 week'));

    $stmt = pdo()->prepare("SELECT * FROM operations WHERE (date BETWEEN :oneWeekAgo AND CURDATE()) AND type = :type AND user = :user GROUP BY category");
    $stmt->execute(['oneWeekAgo' => $oneWeekAgo, 'type' => $type, 'user' => $_SESSION['user_id']]);

    $operations = $stmt->fetchAll();

    $totalAmount = array_reduce($operations, function ($carry, $item) {
        return $carry + $item['amount'];
    }, 0);

    foreach ($operations as &$operation) {
        $sumStmt = pdo()->prepare("SELECT SUM(amount) FROM operations WHERE category = :categoryId AND type = :type AND (date BETWEEN :oneWeekAgo AND CURDATE()) AND user = :user");
        $sumStmt->execute(['categoryId' => $operation['category'], 'type' => $type, 'oneWeekAgo' => $oneWeekAgo, 'user' => $operation['user']]);
        $sumResult = $sumStmt->fetchColumn();

        $categoryStmt = pdo()->prepare("SELECT * FROM categories WHERE id = :categoryId");
        $categoryStmt->execute(['categoryId' => $operation['category']]);
        $categoryResult = $categoryStmt->fetch(PDO::FETCH_ASSOC);

        $result = array_merge($categoryResult, ['total_amount' => $sumResult]);

        $operation['category_info'] = $result;
        $operation['percentage'] = round(($operation['amount'] / $totalAmount) * 100, 1);
    }

    return $operations;
}

function getLastMonthOperationsWithType($type)
{
    $oneWeekAgo = date('Y-m-d', strtotime('-1 month'));

    $stmt = pdo()->prepare("SELECT * FROM operations WHERE (date BETWEEN :oneWeekAgo AND CURDATE()) AND type = :type AND user = :user");
    $stmt->execute(['oneWeekAgo' => $oneWeekAgo, 'type' => $type, 'user' => $_SESSION['user_id']]);

    $operations = $stmt->fetchAll();

    $totalAmount = array_reduce($operations, function ($carry, $item) {
        return $carry + $item['amount'];
    }, 0);

    foreach ($operations as &$operation) {
        $sumStmt = pdo()->prepare("SELECT SUM(amount) FROM operations WHERE category = :categoryId AND type = :type AND (date BETWEEN :oneWeekAgo AND CURDATE()) AND user = :user");
        $sumStmt->execute(['categoryId' => $operation['category'], 'type' => $type, 'oneWeekAgo' => $oneWeekAgo, 'user' => $operation['user']]);
        $sumResult = $sumStmt->fetchColumn();

        $categoryStmt = pdo()->prepare("SELECT * FROM categories WHERE id = :categoryId");
        $categoryStmt->execute(['categoryId' => $operation['category']]);
        $categoryResult = $categoryStmt->fetch(PDO::FETCH_ASSOC);

        $result = array_merge($categoryResult, ['total_amount' => $sumResult]);

        $operation['category_info'] = $result;
        $operation['percentage'] = round(($operation['amount'] / $totalAmount) * 100, 1);
    }

    return $operations;
}

function getLastMonthOperationsWithTypeDistinctCategory($type)
{
    $oneWeekAgo = date('Y-m-d', strtotime('-1 month'));

    $stmt = pdo()->prepare("SELECT * FROM operations WHERE (date BETWEEN :oneWeekAgo AND CURDATE()) AND type = :type AND user = :user GROUP BY category");
    $stmt->execute(['oneWeekAgo' => $oneWeekAgo, 'type' => $type, 'user' => $_SESSION['user_id']]);

    $operations = $stmt->fetchAll();

    $totalAmount = array_reduce($operations, function ($carry, $item) {
        return $carry + $item['amount'];
    }, 0);

    foreach ($operations as &$operation) {
        $sumStmt = pdo()->prepare("SELECT SUM(amount) FROM operations WHERE category = :categoryId AND type = :type AND (date BETWEEN :oneWeekAgo AND CURDATE()) AND user = :user");
        $sumStmt->execute(['categoryId' => $operation['category'], 'type' => $type, 'oneWeekAgo' => $oneWeekAgo, 'user' => $operation['user']]);
        $sumResult = $sumStmt->fetchColumn();

        $categoryStmt = pdo()->prepare("SELECT * FROM categories WHERE id = :categoryId");
        $categoryStmt->execute(['categoryId' => $operation['category']]);
        $categoryResult = $categoryStmt->fetch(PDO::FETCH_ASSOC);

        $result = array_merge($categoryResult, ['total_amount' => $sumResult]);

        $operation['category_info'] = $result;
        $operation['percentage'] = round(($operation['amount'] / $totalAmount) * 100, 1);
    }

    return $operations;
}

function getLastYearOperationsWithType($type)
{
    $oneWeekAgo = date('Y-m-d', strtotime('-1 year'));

    $stmt = pdo()->prepare("SELECT * FROM operations WHERE (date BETWEEN :oneWeekAgo AND CURDATE()) AND type = :type AND user = :user");
    $stmt->execute(['oneWeekAgo' => $oneWeekAgo, 'type' => $type, 'user' => $_SESSION['user_id']]);

    $operations = $stmt->fetchAll();

    $totalAmount = array_reduce($operations, function ($carry, $item) {
        return $carry + $item['amount'];
    }, 0);

    foreach ($operations as &$operation) {
        $sumStmt = pdo()->prepare("SELECT SUM(amount) FROM operations WHERE category = :categoryId AND type = :type AND (date BETWEEN :oneWeekAgo AND CURDATE()) AND user = :user");
        $sumStmt->execute(['categoryId' => $operation['category'], 'type' => $type, 'oneWeekAgo' => $oneWeekAgo, 'user' => $operation['user']]);
        $sumResult = $sumStmt->fetchColumn();

        $categoryStmt = pdo()->prepare("SELECT * FROM categories WHERE id = :categoryId");
        $categoryStmt->execute(['categoryId' => $operation['category']]);
        $categoryResult = $categoryStmt->fetch(PDO::FETCH_ASSOC);

        $result = array_merge($categoryResult, ['total_amount' => $sumResult]);

        $operation['category_info'] = $result;
        $operation['percentage'] = round(($operation['amount'] / $totalAmount) * 100, 1);
    }

    return $operations;
}

function getLastYearOperationsWithTypeDistinctCategory($type)
{
    $oneWeekAgo = date('Y-m-d', strtotime('-1 year'));

    $stmt = pdo()->prepare("SELECT * FROM operations WHERE (date BETWEEN :oneWeekAgo AND CURDATE()) AND type = :type AND user = :user GROUP BY category");
    $stmt->execute(['oneWeekAgo' => $oneWeekAgo, 'type' => $type, 'user' => $_SESSION['user_id']]);

    $operations = $stmt->fetchAll();

    $totalAmount = array_reduce($operations, function ($carry, $item) {
        return $carry + $item['amount'];
    }, 0);

    foreach ($operations as &$operation) {
        $sumStmt = pdo()->prepare("SELECT SUM(amount) FROM operations WHERE category = :categoryId AND type = :type AND (date BETWEEN :oneWeekAgo AND CURDATE()) AND user = :user");
        $sumStmt->execute(['categoryId' => $operation['category'], 'type' => $type, 'oneWeekAgo' => $oneWeekAgo, 'user' => $operation['user']]);
        $sumResult = $sumStmt->fetchColumn();

        $categoryStmt = pdo()->prepare("SELECT * FROM categories WHERE id = :categoryId");
        $categoryStmt->execute(['categoryId' => $operation['category']]);
        $categoryResult = $categoryStmt->fetch(PDO::FETCH_ASSOC);

        $result = array_merge($categoryResult, ['total_amount' => $sumResult]);

        $operation['category_info'] = $result;
        $operation['percentage'] = round(($operation['amount'] / $totalAmount) * 100, 1);
    }

    return $operations;
}

function getLabels($operations)
{
    $labels = array_map(function ($operation) {
        return $operation['category_info']['name'];
    }, $operations);

    return json_encode($labels);
}

function getData($operations)
{
    $labels = array_map(function ($operation) {
        return $operation['category_info']['total_amount'];
    }, $operations);

    return json_encode($labels);
}

function getHighestExpense($type)
{
    $stmt = pdo()->prepare("SELECT * FROM operations WHERE user = :user AND type = :type AND date >= DATE_SUB(CURDATE(), INTERVAL 1 MONTH) ORDER BY amount DESC LIMIT 1");
    $stmt->execute(['user' => $_SESSION['user_id'], 'type' => $type]);
    return $stmt->fetch();
}

function getAllUsers() {
    $stmt = pdo()->prepare("SELECT * FROM users");
    $stmt->execute();
    return $stmt->fetchAll();
}