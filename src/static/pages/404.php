<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta http-equiv="X-UA-Compatible" content="IE=edge">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>404 - Page not found</title>

  <?= style('../assets/stylesheet/styles.css') ?>
</head>
<body>
  <?php require __DIR__ . '/partials/header.php'; ?>
  <div class="page-container">
    <div class="page-404-content">
      <h1>404</h1>
      <p>
        The you are trying to visit does not exists.
        <span>Go to <a href="<?= url('/') ?>">home page</a> if you feel lost!</span>
      </p>
    </div>
  </div>
</body>
</html>
