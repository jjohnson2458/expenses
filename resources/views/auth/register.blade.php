<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Register - VQ Money</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }

        body {
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            background: linear-gradient(135deg, #1a1c2e 0%, #2d3154 100%);
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
        }

        .register-container {
            width: 100%;
            max-width: 420px;
            padding: 1rem;
        }

        .register-card {
            background: #fff;
            border-radius: 1rem;
            box-shadow: 0 20px 60px rgba(0,0,0,0.3);
            padding: 2.5rem 2rem;
            text-align: center;
        }

        .register-icon {
            width: 80px;
            height: 80px;
            background: linear-gradient(135deg, #1cc88a, #17a673);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 1.25rem;
            box-shadow: 0 8px 25px rgba(28,200,138,0.4);
        }

        .register-icon .bi {
            font-size: 2rem;
            color: #fff;
        }

        .register-card h1 {
            font-size: 1.75rem;
            font-weight: 700;
            color: #1a1c2e;
            margin-bottom: 0.25rem;
        }

        .register-card .subtitle {
            color: #858796;
            font-size: 0.9rem;
            margin-bottom: 2rem;
        }

        .form-floating { text-align: left; }

        .form-floating .bi {
            position: absolute;
            top: 50%;
            transform: translateY(-50%);
            right: 1rem;
            color: #858796;
            z-index: 5;
            pointer-events: none;
        }

        .form-floating .form-control {
            border-radius: 0.5rem;
            border: 1px solid #d1d3e2;
            padding-right: 2.5rem;
        }

        .form-floating .form-control:focus {
            border-color: #1cc88a;
            box-shadow: 0 0 0 0.2rem rgba(28,200,138,0.25);
        }

        .btn-register {
            background: linear-gradient(135deg, #1cc88a, #17a673);
            border: none;
            border-radius: 0.5rem;
            padding: 0.75rem;
            font-size: 1rem;
            font-weight: 600;
            width: 100%;
            color: #fff;
            transition: all 0.3s;
        }

        .btn-register:hover {
            background: linear-gradient(135deg, #17a673, #128a56);
            transform: translateY(-1px);
            box-shadow: 0 4px 15px rgba(28,200,138,0.4);
            color: #fff;
        }

        .login-link {
            margin-top: 1.5rem;
            font-size: 0.9rem;
            color: #858796;
        }

        .login-link a {
            color: #4e73df;
            text-decoration: none;
            font-weight: 600;
        }

        .login-link a:hover { text-decoration: underline; }

        .register-footer {
            margin-top: 2rem;
            color: rgba(255,255,255,0.4);
            font-size: 0.8rem;
        }

        .alert {
            text-align: left;
            border-radius: 0.5rem;
            font-size: 0.875rem;
        }
    </style>
</head>
<body>
    <div class="register-container">
        <div class="register-card">
            <div class="register-icon">
                <i class="bi bi-person-plus"></i>
            </div>
            <h1>Create Account</h1>
            <p class="subtitle">Start tracking expenses for free</p>

            @if(session('flash'))
                <div class="alert alert-{{ session('flash')['type'] ?? 'info' }} alert-dismissible fade show" role="alert">
                    {{ session('flash')['message'] ?? '' }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            @endif

            @if($errors->any())
                <div class="alert alert-danger">
                    @foreach($errors->all() as $error)
                        <div>{{ $error }}</div>
                    @endforeach
                </div>
            @endif

            <form method="POST" action="{{ url('/register') }}">
                @csrf

                <div class="form-floating mb-3 position-relative">
                    <input type="text" class="form-control" id="name" name="name" placeholder="Full name" value="{{ old('name') }}" required autofocus>
                    <label for="name">Full name</label>
                    <i class="bi bi-person"></i>
                </div>

                <div class="form-floating mb-3 position-relative">
                    <input type="email" class="form-control" id="email" name="email" placeholder="Email address" value="{{ old('email') }}" required>
                    <label for="email">Email address</label>
                    <i class="bi bi-envelope"></i>
                </div>

                <div class="form-floating mb-3 position-relative">
                    <input type="password" class="form-control" id="password" name="password" placeholder="Password" required>
                    <label for="password">Password</label>
                    <i class="bi bi-lock"></i>
                </div>

                <div class="form-floating mb-4 position-relative">
                    <input type="password" class="form-control" id="password_confirmation" name="password_confirmation" placeholder="Confirm password" required>
                    <label for="password_confirmation">Confirm password</label>
                    <i class="bi bi-lock-fill"></i>
                </div>

                <button type="submit" class="btn btn-register">
                    <i class="bi bi-rocket-takeoff me-2"></i>Create Account
                </button>
            </form>

            <div class="login-link">
                Already have an account? <a href="{{ url('/login') }}">Sign in</a>
            </div>
        </div>
        <div class="register-footer text-center">
            &copy; 2026 VisionQuest Services LLC
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
