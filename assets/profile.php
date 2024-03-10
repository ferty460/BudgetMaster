<?php
require_once '../service/boot.php';
$user = null;

if (check_auth()) {
    $stmt = pdo()->prepare("SELECT * FROM `users` WHERE `id` = :id");
    $stmt->execute(['id' => $_SESSION['user_id']]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);
} else {
    header("HTTP/1.1 403 Forbidden");
    header("Location: http://" . $_SERVER['HTTP_HOST'], TRUE, 403);
    exit();
}

$expLastMonthOperations = getLastMonthOperationsWithTypeDistinctCategory('expenses');
$expLastMonthLabels = getLabels($expLastMonthOperations);
$expLastMonthData = getData($expLastMonthOperations);

$incLastMonthOperations = getLastMonthOperationsWithTypeDistinctCategory('income');
$incLastMonthLabels = getLabels($incLastMonthOperations);
$incLastMonthData = getData($incLastMonthOperations);

$accounts = getAccountsByUserId($user['id']);
$balance = getAccountsSumByUserId($user['id']);
$highestExpense = getHighestExpense('expenses');
$highestIncome = getHighestExpense('income');
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="На нашей странице профиля вы можете увидеть важную статистику за месяц, включая ваши доходы, расходы, анализ расходов по категориям и многое другое." />
    <link rel="stylesheet" href="css/style.css" />
    <link rel="stylesheet" href="css/reset.css" />
    <link rel="stylesheet" href="css/media.css" />
    <title>Профиль | БюджетМастер</title>
</head>

