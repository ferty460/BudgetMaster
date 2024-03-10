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

$accounts = getAccountsByUserId($user['id']);
$balance = getAccountsSumByUserId($user['id']);
?>

<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <meta name="description" content="Создавайте, просматривайте и контролируйте свои счета на нашем сервисе для учета доходов и расходов. Наш сайт предоставляет удобный интерфейс для управления вашими финансами." />
  <link rel="stylesheet" href="css/style.css" />
  <link rel="stylesheet" href="css/reset.css" />
  <link rel="stylesheet" href="css/media.css" />
  <title>Счета | БюджетМастер</title>
</head>

<body>
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
        </div>
      </div>
    </header>

    <main class="main">
      <section class="accounts">
        <div class="container">
          <p class="title">Итого:</p>
          <p class="title"><?php echo $balance[0]; ?> ₽</p>

          <div class="wallet-stat-list active-canvas mt20">
            <?php foreach ($accounts as $account) { ?>
              <div class="wallet-stat-item">
                <div class="stat-item-details">
                  <div class="stat-item-icon-block">
                    <img src="images/card.svg" alt="card" class="stat-item-icon">
                  </div>
                  <h5 class="stat-item-name"><?php echo $account['name']; ?></h5>
                </div>
                <p class="stat-item-info">
                  <span class="stat-item-percentages"><?php echo getPercentageBalance($account['id']) . ' %'; ?></span> |
                  <span class="stat-item-result"><?php echo $account['balance'] . ' ₽'; ?></span>
                </p>
              </div>
            <?php } ?>
          </div>
          <button type="button" class="form-submit" id="add_account">Добавление счета</button>

          <!-- Форма добавления счета -->
          <div class="add_account">
            <form action="../service/add_account.php" method="post" class="register-form" onsubmit="return validateBalance()">
              <div class="form-item">
                <label for="name" class="form-label">Введите название нового счета:</label>
                <input name="name" type="text" id="name" placeholder="Название счета" class="form-input" required>
              </div>
              <div class="form-item">
                <label for="balance" class="form-label">Начальный баланс:</label>
                <input name="balance" type="number" id="balance" placeholder="Баланс" class="form-input" value="0" required>
              </div>
              <input type="submit" value="Добавить" class="form-submit">
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

    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.5.1/jquery.min.js"></script>
    <script>
      $(document).ready(function() {
        $("#balance").click(function() {
          $(this).val("");
        });
        $('#add_account').click(function() {
          $('.add_account').css('display', 'flex');
          $('.bg').css('display', 'block');
        });
        $('#checked').click(function() {
          $('.add_account').css('display', 'none');
          $('.bg').css('display', 'none');
        })
        $('.bg').click(function() {
          $('.add_account').css('display', 'none');
          $('.bg').css('display', 'none');
        })
      });
    </script>
    <script>
      function validateBalance() {
        var balance = document.getElementById('balance').value;
        if (balance < 0) {
          alert('Баланс не может быть отрицательным.');
          return false; // Предотвращает отправку формы
        }
        return true; // Разрешает отправку формы
      }
    </script>
</body>

</html>