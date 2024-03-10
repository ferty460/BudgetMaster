<?php require_once 'service/boot.php'; ?>
<?php
$stmt = pdo()->prepare("SELECT * FROM `users` WHERE `id` = :id");
if (isset($_SESSION['user_id'])) {
    $stmt->execute(['id' => $_SESSION['user_id']]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="Наш сайт - это удобный помощник в учете ваших доходов и расходов. Мы предлагаем инструменты для отслеживания ваших финансов, анализа и визуализации ваших данных, чтобы вы могли лучше контролировать свои финансы." />
    <link rel="stylesheet" href="assets/css/style.css" />
    <link rel="stylesheet" href="assets/css/reset.css" />
    <link rel="stylesheet" href="assets/css/media.css" />
    <title>Главная | БюджетМастер</title>
</head>

<body>
    <div class="wrapper">
        <header class="header">
            <div class="container">
                <div class="header-inner">
                    <div class="logo-block">
                        <a href="main.php" class="logo-link">
                            <img src="assets/images/logo.svg" alt="logo" class="logo" />
                        </a>
                    </div>

                    <?php if (check_auth()) { ?>
                        <ul class="header-list">
                            <li class="header-item">
                                <a href="assets/my_wallet.php" class="header-link">Кошелек</a>
                            </li>
                            <li class="header-item">
                                <a href="assets/accounts.php" class="header-link">Счета</a>
                            </li>
                            <li class="header-item">
                                <a href="assets/profile.php" class="header-link">Профиль</a>
                            </li>
                            <?php if ($user['role'] === 'admin') { ?>
                                <li class="header-item">
                                    <a href="assets/admin.php" class="header-link">Адм панель</a>
                                </li>
                            <?php } ?>
                            <li class="header-item">
                                <a href="service/do-logout.php" class="header-link">Выйти</a>
                            </li>
                        </ul>
                    <?php } else { ?>
                        <ul class="header-list">
                            <li class="header-item">
                                <a href="assets/login.php" class="header-link">Войти</a>
                            </li>
                            <li class="header-item">
                                <a href="assets/registration.php" class="header-link">Зарегистрироваться</a>
                            </li>
                            </li>
                        </ul>
                    <?php } ?>
                </div>
            </div>
        </header>

        <main class="main">
            <section class="welcome">
                <div class="container">
                    <div class="welcome-block">
                        <div class="welcome-details">
                            <h2 class="welcome-title">Управляй своими расходами - улучшай свою жизнь.</h2>
                            <div>
                                <a href="assets/my_wallet.php" class="link-button">Начать сейчас</a>
                            </div>
                        </div>
                        <img src="assets/images/main.png" alt="welcome">
                    </div>
                </div>
            </section>

            <section class="area">
                <div class="container">
                    <h2 class="title">Кому подойдет сервис?</h2>
                    <div class="area-list">
                        <div class="area-item">
                            <h3 class="subtitle">Студенты</h3>
                            <img src="assets/images/students.svg" alt="students">
                            <p class="area-description">Студенты могут использовать это приложение для учета расходов, чтобы лучше контролировать свои финансы и улучшать свою жизнь.</p>
                        </div>
                        <div class="area-item">
                            <h3 class="subtitle">Малый бизнес</h3>
                            <img src="assets/images/business.svg" alt="business">
                            <p class="area-description">Семейные пары могут использовать это приложение для учета расходов, 
                                чтобы лучше контролировать свои финансы и улучшать качество
                                жизни.</p>
                        </div>
                        <div class="area-item">
                            <h3 class="subtitle">Семьи</h3>
                            <img src="assets/images/family.svg" alt="family">
                            <p class="area-description">Студенты могут использовать это приложение для учета расходов, чтобы лучше контролировать свои финансы и улучшать свою жизнь.</p>
                        </div>
                    </div>
                </div>
            </section>

            <section class="advantages">
                <div class="container">
                    <h2 class="title">Преимущества нашего сервиса</h2>
                    <div class="area-list">
                        <div class="area-item">
                            <h3 class="subtitle">Удобство использования</h3>
                            <img src="assets/images/home.svg" alt="students">
                            <p class="area-description">Интуитивно понятный интерфейс позволит пользователям легко
                                вводить и отслеживать свои расходы</p>
                        </div>
                        <div class="area-item">
                            <h3 class="subtitle">Автоматическое отслеживание</h3>
                            <img src="assets/images/budget.svg" alt="students">
                            <p class="area-description">Система автоматически отслеживает и анализирует расходы,
                                предоставляя подробные отчеты и статистику</p>
                        </div>
                        <div class="area-item">
                            <h3 class="subtitle">Управление бюджетом</h3>
                            <img src="assets/images/accounting.svg" alt="students">
                            <p class="area-description">Помогает пользователям контролировать свои расходы,
                                устанавливая бюджеты и получая оповещения о превышении лимитов</p>
                        </div>
                    </div>
                </div>
            </section>
        </main>

        <footer class="footer">
            <div class="container">
                <div class="footer-inner">
                    <div class="logo-block">
                        <a href="#" class="logo-link">
                            <img src="assets/images/logo.svg" alt="logo" class="logo" />
                        </a>
                    </div>
                </div>
            </div>
        </footer>
    </div>
</body>

</html>