<body>
    <div class="wrapper">
        <header class="header">
            <div class="container">
                <div class="header-inner">
                    <div class="logo-block">
                        <a href="../index.php" class="logo-link">
                            <img src="images/logo.svg" alt="logo" class="logo" />
                        </a>
                    </div>

                    <ul class="header-list">
                        <li class="header-item">
                            <a href="my_wallet.php" class="header-link">Кошелек</a>
                        </li>
                        <li class="header-item">
                            <a href="accounts.php" class="header-link">Счета</a>
                        </li>
                        <li class="header-item">
                            <a href="profile.php" class="header-link active-link">Профиль</a>
                        </li>
                        <?php if ($user['role'] === 'admin') { ?>
                            <li class="header-item">
                                <a href="admin.php" class="header-link">Адм панель</a>
                            </li>
                        <?php } ?>
                        <li class="header-item">
                            <a href="../service/do-logout.php" class="header-link">Выйти</a>
                        </li>
                    </ul>

                    <div class="header-profile">
                        <div class="avatar-block">
                            <a href="profile.php" class="profile-link">
                                <img src="images/profile.svg" alt="avatar" class="avatar" />
                            </a>
                        </div>
                        <div class="profile-details">
                            <h5 class="profile-nickname"><?php echo $user['username']; ?></h5>
                            <p class="profile-balance">Остаток: <?php echo $balance[0]; ?> ₽</p>
                        </div>
                    </div>
                </div>
            </div>
        </header>

        <main class="main">
            <div class="container">
                <section class="profile">
                    <div id="expenses-block" class="expenses-block-block flex">
                        <div class="wallet-stat-block">
                            <h3 class="subtitle">Расходы за месяц</h3>
                            <div class="exp-doughnut">
                                <?php if (empty($expLastMonthOperations)) echo '<p class="warning">Расходов нет</p>' ?>
                                <canvas id="expenses-month" <?php if (empty($expLastMonthOperations)) echo 'style="display: none;"' ?>></canvas>
                            </div>
                        </div>
                        <div class="wallet-stat-block">
                            <h3 class="subtitle">Доходы за месяц</h3>
                            <div class="exp-doughnut">
                                <?php if (empty($incLastMonthOperations)) echo '<p class="warning">Расходов нет</p>' ?>
                                <canvas id="income-month" <?php if (empty($incLastMonthOperations)) echo 'style="display: none;"' ?>></canvas>
                            </div>
                        </div>
                    </div>
                    <div class="block">
                        <p class="stat-item-info">Самая большая трата за последний месяц: </p>
                        <?php if ($highestExpense) { ?>
                            <div class="wallet-stat-item">
                                <div class="stat-item-details">
                                    <div class="stat-item-icon-block">
                                        <img src="<?php echo getCategoryById($highestExpense['category'])['image']; ?>" alt="products" class="stat-item-icon">
                                    </div>
                                    <h5 class="stat-item-name"><?php echo getCategoryById($highestExpense['category'])['name']; ?></h5>
                                </div>
                                <p class="stat-item-info">
                                    <span class="stat-item-percentages"><?php echo $highestExpense['date']; ?></span> | <span class="stat-item-result"><?php echo $highestExpense['amount']; ?> ₽</span>
                                </p>
                            </div>
                        <?php } else echo '<p>Операций не производилось</p>' ?>
                    </div>
                    <div class="block">
                        <p class="stat-item-info">Самый большой доход за последний месяц: </p>
                        <?php if ($highestIncome) { ?>
                            <div class="wallet-stat-item">
                                <div class="stat-item-details">
                                    <div class="stat-item-icon-block">
                                        <img src="<?php echo getCategoryById($highestIncome['category'])['image']; ?>" alt="products" class="stat-item-icon">
                                    </div>
                                    <h5 class="stat-item-name"><?php echo getCategoryById($highestIncome['category'])['name']; ?></h5>
                                </div>
                                <p class="stat-item-info">
                                    <span class="stat-item-percentages"><?php echo $highestIncome['date']; ?></span> | <span class="stat-item-result"><?php echo $highestIncome['amount']; ?> ₽</span>
                                </p>
                            </div>
                        <?php } else echo '<p>Операций не производилось</p>' ?>
                    </div>
                    <form action="../service/edit_profile.php" method="post" class="register-form">
                        <h3 class="title">Редактирование профиля</h3>
                        <div class="form-item">
                            <label for="username" class="form-label">Ваше имя:</label>
                            <input name="username" type="text" id="username" value="<?php echo $user['username'] ?>" class="form-input">
                        </div>
                        <div class="form-item">
                            <label for="email" class="form-label">Ваша электронная почта:</label>
                            <input name="email" type="email" id="email" value="<?php echo $user['email'] ?>" class="form-input">
                        </div>
                        <input type="submit" value="Редактировать" class="form-submit">
                    </form>
                </section>
            </div>
        </main>

        <footer class="footer">
            <div class="container">
                <div class="footer-inner">
                    <div class="logo-block">
                        <a href="../index.php" class="logo-link">
                            <img src="images/logo.svg" alt="logo" class="logo" />
                        </a>
                    </div>
                </div>
            </div>
        </footer>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script>
        const expToday = document.getElementById('expenses-month');
        new Chart(expToday, {
            type: 'bar',
            data: {
                labels: <?php echo $expLastMonthLabels; ?>,
                datasets: [{
                    label: '# Категории',
                    data: <?php echo $expLastMonthData; ?>,
                    backgroundColor: [
                        'rgb(255, 99, 132)',
                        'rgb(54, 162, 235)',
                        'rgb(255, 205, 86)',
                        'rgb(86, 255, 114)',
                        'rgb(167, 212, 255)',
                        'rgb(255, 246, 167)',
                        'rgb(255, 199, 167)'
                    ],
                    borderWidth: 1
                }]
            },
            options: {
                scales: {
                    y: {
                        beginAtZero: true
                    }
                }
            }
        });

        const incToday = document.getElementById('income-month');
        new Chart(incToday, {
            type: 'bar',
            data: {
                labels: <?php echo $incLastMonthLabels; ?>,
                datasets: [{
                    label: '# Категории',
                    data: <?php echo $incLastMonthData; ?>,
                    backgroundColor: [
                        'rgb(255, 99, 132)',
                        'rgb(54, 162, 235)',
                        'rgb(255, 205, 86)',
                        'rgb(86, 255, 114)',
                        'rgb(167, 212, 255)',
                        'rgb(255, 246, 167)',
                        'rgb(255, 199, 167)'
                    ],
                    borderWidth: 1
                }]
            },
            options: {
                scales: {
                    y: {
                        beginAtZero: true
                    }
                }
            }
        });
    </script>
</body>

</html>