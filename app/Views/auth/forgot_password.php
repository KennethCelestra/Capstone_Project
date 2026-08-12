<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Forgot Password - <?= APP_NAME ?></title>
    <link rel="stylesheet" href="<?= BASE_URL ?>css/style.css">
    <link rel="stylesheet" href="<?= BASE_URL ?>css/isatu.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <style>
        .login-body {
            background-color: var(--bg);
            background-image: linear-gradient(135deg, rgba(0,33,71,0.05) 0%, rgba(253,184,19,0.05) 100%);
            display: flex;
            align-items: center;
            justify-content: center;
            min-height: 100vh;
        }
        .login-card {
            padding: 2rem 2rem;
            max-width: 420px;
            width: 100%;
            margin: 0 1rem;
            box-sizing: border-box;
        }
        .login-header h1 {
            color: var(--primary);
            font-size: 1.5rem;
            font-weight: 800;
            margin-bottom: 0.5rem;
        }
        .login-header p {
            color: var(--text-muted);
            margin-bottom: 1.25rem;
            font-size: 0.95rem;
        }
        .form-group {
            margin-bottom: 1rem;
        }
        .form-group label {
            font-weight: 600;
            color: var(--primary-dark);
            margin-bottom: 0.5rem;
            display: block;
            text-align: left;
        }
        .form-control {
            width: 100%;
            padding: 0.75rem 1rem;
            border: 1px solid var(--border);
            border-radius: 8px;
            font-family: inherit;
            transition: all 0.2s;
            box-sizing: border-box;
        }
        .form-control:focus {
            outline: none;
            border-color: var(--primary);
            box-shadow: 0 0 0 3px rgba(0,33,71,0.1);
        }
        .input-group {
            position: relative;
        }
        .input-group i {
            position: absolute;
            left: 1rem;
            top: 50%;
            transform: translateY(-50%);
            color: var(--text-muted);
        }
        .input-group .form-control {
            padding-left: 2.75rem;
        }
        .btn-login {
            background-color: var(--primary);
            color: #fff;
            border: none;
            padding: 0.85rem;
            border-radius: 8px;
            font-weight: 600;
            width: 100%;
            transition: all 0.2s;
            margin-top: 0.5rem;
            cursor: pointer;
        }
        .btn-login:hover {
            background-color: var(--primary-light);
            transform: translateY(-1px);
        }
        .school-logo {
            width: 65px;
            height: 65px;
            object-fit: contain;
            margin-bottom: 0.5rem;
        }
    </style>
</head>
<body class="login-body">
    <div class="gold-card login-card text-center">
        <div class="login-header">
            <h1 class="mt-2">Forgot Password</h1>
            <p>Enter your email to receive a reset link</p>
        </div>

        <?php if (!empty($flash)): ?>
            <div class="alert mb-3 p-3 rounded" style="background: <?= $flash['type'] === 'error' ? '#f8d7da' : '#d4edda' ?>; color: <?= $flash['type'] === 'error' ? 'red' : '#155724' ?>; border: 1px solid <?= $flash['type'] === 'error' ? '#f5c6cb' : '#c3e6cb' ?>; text-align: left; font-size: 0.9rem;">
                <i class="bi <?= $flash['type'] === 'error' ? 'bi-exclamation-octagon' : 'bi-check-circle' ?> me-2"></i> <?= htmlspecialchars($flash['message']) ?>
            </div>
        <?php endif; ?>

        <form action="<?= BASE_URL ?>forgot-password" method="POST" class="login-form">
            <input type="hidden" name="_csrf_token" value="<?= htmlspecialchars($csrfToken ?? '') ?>">
            <div class="form-group">
                <label>Email Address</label>
                <div class="input-group">
                    <i class="bi bi-envelope"></i>
                    <input type="email" name="email" class="form-control" required placeholder="Enter your email" autofocus>
                </div>
            </div>

            <button type="submit" class="btn-login"><i class="bi bi-send me-1"></i> Send Reset Link</button>
        </form>

        <div style="text-align: center; margin-top: 1.5rem;">
            <p style="font-size: 0.9rem; color: var(--text-muted); margin-bottom: 0;">Remembered your password? <br><a href="<?= BASE_URL ?>login" style="color: var(--primary); text-decoration: none; font-weight: 600; display: inline-block; margin-top: 0.5rem;"><i class="bi bi-arrow-left"></i> Back to Login</a></p>
        </div>
    </div>
</body>
</html>
