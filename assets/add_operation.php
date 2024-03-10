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
$accounts = getAccountsByUserId($user['id']);
$action = $_GET['action'];
$categories = getAllCategoriesWithType($action);
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="Добавляйте операции как для расходов, так и для доходов на нашем удобном сервисе для учета доходов и расходов. Наш сервис поможет вам контролировать свои финансы." />
    <link rel="stylesheet" href="css/style.css" />
    <link rel="stylesheet" href="css/reset.css" />
    <link rel="stylesheet" href="css/media.css" />
    <title>Добавление операции | БюджетМастер</title>
</head>

<body class="body">
    <div class="bg"></div>
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
                            <h5 class="profile-nickname">Anonymous</h5>
                            <p class="profile-balance">Остаток: <?php echo $balance[0]; ?> ₽</p>
                        </div>
                    </div>
                </div>
            </div>
        </header>

        <main class="main">
            <section class="add-operation">
                <div class="container">
                    <form action="../service/add_operation.php" method="post">
                        <input type="hidden" name="action" value="<?php echo $action; ?>">
                        <?php flash(); ?>
                        <label class="money-to-operation-block">
                            <input id="amount" type="number" name="amount" class="money-to-operation" value="0" required>
                            <span>₽</span>
                        </label>

                        <div class="operation-link-button">
                            <button type="button" class="link-button" id="account-selection">Выбрать счет</button>
                        </div>

                        <div class="account-selection">
                            <?php foreach ($accounts as $i => $account) { ?>
                                <div class="account-item">
                                    <input class="custom-radio" id="account<?php echo $account['id']; ?>" name="account" value="<?php echo $account['id']; ?>" type="radio" <?php if ($i == 0) echo 'checked'; ?>>
                                    <label for="account<?php echo $account['id']; ?>">
                                        <p class="account-name"><?php echo $account['name']; ?> - <?php echo $account['balance']; ?> ₽</p>
                                    </label>
                                </div>
                            <?php } ?>
                            <button type="button" class="form-submit" id="checked">Выбрать</button>
                        </div>

                        <div class="categories">
                            <?php if ($categories == null) echo '<h3 class="subtitle">Категорий расхода нет</h3>' ?>
                            <?php foreach ($categories as $i => $category) { ?>
                                <div class="category">
                                    <input type="radio" name="category" value="cat<?php echo $category['id']; ?>" id="cat<?php echo $category['id']; ?>" <?php if ($i == 0) echo 'checked'; ?>>
                                    <label for="cat<?php echo $category['id']; ?>">
                                        <div class="category-bg">
                                            <img src="<?php echo $category['image']; ?>" alt="cat<?php echo $category['id']; ?>">
                                        </div>
                                        <span><?php echo $category['name']; ?></span>
                                    </label>
                                </div>
                            <?php } ?>
                        </div>

                        <div class="operation-details">
                            <label>
                                <input type="date" name="operation-date" class="date" title="Выберите дату" required>
                            </label>
                            <textarea name="comment" class="form-input" placeholder="Комментарий"></textarea>
                        </div>

                        <button type="submit" class="form-submit">Добавить</button>
                    </form>
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

    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.5.1/jquery.min.js"></script>
    <script>
        $(document).ready(function() {
            $("#amount").click(function() {
                $(this).val("");
            });
            $('#account-selection').click(function() {
                $('.account-selection').css('display', 'flex');
                $('.bg').css('display', 'block');
            });
            $('#checked').click(function() {
                $('.account-selection').css('display', 'none');
                $('.bg').css('display', 'none');
            })
            $('.bg').click(function() {
                $('.account-selection').css('display', 'none');
                $('.bg').css('display', 'none');
            })
        });
        window.onload = function() {
            var today = new Date();
            var dd = today.getDate();
            var mm = today.getMonth() + 1;
            var yyyy = today.getFullYear();

            if (dd < 10) {
                dd = '0' + dd;
            }

            if (mm < 10) {
                mm = '0' + mm;
            }

            today = yyyy + '-' + mm + '-' + dd;
            document.getElementsByName("operation-date")[0].setAttribute('max', today);
        }
    </script>
</body>

</html>