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

$balance = getAccountsSumByUserId($user['id']);
$expTodayOperations = getTodayOperationsWithType('expenses');
$incTodayOperations = getTodayOperationsWithType('income');
$expLastWeekOperations = getLastWeekOperationsWithType('expenses');
$incLastWeekOperations = getLastWeekOperationsWithType('income');
$expLastMonthOperations = getLastMonthOperationsWithType('expenses');
$incLastMonthOperations = getLastMonthOperationsWithType('income');
$expLastYearOperations = getLastYearOperationsWithType('expenses');
$incLastYearOperations = getLastYearOperationsWithType('income');

$expTodayOperationsDistinct = getTodayOperationsWithTypeDistinctCategory('expenses');
$incTodayOperationsDistinct = getTodayOperationsWithTypeDistinctCategory('income');
$expLastWeekOperationsDistinct = getLastWeekOperationsWithTypeDistinctCategory('expenses');
$incLastWeekOperationsDistinct = getLastWeekOperationsWithTypeDistinctCategory('income');
$expLastMonthOperationsDistinct = getLastMonthOperationsWithTypeDistinctCategory('expenses');
$incLastMonthOperationsDistinct = getLastMonthOperationsWithTypeDistinctCategory('income');
$expLastYearOperationsDistinct = getLastYearOperationsWithTypeDistinctCategory('expenses');
$incLastYearOperationsDistinct = getLastYearOperationsWithTypeDistinctCategory('income');

$expTodayLabels = getLabels($expTodayOperationsDistinct);
$incTodayLabels = getLabels($incTodayOperationsDistinct);
$expLastWeekLabels = getLabels($expLastWeekOperationsDistinct);
$incLastWeekLabels = getLabels($incLastWeekOperationsDistinct);
$expLastMonthLabels = getLabels($expLastMonthOperationsDistinct);
$incLastMonthLabels = getLabels($incLastMonthOperationsDistinct);
$expLastYearLabels = getLabels($expLastYearOperationsDistinct);
$incLastYearLabels = getLabels($incLastYearOperationsDistinct);

