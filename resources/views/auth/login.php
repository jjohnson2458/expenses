<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sign In - MyExpenses</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        body {
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            background: linear-gradient(135deg, #1a1c2e 0%, #2d3154 100%);
            font-family: 'Segoe UI', system-ui, -apple-system, sans-serif;
        }
        .login-wrapper {
            width: 100%;
            max-width: 420px;
            padding: 1rem;
        }
        .login-card {
            background: #fff;
            border-radius: 12px;
            box-shadow: 0 20px 60px rgba(0, 0, 0, 0.3);
            overflow: hidden;
        }
        .login-header {
            text-align: center;
            padding: 2.5rem 2rem 1.5rem;
        }
        .login-header .icon-circle {
            width: 64px;
            height: 64px;
            background: linear-gradient(135deg, #4e73df 0%, #224abe 100%);
            border-radius: 50%;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            margin-bottom: 1rem;
        }
        .login-header .icon-circle i {
            font-size: 1.75rem;
            color: #fff;
        }
        .login-header h1 {
            font-size: 1.5rem;
            font-weight: 700;
            color: #1a1c2e;
            margin-bottom: 0.25rem;
        }
        .login-header p {
            color: #6c757d;
            font-size: 0.9rem;
            margin: 0;
        }
        .login-body {
            padding: 1rem 2rem 2rem;
        }
        .form-floating {
            margin-bottom: 1rem;
        }
        .form-floating .form-control {
            border: 1px solid #dee2e6;
            border-radius: 8px;
            padding-left: 2.75rem;
            height: 3.25rem;
        }
        .form-floating .form-control:focus {
            border-color: #4e73df;
            box-shadow: 0 0 0 0.2rem rgba(78, 115, 223, 0.15);
        }
        .form-floating label {
            padding-left: 2.75rem;
            color: #6c757d;
        }
        .input-icon {
            position: absolute;
            left: 1rem;
            top: 50%;
            transform: translateY(-50%);
            color: #adb5bd;
            z-index: 5;
            font-size: 1.1rem;
        }
        .btn-login {
            background: linear-gradient(135deg, #4e73df 0%, #224abe 100%);
            border: none;
            border-radius: 8px;
            padding: 0.75rem;
            font-size: 1rem;
            font-weight: 600;
            color: #fff;
            width: 100%;
            transition: all 0.2s ease;
        }
        .btn-login:hover {
            background: linear-gradient(135deg, #3a5ec8 0%, #1a3fa6 100%);
            transform: translateY(-1px);
            box-shadow: 0 4px 15px rgba(78, 115, 223, 0.4);
            color: #fff;
        }
        .btn-login:active {
            transform: translateY(0);
        }
        .login-footer {
            text-align: center;
            padding: 1.5rem 0 0;
        }
        .login-footer p {
            color: rgba(255, 255, 255, 0.4);
            font-size: 0.8rem;
            margin: 0;
        }
        .alert {
            border: none;
            border-radius: 8px;
            font-size: 0.875rem;
        }
    </style>
</head>
<body>
    <div class="login-wrapper">
        <div class="login-card">
            <div class="login-header">
                <div class="icon-circle">
                    <i class="bi bi-receipt-cutoff"></i>
                </div>
                <h1>MyExpenses</h1>
                <p>Smart Expense Reporting</p>
            </div>
            <div class="login-body">
                <?php $flash = flash(); ?>
                <?php if ($flash): ?>
                    <div class="alert alert-<?= e($flash['type']) ?>" role="alert">
                        <?= e($flash['message']) ?>
                    </div>
                <?php endif; ?>

                <form method="POST" action="/login" autocomplete="on">
                    <?= csrf_field() ?>

                    <div class="form-floating position-relative">
                        <i class="bi bi-envelope input-icon"></i>
                        <input type="email" class="form-control" id="email" name="email"
                               placeholder="Email address" required autofocus>
                        <label for="email">Email address</label>
                    </div>

                    <div class="form-floating position-relative">
                        <i class="bi bi-lock input-icon"></i>
                        <input type="password" class="form-control" id="password" name="password"
                               placeholder="Password" required>
                        <label for="password">Password</label>
                    </div>

                    <button type="submit" class="btn btn-login mt-2">
                        <i class="bi bi-box-arrow-in-right me-1"></i> Sign In
                    </button>
                </form>
            </div>
        </div>
        <div class="login-footer">
            <p>&copy; <?= date('Y') ?> VisionQuest Services LLC</p>
        </div>
    </div>
</body>
</html>
