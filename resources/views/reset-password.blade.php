<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reset Password</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600&display=swap" rel="stylesheet">

    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: 'Inter', sans-serif;
        }

        body {
            background: linear-gradient(135deg, #4f46e5, #3b82f6);
            height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .reset-password-container {
            background: #fff;
            padding: 2.5rem;
            border-radius: 16px;
            box-shadow: 0 6px 25px rgba(0, 0, 0, 0.15);
            width: 100%;
            max-width: 420px;
            animation: fadeIn 0.6s ease;
        }

        h2 {
            text-align: center;
            color: #111827;
            margin-bottom: 1.5rem;
            font-weight: 600;
        }

        .alert {
            background-color: #fee2e2;
            color: #b91c1c;
            padding: 0.75rem 1rem;
            border-radius: 8px;
            margin-bottom: 1rem;
            font-size: 0.95rem;
        }

        .form-group {
            margin-bottom: 1.25rem;
        }

        label {
            display: block;
            font-weight: 500;
            color: #374151;
            margin-bottom: 0.5rem;
        }

        input[type="password"] {
            width: 100%;
            padding: 0.75rem;
            border: 1px solid #d1d5db;
            border-radius: 8px;
            font-size: 1rem;
            transition: border-color 0.2s ease, box-shadow 0.2s ease;
        }

        input[type="password"]:focus {
            border-color: #3b82f6;
            box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.25);
            outline: none;
        }

        .btn {
            width: 100%;
            background-color: #3b82f6;
            color: white;
            font-size: 1rem;
            font-weight: 500;
            padding: 0.85rem;
            border: none;
            border-radius: 10px;
            cursor: pointer;
            transition: all 0.3s ease;
        }

        .btn:hover {
            background-color: #2563eb;
            transform: translateY(-1px);
        }

        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(20px); }
            to { opacity: 1; transform: translateY(0); }
        }

        /* Mobile Responsive */
        @media (max-width: 480px) {
            body {
                padding: 1rem;
            }

            .reset-password-container {
                padding: 2rem 1.5rem;
                box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);
            }

            h2 {
                font-size: 1.5rem;
            }
        }
    </style>
</head>

<body>
    <div class="reset-password-container">
        <h2>Reset Your Password</h2>

        @if($errors->any())
            <div class="alert alert-danger">{{ $errors->first() }}</div>
        @endif

        <form method="POST" action="{{ route('password.update') }}">
            @csrf
            <input type="hidden" name="token" value="{{ $token }}">
            <input type="hidden" name="email" value="{{ $email }}">

            <div class="form-group">
                <label for="password">New Password</label>
                <input type="password" id="password" name="password" placeholder="Enter new password" required>
            </div>

            <div class="form-group">
                <label for="password_confirmation">Confirm New Password</label>
                <input type="password" id="password_confirmation" name="password_confirmation" placeholder="Confirm password" required>
            </div>

            <button type="submit" class="btn">Reset Password</button>
        </form>
    </div>
</body>
</html>
