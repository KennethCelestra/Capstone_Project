<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Login – <?= APP_NAME ?></title>
    <link rel="stylesheet" href="<?= BASE_URL ?>css/style.css">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
</head>

<body class="login-body">
    <div class="login-container">
        <div class="login-card">
            <div class="login-header">
                <div class="login-logo">CS</div>
                <h1><?= APP_NAME ?></h1>
                <p class="admin-badge">Administrator Portal</p>
            </div>

            <?php if (!empty($flash)): ?>
                <div class="alert alert-<?= $flash['type'] ?>">
                    <?= htmlspecialchars($flash['message']) ?>
                </div>
            <?php endif; ?>

            <form action="<?= BASE_URL ?>admin/login" method="POST" class="login-form">
                <div class="form-group">
                    <label for="email">Admin Email</label>
                    <input type="email" id="email" name="email" placeholder="admin@school.edu" required autofocus>
                </div>
                <div class="form-group">
                    <label for="password">Password</label>
                    <input type="password" id="password" name="password" placeholder="••••••••" required>
                </div>
                <button type="submit" class="btn btn-primary btn-full">Sign In as Admin</button>
            </form>
        </div>
    </div>
</body>

</html>
