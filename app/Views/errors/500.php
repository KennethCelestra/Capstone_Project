<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>500 Internal Error – <?= defined('APP_NAME') ? APP_NAME : 'AutoClear Clearance System' ?></title>
    <link rel="stylesheet" href="<?= defined('BASE_URL') ? BASE_URL : '/' ?>css/style.css">
</head>

<body class="login-body">
    <div class="login-container">
        <div class="login-card" style="text-align:center;">
            <div style="font-size:4rem;">⚠️</div>
            <h1 style="font-size:2.5rem;margin:1rem 0 0.5rem 0;">500 Internal Error</h1>
            <p>Something went wrong on our end. Please try refreshing the page or try again later.</p>
            <a href="<?= defined('BASE_URL') ? BASE_URL : '/' ?>" class="btn btn-primary" style="margin-top:1rem;">Return Home</a>
        </div>
    </div>
</body>

</html>
