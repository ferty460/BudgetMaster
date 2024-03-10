<?php require_once "../service/boot.php"; ?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <meta name="description" content="На нашем сайте вы можете авторизоваться для доступа к вашим личным финансовым данным на нашем сервисе для учета доходов и расходов." />
    <link rel="stylesheet" href="css/style.css" />
    <link rel="stylesheet" href="css/reset.css" />
    <link rel="stylesheet" href="css/media.css" />
    <title>Авторизация | БюджетМастер</title>
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

                    <?php if (check_auth()) { ?>
                        <ul class="header-list">
                            <li class="header-item">
                                <a href="my_wallet.php" class="header-link">Кошелек</a>
                            </li>
                            <li class="header-item">
                                <a href="accounts.php" class="header-link active-link">Счета</a>
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
                    <?php } ?>
                </div>
            </div>
        </header>

        <main class="main">
            <section class="registration">
                <div class="container">
                    <div class="container-inner">
                        <h2 class="title">Авторизация</h2>
                        <p class="question">Еще нет аккаунта? <a href="registration.php">Зарегистрироваться</a></p>

                        <?php flash(); ?>
                        <form action="../service/do-login.php" method="post" class="register-form">
                            <div class="form-item">
                                <label for="email" class="form-label">Введите электронную почту:</label>
                                <input name="email" type="email" id="email" placeholder="Электронная почта" class="form-input">
                            </div>

                            <div class="form-item">
                                <label for="password" class="form-label">Введите пароль:</label>
                                <input name="password" type="password" id="password" placeholder="Пароль" class="form-input">
                            </div>

                            <div class="form-item">
                                <label for="captcha" class="form-label">Введите код с картинки:</label>
                                <div class="captcha-block">
                                    <img src="../service/captcha.php" alt="Captcha" class="captcha">
                                </div>
                                <input type="text" id="captcha" name="captcha_code" placeholder="Код с картинки" class="form-input">
                            </div>

                            <input type="submit" value="Войти" class="form-submit">
                        </form>
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
    </div>
</body>

</html>