$expTodayData = getData($expTodayOperationsDistinct);
$incTodayData = getData($incTodayOperationsDistinct);
$expLastWeekData = getData($expLastWeekOperationsDistinct);
$incLastWeekData = getData($incLastWeekOperationsDistinct);
$expLastMonthData = getData($expLastMonthOperationsDistinct);
$incLastMonthData = getData($incLastMonthOperationsDistinct);
$expLastYearData = getData($expLastYearOperationsDistinct);
$incLastYearData = getData($incLastYearOperationsDistinct);
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="Отслеживайте свои расходы и доходы за день, неделю, месяц и год на нашем сервисе для учета доходов и расходов. Добавляйте новые операции прямо с этой страницы." />
    <link rel="stylesheet" href="css/style.css" />
    <link rel="stylesheet" href="css/reset.css" />
    <link rel="stylesheet" href="css/media.css" />
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <title>Мой кошелек | БюджетМастер</title>
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
                            <a href="my_wallet.php" class="header-link active-link">Кошелек</a>
                        </li>
                        <li class="header-item">
                            <a href="accounts.php" class="header-link">Счета</a>
                        </li>
                        <li class="header-item">
                            <a href="profile.php" class="header-link">Профиль</a>
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
            <section class="wallet-stat">
                <div class="container">
                    <p class="title">Итого:</p>
                    <p class="title"><?php echo $balance[0]; ?> ₽</p>
                    <div class="action-switch">
                        <span class="expenses active-action" id="expenses-btn">Расходы</span>
                        <span class="income" id="income-btn">Доходы</span>
                    </div>
                    <form action="add_operation.php" method="get" class="get-form" id="expenses-form">
                        <input type="hidden" name="action" value="expenses">
                        <input type="submit" value="Добавить операцию по расходам" class="form-submit">
                    </form>
                    <form action="add_operation.php" method="get" class="get-form" id="income-form">
                        <input type="hidden" name="action" value="income">
                        <input type="submit" value="Добавить операцию по доходам" class="form-submit">
                    </form>

                    <!-- Блок с расходами -->
                    <div id="expenses-block" class="expenses-block-block">
                        <div class="wallet-stat-block">
                            <ul class="time-switch">
                                <li class="time-item exp-item active-action" data-canvas="expenses-today1" data-stat="exp-stat-today">Сегодня</li>
                                <li class="time-item exp-item" data-canvas="expenses-week1" data-stat="exp-stat-week">Неделя</li>
                                <li class="time-item exp-item" data-canvas="expenses-month1" data-stat="exp-stat-month">Месяц</li>
                                <li class="time-item exp-item" data-canvas="expenses-year1" data-stat="exp-stat-year">Год</li>
                            </ul>
                            <div id="expenses-today1" class="exp-doughnut hide show">
                                <?php if (empty($expTodayOperations)) echo '<p class="warning">Расходов нет</p>' ?>
                                <canvas id="expenses-today" class="wallet-graphics" <?php if (empty($expTodayOperations)) echo 'style="display: none;"' ?>></canvas>
                            </div>
                            <div id="expenses-week1" class="exp-doughnut hide">
                                <?php if (empty($expLastWeekOperations)) echo '<p class="warning">Расходов нет</p>' ?>
                                <canvas id="expenses-week" class="wallet-graphics" <?php if (empty($expLastWeekOperations)) echo 'style="display: none;"' ?>></canvas>
                            </div>
                            <div id="expenses-month1" class="exp-doughnut hide">
                                <?php if (empty($expLastMonthOperations)) echo '<p class="warning">Расходов нет</p>' ?>
                                <canvas id="expenses-month" class="wallet-graphics" <?php if (empty($expLastMonthOperations)) echo 'style="display: none;"' ?>></canvas>
                            </div>
                            <div id="expenses-year1" class="exp-doughnut hide">
                                <?php if (empty($expLastYearOperations)) echo '<p class="warning">Расходов нет</p>' ?>
                                <canvas id="expenses-year" class="wallet-graphics" <?php if (empty($expLastMonthOperations)) echo 'style="display: none;"' ?>></canvas>
                            </div>
                        </div>

                        <!-- Расходы за сегодня -->
                        <div class="wallet-stat-list exp-stat-list active-canvas" id="exp-stat-today">
                            <h3 class="title">Расходы за сегодня</h3>
                            <?php if (empty($expTodayOperations)) echo '<p class="warning">Расходов нет</p>' ?>
                            <?php foreach ($expTodayOperations as $operation) { ?>
                                <div class="wallet-stat-item">
                                    <div class="stat-item-details">
                                        <div class="stat-item-icon-block">
                                            <img src="<?php echo $operation['category_info']['image']; ?>" alt="products" class="stat-item-icon">
                                        </div>
                                        <h5 class="stat-item-name"><?php echo $operation['category_info']['name']; ?></h5>
                                    </div>
                                    <p class="stat-item-info">
                                        <span class="stat-item-percentages"><?php echo $operation['percentage']; ?> %</span> | <span class="stat-item-result"><?php echo $operation['amount']; ?> ₽</span>
                                    </p>
                                </div>
                            <?php } ?>
                        </div>

                        <!-- Расходы за неделю -->
                        <div class="wallet-stat-list exp-stat-list" id="exp-stat-week">
                            <h3 class="title">Расходы за неделю</h3>
                            <?php if (empty($expLastWeekOperations)) echo '<p class="warning">Расходов нет</p>' ?>
                            <?php foreach ($expLastWeekOperations as $operation) { ?>
                                <div class="wallet-stat-item">
                                    <div class="stat-item-details">
                                        <div class="stat-item-icon-block">
                                            <img src="<?php echo $operation['category_info']['image']; ?>" alt="products" class="stat-item-icon">
                                        </div>
                                        <h5 class="stat-item-name"><?php echo $operation['category_info']['name']; ?></h5>
                                    </div>
                                    <p class="stat-item-info">
                                        <span class="stat-item-percentages"><?php echo $operation['percentage']; ?> %</span> | <span class="stat-item-result"><?php echo $operation['amount']; ?> ₽</span>
                                    </p>
                                </div>
                            <?php } ?>
                        </div>

                        <!-- Расходы за месяц -->
                        <div class="wallet-stat-list exp-stat-list" id="exp-stat-month">
                            <h3 class="title">Расходы за месяц</h3>
                            <?php if (empty($expLastMonthOperations)) echo '<p class="warning">Расходов нет</p>' ?>
                            <?php foreach ($expLastMonthOperations as $operation) { ?>
                                <div class="wallet-stat-item">
                                    <div class="stat-item-details">
                                        <div class="stat-item-icon-block">
                                            <img src="<?php echo $operation['category_info']['image']; ?>" alt="products" class="stat-item-icon">
                                        </div>
                                        <h5 class="stat-item-name"><?php echo $operation['category_info']['name']; ?></h5>
                                    </div>
                                    <p class="stat-item-info">
                                        <span class="stat-item-percentages"><?php echo $operation['percentage']; ?> %</span> | <span class="stat-item-result"><?php echo $operation['amount']; ?> ₽</span>
                                    </p>
                                </div>
                            <?php } ?>
                        </div>

                        <!-- Расходы за год -->
                        <div class="wallet-stat-list exp-stat-list" id="exp-stat-year">
                            <h3 class="title">Расходы за год</h3>
                            <?php if (empty($expLastYearOperations)) echo '<p class="warning">Расходов нет</p>' ?>
                            <?php foreach ($expLastYearOperations as $operation) { ?>
                                <div class="wallet-stat-item">
                                    <div class="stat-item-details">
                                        <div class="stat-item-icon-block">
                                            <img src="<?php echo $operation['category_info']['image']; ?>" alt="products" class="stat-item-icon">
                                        </div>
                                        <h5 class="stat-item-name"><?php echo $operation['category_info']['name']; ?></h5>
                                    </div>
                                    <p class="stat-item-info">
                                        <span class="stat-item-percentages"><?php echo $operation['percentage']; ?> %</span> | <span class="stat-item-result"><?php echo $operation['amount']; ?> ₽</span>
                                    </p>
                                </div>
                            <?php } ?>
                        </div>
                    </div>

                    <div id="income-block" class="income-block">
                        <div class="wallet-stat-block">
                            <ul class="time-switch">
                                <li class="time-item inc-item active-action" data-canvas="income-today1" data-stat="inc-stat-today">Сегодня</li>
                                <li class="time-item inc-item" data-canvas="income-week1" data-stat="inc-stat-week">Неделя</li>
                                <li class="time-item inc-item" data-canvas="income-month1" data-stat="inc-stat-month">Месяц</li>
                                <li class="time-item inc-item" data-canvas="income-year1" data-stat="inc-stat-year">Год</li>
                            </ul>
                            <div id="income-today1" class="inc-doughnut hide show">
                                <?php if (empty($incTodayOperations)) echo '<p class="warning">Доходов нет</p>' ?>
                                <canvas id="income-today" class="wallet-graphics" <?php if (empty($incTodayOperations)) echo 'style="display: none;"' ?>></canvas>
                            </div>
                            <div id="income-week1" class="inc-doughnut hide">
                                <?php if (empty($incLastWeekOperations)) echo '<p class="warning">Доходов нет</p>' ?>
                                <canvas id="income-week" class="wallet-graphics" <?php if (empty($incLastWeekOperations)) echo 'style="display: none;"' ?>></canvas>
                            </div>
                            <div id="income-month1" class="inc-doughnut hide">
                                <?php if (empty($incLastMonthOperations)) echo '<p class="warning">Доходов нет</p>' ?>
                                <canvas id="income-month" class="wallet-graphics" <?php if (empty($incLastMonthOperations)) echo 'style="display: none;"' ?>></canvas>
                            </div>
                            <div id="income-year1" class="inc-doughnut hide">
                                <?php if (empty($incLastYearOperations)) echo '<p class="warning">Доходов нет</p>' ?>
                                <canvas id="income-year" class="wallet-graphics" <?php if (empty($incLastYearOperations)) echo 'style="display: none;"' ?>></canvas>
                            </div>
                        </div>

                        <!-- Доходы за сегодня -->
                        <div class="wallet-stat-list inc-stat-list active-canvas" id="inc-stat-today">
                            <h3 class="title">Доходы за сегодня</h3>
                            <?php if (empty($incTodayOperations)) echo '<p class="warning">Расходов нет</p>' ?>
                            <?php foreach ($incTodayOperations as $operation) { ?>
                                <div class="wallet-stat-item">
                                    <div class="stat-item-details">
                                        <div class="stat-item-icon-block">
                                            <img src="<?php echo $operation['category_info']['image']; ?>" alt="products" class="stat-item-icon">
                                        </div>
                                        <h5 class="stat-item-name"><?php echo $operation['category_info']['name']; ?></h5>
                                    </div>
                                    <p class="stat-item-info">
                                        <span class="stat-item-percentages"><?php echo $operation['percentage']; ?> %</span> | <span class="stat-item-result"><?php echo $operation['amount']; ?> ₽</span>
                                    </p>
                                </div>
                            <?php } ?>
                        </div>

                        <!-- Доходы за неделю -->
                        <div class="wallet-stat-list inc-stat-list" id="inc-stat-week">
                            <h3 class="title">Доходы за неделю</h3>
                            <?php if (empty($incLastWeekOperations)) echo '<p class="warning">Расходов нет</p>' ?>
                            <?php foreach ($incLastWeekOperations as $operation) { ?>
                                <div class="wallet-stat-item">
                                    <div class="stat-item-details">
                                        <div class="stat-item-icon-block">
                                            <img src="<?php echo $operation['category_info']['image']; ?>" alt="products" class="stat-item-icon">
                                        </div>
                                        <h5 class="stat-item-name"><?php echo $operation['category_info']['name']; ?></h5>
                                    </div>
                                    <p class="stat-item-info">
                                        <span class="stat-item-percentages"><?php echo $operation['percentage']; ?> %</span> | <span class="stat-item-result"><?php echo $operation['amount']; ?> ₽</span>
                                    </p>
                                </div>
                            <?php } ?>
                        </div>

                        <!-- Доходы за месяц -->
                        <div class="wallet-stat-list inc-stat-list" id="inc-stat-month">
                            <h3 class="title">Доходы за месяц</h3>
                            <?php if (empty($incLastMonthOperations)) echo '<p class="warning">Расходов нет</p>' ?>
                            <?php foreach ($incLastMonthOperations as $operation) { ?>
                                <div class="wallet-stat-item">
                                    <div class="stat-item-details">
                                        <div class="stat-item-icon-block">
                                            <img src="<?php echo $operation['category_info']['image']; ?>" alt="products" class="stat-item-icon">
                                        </div>
                                        <h5 class="stat-item-name"><?php echo $operation['category_info']['name']; ?></h5>
                                    </div>
                                    <p class="stat-item-info">
                                        <span class="stat-item-percentages"><?php echo $operation['percentage']; ?> %</span> | <span class="stat-item-result"><?php echo $operation['amount']; ?> ₽</span>
                                    </p>
                                </div>
                            <?php } ?>
                        </div>

                        <!-- Доходы за год -->
                        <div class="wallet-stat-list inc-stat-list" id="inc-stat-year">
                            <h3 class="title">Доходы за год</h3>
                            <?php if (empty($incLastYearOperations)) echo '<p class="warning">Расходов нет</p>' ?>
                            <?php foreach ($incLastYearOperations as $operation) { ?>
                                <div class="wallet-stat-item">
                                    <div class="stat-item-details">
                                        <div class="stat-item-icon-block">
                                            <img src="<?php echo $operation['category_info']['image']; ?>" alt="products" class="stat-item-icon">
                                        </div>
                                        <h5 class="stat-item-name"><?php echo $operation['category_info']['name']; ?></h5>
                                    </div>
                                    <p class="stat-item-info">
                                        <span class="stat-item-percentages"><?php echo $operation['percentage']; ?> %</span> | <span class="stat-item-result"><?php echo $operation['amount']; ?> ₽</span>
                                    </p>
                                </div>
                            <?php } ?>
                        </div>
                    </div>
                </div>
            </section>
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

        <!-- <script src="js/wallet-doughnut.js"></script> -->
        <script src="js/action-switch.js"></script>
        <script>
            const expToday = document.getElementById('expenses-today');
            new Chart(expToday, {
                type: 'doughnut',
                data: {
                    labels: <?php echo $expTodayLabels; ?>,
                    datasets: [{
                        data: <?php echo $expTodayData; ?>,
                        backgroundColor: [
                            'rgb(255, 99, 132)',
                            'rgb(54, 162, 235)',
                            'rgb(255, 205, 86)',
                            'rgb(86, 255, 114)',
                            'rgb(167, 212, 255)',
                            'rgb(255, 246, 167)',
                            'rgb(255, 199, 167)'
                        ],
                        hoverOffset: 4
                    }]
                }
            });

            const expWeek = document.getElementById('expenses-week');
            new Chart(expWeek, {
                type: 'doughnut',
                data: {
                    labels: <?php echo $expLastWeekLabels; ?>,
                    datasets: [{
                        data: <?php echo $expLastWeekData; ?>,
                        backgroundColor: [
                            'rgb(255, 99, 132)',
                            'rgb(54, 162, 235)',
                            'rgb(255, 205, 86)',
                            'rgb(86, 255, 114)',
                            'rgb(167, 212, 255)',
                            'rgb(255, 246, 167)',
                            'rgb(255, 199, 167)'
                        ],
                        hoverOffset: 4
                    }]
                }
            });

            const expMonth = document.getElementById('expenses-month');
            new Chart(expMonth, {
                type: 'doughnut',
                data: {
                    labels: <?php echo $expLastMonthLabels; ?>,
                    datasets: [{
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
                        hoverOffset: 4
                    }]
                }
            });

            const expYear = document.getElementById('expenses-year');
            new Chart(expYear, {
                type: 'doughnut',
                data: {
                    labels: <?php echo $expLastYearLabels; ?>,
                    datasets: [{
                        data: <?php echo $expLastYearData; ?>,
                        backgroundColor: [
                            'rgb(255, 99, 132)',
                            'rgb(54, 162, 235)',
                            'rgb(255, 205, 86)',
                            'rgb(86, 255, 114)',
                            'rgb(167, 212, 255)',
                            'rgb(255, 246, 167)',
                            'rgb(255, 199, 167)'
                        ],
                        hoverOffset: 4
                    }]
                }
            });

            const incToday = document.getElementById('income-today');
            new Chart(incToday, {
                type: 'doughnut',
                data: {
                    labels: <?php echo $incTodayLabels; ?>,
                    datasets: [{
                        data: <?php echo $incTodayData; ?>,
                        backgroundColor: [
                            'rgb(255, 99, 132)',
                            'rgb(54, 162, 235)',
                            'rgb(255, 205, 86)',
                            'rgb(86, 255, 114)',
                            'rgb(167, 212, 255)',
                            'rgb(255, 246, 167)',
                            'rgb(255, 199, 167)'
                        ],
                        hoverOffset: 4
                    }]
                }
            });

            const incWeek = document.getElementById('income-week');
            new Chart(incWeek, {
                type: 'doughnut',
                data: {
                    labels: <?php echo $incLastWeekLabels; ?>,
                    datasets: [{
                        data: <?php echo $incLastWeekData; ?>,
                        backgroundColor: [
                            'rgb(255, 99, 132)',
                            'rgb(54, 162, 235)',
                            'rgb(255, 205, 86)',
                            'rgb(86, 255, 114)',
                            'rgb(167, 212, 255)',
                            'rgb(255, 246, 167)',
                            'rgb(255, 199, 167)'
                        ],
                        hoverOffset: 4
                    }]
                }
            });

            const incMonth = document.getElementById('income-month');
            new Chart(incMonth, {
                type: 'doughnut',
                data: {
                    labels: <?php echo $incLastMonthLabels; ?>,
                    datasets: [{
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
                        hoverOffset: 4
                    }]
                }
            });

            const incYear = document.getElementById('income-year');
            new Chart(incYear, {
                type: 'doughnut',
                data: {
                    labels: <?php echo $incLastYearLabels; ?>,
                    datasets: [{
                        data: <?php echo $incLastYearData; ?>,
                        backgroundColor: [
                            'rgb(255, 99, 132)',
                            'rgb(54, 162, 235)',
                            'rgb(255, 205, 86)',
                            'rgb(86, 255, 114)',
                            'rgb(167, 212, 255)',
                            'rgb(255, 246, 167)',
                            'rgb(255, 199, 167)'
                        ],
                        hoverOffset: 4
                    }]
                }
            });
        </script>
    </div>
</body>

</html>