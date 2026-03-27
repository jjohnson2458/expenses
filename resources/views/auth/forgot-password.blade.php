<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Forgot Password - VQ Money</title>
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

        .forgot-container {
            width: 100%;
            max-width: 420px;
            padding: 1rem;
        }

        .forgot-card {
            background: #fff;
            border-radius: 1rem;
            box-shadow: 0 20px 60px rgba(0,0,0,0.3);
            padding: 2.5rem 2rem;
            text-align: center;
        }

        .forgot-icon {
            width: 80px;
            height: 80px;
            background: linear-gradient(135deg, #f6c23e, #d4a017);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 1.25rem;
            box-shadow: 0 8px 25px rgba(246,194,62,0.4);
        }

        .forgot-icon .bi {
            font-size: 2rem;
            color: #fff;
        }

        .forgot-card h1 {
            font-size: 1.75rem;
            font-weight: 700;
            color: #1a1c2e;
            margin-bottom: 0.25rem;
        }

        .forgot-card .subtitle {
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
            border-color: #f6c23e;
            box-shadow: 0 0 0 0.2rem rgba(246,194,62,0.25);
        }

        .btn-reset {
            background: linear-gradient(135deg, #f6c23e, #d4a017);
            border: none;
            border-radius: 0.5rem;
            padding: 0.75rem;
            font-size: 1rem;
            font-weight: 600;
            width: 100%;
            color: #fff;
            transition: all 0.3s;
        }

        .btn-reset:hover {
            background: linear-gradient(135deg, #d4a017, #b8860b);
            transform: translateY(-1px);
            box-shadow: 0 4px 15px rgba(246,194,62,0.4);
            color: #fff;
        }

        .back-link {
            margin-top: 1.5rem;
            font-size: 0.9rem;
            color: #858796;
        }

        .back-link a {
            color: #4e73df;
            text-decoration: none;
            font-weight: 600;
        }

        .back-link a:hover { text-decoration: underline; }

        .forgot-footer {
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
    <div class="forgot-container">
        <div class="forgot-card">
            <div class="forgot-icon">
                <i class="bi bi-key"></i>
            </div>
            <h1>Forgot Password?</h1>
            <p class="subtitle">Enter your email and we'll send you a reset link</p>

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

            <form method="POST" action="{{ url('/forgot-password') }}">
                @csrf

                <div class="form-floating mb-4 position-relative">
                    <input type="email" class="form-control" id="email" name="email" placeholder="Email address" value="{{ old('email') }}" required autofocus>
                    <label for="email">Email address</label>
                    <i class="bi bi-envelope"></i>
                </div>

                <button type="submit" class="btn btn-reset">
                    <i class="bi bi-send me-2"></i>Send Reset Link
                </button>
            </form>

            <div class="back-link">
                <a href="{{ url('/login') }}"><i class="bi bi-arrow-left me-1"></i>Back to sign in</a>
            </div>
        </div>
        <div class="forgot-footer text-center">
            &copy; 2026 VisionQuest Services LLC
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
