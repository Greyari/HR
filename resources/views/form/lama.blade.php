<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reset Password</title>
    <style>
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: linear-gradient(135deg, #71b7e6, #9b59b6);
            height: 100vh;
            margin: 0;
            display: flex;
            justify-content: center;
            align-items: center;
        }

        .container {
            background-color: white;
            padding: 40px 30px;
            border-radius: 12px;
            box-shadow: 0 10px 25px rgba(0,0,0,0.2);
            width: 100%;
            max-width: 400px;
            text-align: center;
        }

        h2 {
            margin-bottom: 25px;
            color: #333;
        }

        .input-group {
            position: relative;
            margin: 10px 0;
        }

        input[type="email"],
        input[type="password"] {
            width: 100%;
            padding: 12px 40px 12px 15px; /* sisakan ruang untuk ikon */
            border-radius: 8px;
            border: 1px solid #ccc;
            font-size: 16px;
        }

        input[type="email"]:read-only {
            background-color: #f5f5f5;
        }

        .toggle-password {
            position: absolute;
            top: 50%;
            right: 12px;
            transform: translateY(-50%);
            cursor: pointer;
            font-size: 18px;
            color: #888;
        }

        button {
            width: 100%;
            padding: 12px;
            margin-top: 15px;
            border: none;
            border-radius: 8px;
            background-color: #6c63ff;
            color: white;
            font-size: 16px;
            cursor: pointer;
            transition: 0.3s;
        }

        button:hover {
            background-color: #574fd6;
        }

        .error-messages {
            text-align: left;
            margin-top: 15px;
            color: #e74c3c;
        }

        .error-messages ul {
            padding-left: 20px;
            margin: 0;
        }
    </style>
</head>
<body>

<div class="container">
    <h2>Reset Password</h2>

    <form method="POST" action="{{ route('password.update') }}">
        @csrf
        <input type="hidden" name="token" value="{{ $token }}">
        <input type="email" name="email" value="{{ $email }}" readonly>

        <div class="input-group">
            <input type="password" name="password" placeholder="Password Baru" required id="password">
            <span class="toggle-password" onclick="togglePassword('password')">👁️</span>
        </div>

        <div class="input-group">
            <input type="password" name="password_confirmation" placeholder="Konfirmasi Password" required id="password_confirmation">
            <span class="toggle-password" onclick="togglePassword('password_confirmation')">👁️</span>
        </div>

        <button type="submit">Reset Password</button>
    </form>

    @if(isset($errors) && $errors->any())
        <div class="error-messages">
            <ul>
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif
</div>

<script>
function togglePassword(id) {
    const input = document.getElementById(id);
    const type = input.getAttribute('type') === 'password' ? 'text' : 'password';
    input.setAttribute('type', type);
}
</script>
