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

if ($user['role'] !== 'admin') {
  header("HTTP/1.1 403 Forbidden");
  header("Location: http://" . $_SERVER['HTTP_HOST'], TRUE, 403);
  exit();
}

$balance = getAccountsSumByUserId($user['id']);
$categories = getAllCategoriesWithType("expenses");
$categoriesInc = getAllCategoriesWithType("income");
$users = getAllUsers();
?>

<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <meta name="description" content="Управляйте пользователями и категориями на нашем сервисе для учета доходов и расходов. Просматривайте список пользователей и добавляйте новые категории в общий список." />
  <link rel="stylesheet" href="css/style.css" />
  <link rel="stylesheet" href="css/reset.css" />
  <link rel="stylesheet" href="css/media.css" />
  <title>Панель администратора | БюджетМастер</title>
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
              <a href="accounts.php" class="header-link">Счета</a>
            </li>
            <li class="header-item">
              <a href="profile.php" class="header-link">Профиль</a>
            </li>
            <?php if ($user['role'] === 'admin') { ?>
              <li class="header-item">
                <a href="admin.php" class="header-link active-link">Адм панель</a>
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
              <p class="profile-balance">Остаток: <?php echo $balance[0]; ?>₽</p>
            </div>
          </div>
        </div>
      </div>
    </header>

    <main class="main">
      <div class="container">
        <section class="categories-sec">

          <!-- категории доходов -->
          <div class="action">
            <div class="categories">
              <?php if ($categoriesInc == null) echo '<h3 class="subtitle">Категорий дохода нет</h3>' ?>
              <?php foreach ($categoriesInc as $i => $category) { ?>
                <div class="category">
                  <input type="radio" name="categoryInc" value="cat<?php echo $category['id']; ?>" id="cat<?php echo $category['id']; ?>" <?php if ($i == 0) echo 'checked'; ?>>
                  <label for="cat<?php echo $category['id']; ?>">
                    <div class="category-bg">
                      <img src="<?php echo $category['image']; ?>" alt="cat<?php echo $category['id']; ?>">
                    </div>
                    <span><?php echo $category['name']; ?></span>
                  </label>
                </div>

                <!-- Форма редактирования категории дохода -->
                <div class="edit-category cat<?php echo $category['id']; ?>">
                  <form action="../service/edit_category.php" method="post" class="register-form-adm" enctype="multipart/form-data">
                    <input type="hidden" name="category_id" value="<?php echo $category['id']; ?>" class="form-input">
                    <input type="text" name="category_name" value="<?php echo $category['name']; ?>" class="form-input" required>
                    <div class="input__wrapper">
                      <label class="input-file">
                        <span class="input-file-text" type="text"></span>
                        <input type="file" name="category_image" placeholder="Иконка категории" class="form-input" required>
                        <span class="input-file-btn">Выберите файл</span>
                      </label>
                    </div>
                    <button type="submit" class="form-submit">Редактировать</button>
                  </form>
                  <form action="../service/delete_category.php" method="post">
                    <input type="hidden" name="id" value="<?php echo $category['id']; ?>">
                    <button type="submit" class="form-submit-delete">Удалить</button>
                  </form>
                </div>
              <?php } ?>
            </div>
            <button type="button" class="form-submit" id="add_account1">Добавить категорию доходов</button>

            <!-- Форма для добавления категории дохода -->
            <div class="add_account1">
              <form action="../service/add_category.php" method="post" class="register-form-adm" enctype="multipart/form-data">
                <input type="text" name="category_name" placeholder="Название категории" id="category_name" class="form-input" required>
                <input type="hidden" name="action" value="income">

                <div class="input__wrapper">
                  <label class="input-file">
                    <span class="input-file-text" type="text"></span>
                    <input type="file" name="category_image" placeholder="Иконка категории" class="form-input" required>
                    <span class="input-file-btn">Выберите файл</span>
                  </label>
                </div>

                <button type="submit" class="form-submit" id="checked1">Добавить категорию</button>
              </form>
            </div>
          </div>

          <!-- категории расходов -->
          <div class="action">
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

                <!-- Форма редактирования категории расхода -->
                <div class="edit-category cat<?php echo $category['id']; ?>">
                  <form action="../service/edit_category.php" method="post" class="register-form-adm" enctype="multipart/form-data">
                    <input type="hidden" name="category_id" value="<?php echo $category['id']; ?>" class="form-input">
                    <input type="text" name="category_name" value="<?php echo $category['name']; ?>" class="form-input" required>
                    <div class="input__wrapper">
                      <label class="input-file">
                        <span class="input-file-text" type="text"></span>
                        <input type="file" name="category_image" placeholder="Иконка категории" class="form-input" required>
                        <span class="input-file-btn">Выберите файл</span>
                      </label>
                    </div>
                    <button type="submit" class="form-submit">Редактировать</button>
                  </form>
                  <form action="../service/delete_category.php" method="post">
                    <input type="hidden" name="id" value="<?php echo $category['id'] ?>">
                    <button type="submit" class="form-submit-delete">Удалить</button>
                  </form>
                </div>
              <?php } ?>
            </div>
            <button type="button" class="form-submit" id="add_account">Добавить категорию расходов</button>

            <!-- Форма добавления категории расхода -->
            <div class="add_account">
              <form action="../service/add_category.php" method="post" class="register-form-adm" enctype="multipart/form-data">
                <input type="text" name="category_name" placeholder="Название категории" id="category_name" class="form-input" required>
                <input type="hidden" name="action" value="expenses">

                <div class="input__wrapper">
                  <label class="input-file">
                    <span class="input-file-text" type="text"></span>
                    <input type="file" name="category_image" placeholder="Иконка категории" class="form-input" required>
                    <span class="input-file-btn">Выберите файл</span>
                  </label>
                </div>

                <button type="submit" class="form-submit" id="checked">Добавить категорию</button>
              </form>
            </div>

            <div class="mt30">
              <h3 class="title">Пользователи</h3>
              <?php foreach ($users as $user) { ?>
                <?php $role = $user['role'] === 'admin' ? 'Понизить' : 'Повысить'; ?>
                <div class="wallet-stat-item">
                  <div class="stat-item-details">
                    <div class="stat-item-icon-block">
                      <img src="../assets/images/profile.svg" alt="products" class="stat-item-icon">
                    </div>
                    <h5 class="stat-item-name"><?php echo $user['username']; ?></h5>
                  </div>
                  <p class="stat-item-info">
                    <span class="stat-item-percentages"><?php echo $user['email']; ?></span> | <a href="../service/change_role.php?role=<?php echo $user['role'] ?>&id=<?php echo $user['id'] ?>" class="header-link"><?php echo $role; ?></a>
                  </p>
                </div>
              <?php } ?>
            </div>
          </div>
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

  <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.5.1/jquery.min.js"></script>
  <script>
    $('.input-file input[type=file]').on('change', function() {
      let file = this.files[0];
      $(this).closest('.input-file').find('.input-file-text').html(file.name);
    });
    $(document).ready(function() {
      $("#balance").click(function() {
        $(this).val("");
      });
      $('#add_account').click(function() {
        $('.add_account').css('display', 'flex');
        $('.bg').css('display', 'block');
      });
      $('#add_account1').click(function() {
        $('.add_account1').css('display', 'flex');
        $('.bg').css('display', 'block');
      });
      // $('#checked').click(function() {
      //   $('.add_account').css('display', 'none');
      //   $('.bg').css('display', 'none');
      // });
      // $('#checked1').click(function() {
      //   $('.add_account1').css('display', 'none');
      //   $('.bg').css('display', 'none');
      // });
      $('.bg').click(function() {
        $('.add_account').css('display', 'none');
        $('.add_account1').css('display', 'none');
        $('.bg').css('display', 'none');
        $('.edit-category').css('display', 'none');
      });
      $('input[type="radio"]').click(function() {
        var id = $(this).val();
        $('.' + id).css('display', 'flex');
        $('.bg').css('display', 'block');
      });
    });
  </script>
</body>

</html>