<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reset Password - VQ Money</title>
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

        .reset-container {
            width: 100%;
            max-width: 420px;
            padding: 1rem;
        }

        .reset-card {
            background: #fff;
            border-radius: 1rem;
            box-shadow: 0 20px 60px rgba(0,0,0,0.3);
            padding: 2.5rem 2rem;
            text-align: center;
        }

        .reset-icon {
            width: 80px;
            height: 80px;
            background: linear-gradient(135deg, #4e73df, #224abe);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 1.25rem;
            box-shadow: 0 8px 25px rgba(78,115,223,0.4);
        }

        .reset-icon .bi {
            font-size: 2rem;
            color: #fff;
        }

        .reset-card h1 {
            font-size: 1.75rem;
            font-weight: 700;
            color: #1a1c2e;
            margin-bottom: 0.25rem;
        }

        .reset-card .subtitle {
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
            border-color: #4e73df;
            box-shadow: 0 0 0 0.2rem rgba(78,115,223,0.25);
        }

        .btn-reset {
            background: linear-gradient(135deg, #4e73df, #224abe);
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
            background: linear-gradient(135deg, #224abe, #1a3a9e);
            transform: translateY(-1px);
            box-shadow: 0 4px 15px rgba(78,115,223,0.4);
            color: #fff;
        }

        .reset-footer {
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
    <div class="reset-container">
        <div class="reset-card">
            <div class="reset-icon">
                <i class="bi bi-shield-lock"></i>
            </div>
            <h1>Reset Password</h1>
            <p class="subtitle">Enter your new password below</p>

            @if($errors->any())
                <div class="alert alert-danger">
                    @foreach($errors->all() as $error)
                        <div>{{ $error }}</div>
                    @endforeach
                </div>
            @endif

            <form method="POST" action="{{ url('/reset-password') }}">
                @csrf
                <input type="hidden" name="token" value="{{ $token }}">
                <input type="hidden" name="email" value="{{ $email }}">

                <div class="form-floating mb-3 position-relative">
                    <input type="password" class="form-control" id="password" name="password" placeholder="New password" required autofocus>
                    <label for="password">New password</label>
                    <i class="bi bi-lock"></i>
                </div>

                <div class="form-floating mb-4 position-relative">
                    <input type="password" class="form-control" id="password_confirmation" name="password_confirmation" placeholder="Confirm new password" required>
                    <label for="password_confirmation">Confirm new password</label>
                    <i class="bi bi-lock-fill"></i>
                </div>

                <button type="submit" class="btn btn-reset">
                    <i class="bi bi-check-circle me-2"></i>Reset Password
                </button>
            </form>
        </div>
        <div class="reset-footer text-center">
            &copy; 2026 VisionQuest Services LLC
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
