</body>
</html>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reset Password</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            height: 100vh;
            display: flex;
            justify-content: center;
            align-items: center;
            overflow: hidden;
            position: relative;
        }

        /* Animated Background Circles */
        .bg-animation {
            position: absolute;
            width: 100%;
            height: 100%;
            overflow: hidden;
            z-index: 0;
        }

        .circle {
            position: absolute;
            border-radius: 50%;
            background: rgba(255, 255, 255, 0.1);
            animation: float 15s infinite ease-in-out;
        }

        .circle:nth-child(1) {
            width: 80px;
            height: 80px;
            top: 10%;
            left: 20%;
            animation-delay: 0s;
        }

        .circle:nth-child(2) {
            width: 60px;
            height: 60px;
            top: 60%;
            left: 80%;
            animation-delay: 2s;
        }

        .circle:nth-child(3) {
            width: 100px;
            height: 100px;
            top: 80%;
            left: 10%;
            animation-delay: 4s;
        }

        .circle:nth-child(4) {
            width: 50px;
            height: 50px;
            top: 30%;
            left: 70%;
            animation-delay: 1s;
        }

        @keyframes float {
            0%, 100% {
                transform: translateY(0) rotate(0deg);
                opacity: 0.5;
            }
            50% {
                transform: translateY(-30px) rotate(180deg);
                opacity: 0.8;
            }
        }

        /* Card Container */
        .card {
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(10px);
            padding: 35px 30px;
            border-radius: 25px;
            box-shadow: 0 25px 50px rgba(0, 0, 0, 0.3);
            max-width: 420px;
            width: 90%;
            position: relative;
            z-index: 1;
            animation: slideUp 0.8s cubic-bezier(0.68, -0.55, 0.265, 1.55);
        }

        @keyframes slideUp {
            from {
                opacity: 0;
                transform: translateY(50px) scale(0.9);
            }
            to {
                opacity: 1;
                transform: translateY(0) scale(1);
            }
        }

        /* Lock Icon */
        .lock-icon {
            width: 70px;
            height: 70px;
            margin: 0 auto 20px;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            animation: scaleIn 0.6s cubic-bezier(0.68, -0.55, 0.265, 1.55) 0.3s backwards;
            box-shadow: 0 10px 30px rgba(102, 126, 234, 0.4);
            position: relative;
        }

        @keyframes scaleIn {
            from {
                transform: scale(0) rotate(-180deg);
                opacity: 0;
            }
            to {
                transform: scale(1) rotate(0deg);
                opacity: 1;
            }
        }

        .lock-icon::before {
            content: '🔒';
            font-size: 32px;
            animation: bounce 2s infinite;
        }

        @keyframes bounce {
            0%, 100% {
                transform: translateY(0);
            }
            50% {
                transform: translateY(-5px);
            }
        }

        /* Title */
        h2 {
            text-align: center;
            font-size: 24px;
            font-weight: 700;
            margin-bottom: 8px;
            animation: fadeIn 0.8s ease 0.5s backwards;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
        }

        .subtitle {
            text-align: center;
            font-size: 13px;
            color: #6b7280;
            margin-bottom: 25px;
            animation: fadeIn 0.8s ease 0.7s backwards;
        }

        @keyframes fadeIn {
            from {
                opacity: 0;
                transform: translateY(10px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        /* Form Styling */
        form {
            animation: fadeIn 0.8s ease 0.9s backwards;
        }

        .input-group {
            position: relative;
            margin-bottom: 18px;
        }

        label {
            display: block;
            font-size: 13px;
            font-weight: 600;
            color: #374151;
            margin-bottom: 6px;
        }

        input[type="email"],
        input[type="password"],
        input[type="text"] {
            width: 100%;
            padding: 12px 40px 12px 14px;
            border-radius: 12px;
            border: 2px solid #e5e7eb;
            font-size: 14px;
            transition: all 0.3s ease;
            background: white;
        }

        input[type="email"]:read-only {
            background-color: #f9fafb;
            color: #6b7280;
            cursor: not-allowed;
        }

        input:focus {
            outline: none;
            border-color: #667eea;
            box-shadow: 0 0 0 3px rgba(102, 126, 234, 0.1);
        }

        .toggle-password {
            position: absolute;
            right: 14px;
            top: 0;
            bottom: 0;
            height: 100%;
            display: flex;
            align-items: center;
            font-size: 16px;
            color: #6b7280;
            cursor: pointer;
            transition: all 0.2s ease;
        }

        .toggle-password:hover {
            color: #667eea;
            transform: scale(1.1);   /* ✅ hapus translateY */
        }

        .toggle-password:active {
            transform: scale(0.95);  /* ✅ hapus translateY */
        }

        /* Password Strength Indicator */
        .password-strength {
            margin-top: 8px;
            height: 4px;
            background: #e5e7eb;
            border-radius: 2px;
            overflow: hidden;
            opacity: 0;
            transition: opacity 0.3s ease;
        }

        .input-wrapper {
            position: relative;
            display: flex;
            align-items: center;   /* ini yang bikin icon center Y */
        }

        .input-wrapper input {
            width: 100%;
            padding-right: 40px;   /* kasih ruang buat icon mata */
        }

        .toggle-password {
            position: absolute;
            right: 14px;
            font-size: 16px;
            color: #6b7280;
            cursor: pointer;
            display: flex;
            align-items: center;
            height: 100%;          /* ikut tinggi input, bukan ikut label */
        }

        .password-strength.show {
            opacity: 1;
        }

        .strength-bar {
            height: 100%;
            width: 0;
            transition: all 0.3s ease;
            border-radius: 2px;
        }

        .strength-bar.weak {
            width: 33%;
            background: #ef4444;
        }

        .strength-bar.medium {
            width: 66%;
            background: #f59e0b;
        }

        .strength-bar.strong {
            width: 100%;
            background: #10b981;
        }

        .strength-text {
            font-size: 12px;
            margin-top: 5px;
            color: #6b7280;
        }

        /* Button */
        button[type="submit"] {
            width: 100%;
            padding: 12px;
            margin-top: 20px;
            border: none;
            border-radius: 12px;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            font-size: 15px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
            box-shadow: 0 4px 15px rgba(102, 126, 234, 0.4);
            position: relative;
            overflow: hidden;
        }

        button[type="submit"]:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(102, 126, 234, 0.5);
        }

        button[type="submit"]:active {
            transform: translateY(0);
        }

        button[type="submit"]::before {
            content: '';
            position: absolute;
            top: 50%;
            left: 50%;
            width: 0;
            height: 0;
            border-radius: 50%;
            background: rgba(255, 255, 255, 0.2);
            transform: translate(-50%, -50%);
            transition: width 0.6s, height 0.6s;
        }

        button[type="submit"]:hover::before {
            width: 300px;
            height: 300px;
        }

        button[type="submit"] span {
            position: relative;
            z-index: 1;
        }

        /* Error Messages */
        .error-messages {
            background: linear-gradient(135deg, #fee2e2 0%, #fecaca 100%);
            border-left: 4px solid #ef4444;
            padding: 15px 20px;
            border-radius: 10px;
            margin-top: 20px;
            animation: shake 0.5s ease;
        }

        @keyframes shake {
            0%, 100% { transform: translateX(0); }
            25% { transform: translateX(-5px); }
            75% { transform: translateX(5px); }
        }

        .error-messages ul {
            list-style: none;
            padding: 0;
            margin: 0;
        }

        .error-messages li {
            color: #dc2626;
            font-size: 14px;
            margin-bottom: 5px;
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .error-messages li::before {
            content: '⚠️';
            font-size: 14px;
        }

        .error-messages li:last-child {
            margin-bottom: 0;
        }

        /* Requirements Box */
        .requirements {
            background: linear-gradient(135deg, #eff6ff 0%, #dbeafe 100%);
            border-left: 4px solid #3b82f6;
            padding: 14px 18px;
            border-radius: 10px;
            margin-top: 18px;
            font-size: 12px;
        }

        .requirements-title {
            font-weight: 600;
            color: #1e40af;
            margin-bottom: 6px;
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .requirements ul {
            list-style: none;
            padding: 0;
            margin: 0;
        }

        .requirements li {
            color: #1e40af;
            margin-bottom: 4px;
            padding-left: 20px;
            position: relative;
        }

        .requirements li::before {
            content: '✓';
            position: absolute;
            left: 0;
            color: #3b82f6;
            font-weight: bold;
        }

        /* Responsive Design */
        @media only screen and (max-width: 480px) {
            .card {
                padding: 30px 22px;
            }

            .lock-icon {
                width: 60px;
                height: 60px;
            }

            .lock-icon::before {
                font-size: 28px;
            }

            h2 {
                font-size: 22px;
            }

            .subtitle {
                font-size: 12px;
            }

            input[type="email"],
            input[type="password"],
            input[type="text"] {
                padding: 11px 38px 11px 12px;
                font-size: 13px;
            }

            button[type="submit"] {
                padding: 11px;
                font-size: 14px;
            }
        }
    </style>
</head>
<body>
    <!-- Animated Background -->
    <div class="bg-animation">
        <div class="circle"></div>
        <div class="circle"></div>
        <div class="circle"></div>
        <div class="circle"></div>
    </div>

    <!-- Main Card -->
    <div class="card">
        <!-- Lock Icon -->
        <div class="lock-icon"></div>

        <!-- Title -->
        <h2>Reset Password</h2>
        <div class="subtitle">Buat password baru yang kuat dan aman</div>

        <!-- Form -->
        <form method="POST" action="{{ route('password.update') }}">
            @csrf
            <input type="hidden" name="token" value="{{ $token }}">

            <div class="input-group">
                <label for="email">Email</label>
                <input type="email" name="email" id="email" value="{{ $email }}" readonly>
            </div>

            <div class="input-group">
                <label for="password">Password Baru</label>
                <div class="input-wrapper">
                    <input type="password" name="password" id="password" placeholder="Masukkan password baru" required>
                    <i class="fas fa-eye toggle-password" onclick="togglePassword('password')" id="toggleIcon1"></i>
                </div>
                <div class="password-strength" id="passwordStrength">
                    <div class="strength-bar" id="strengthBar"></div>
                </div>
                <div class="strength-text" id="strengthText"></div>
            </div>

            <div class="input-group">
                <label for="password_confirmation">Konfirmasi Password</label>
                <div class="input-wrapper">
                    <input type="password" name="password_confirmation" id="password_confirmation" placeholder="Ketik ulang password baru" required>
                    <i class="fas fa-eye toggle-password" onclick="togglePassword('password_confirmation')" id="toggleIcon2"></i>
                </div>
            </div>

            <button type="submit">
                <span>Reset Password</span>
            </button>
        </form>

        <!-- Error Messages -->
        @if ($errors->any())
            <div class="error-messages">
                <ul>
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif
    </div>

    <script>
        function togglePassword(id) {
            const input = document.getElementById(id);
            const icon = id === 'password' ? document.getElementById('toggleIcon1') : document.getElementById('toggleIcon2');

            if (input.getAttribute('type') === 'password') {
                input.setAttribute('type', 'text');
                icon.classList.remove('fa-eye');
                icon.classList.add('fa-eye-slash');
            } else {
                input.setAttribute('type', 'password');
                icon.classList.remove('fa-eye-slash');
                icon.classList.add('fa-eye');
            }
        }

        // Password strength checker
        const passwordInput = document.getElementById('password');
        const strengthBar = document.getElementById('strengthBar');
        const strengthText = document.getElementById('strengthText');
        const passwordStrength = document.getElementById('passwordStrength');

        passwordInput.addEventListener('input', function() {
            const password = this.value;
            const strength = checkPasswordStrength(password);

            if (password.length === 0) {
                passwordStrength.classList.remove('show');
                strengthText.textContent = '';
                return;
            }

            passwordStrength.classList.add('show');
            strengthBar.className = 'strength-bar';

            if (strength.score < 3) {
                strengthBar.classList.add('weak');
                strengthText.textContent = 'Password lemah';
                strengthText.style.color = '#ef4444';
            } else if (strength.score < 4) {
                strengthBar.classList.add('medium');
                strengthText.textContent = 'Password sedang';
                strengthText.style.color = '#f59e0b';
            } else {
                strengthBar.classList.add('strong');
                strengthText.textContent = 'Password kuat';
                strengthText.style.color = '#10b981';
            }
        });

        function checkPasswordStrength(password) {
            let score = 0;

            if (password.length >= 8) score++;
            if (password.length >= 12) score++;
            if (/[a-z]/.test(password) && /[A-Z]/.test(password)) score++;
            if (/\d/.test(password)) score++;
            if (/[^a-zA-Z\d]/.test(password)) score++;

            return { score };
        }

        // Form submission animation
        document.querySelector('form').addEventListener('submit', function() {
            const button = this.querySelector('button[type="submit"]');
            button.textContent = 'Memproses...';
            button.style.opacity = '0.7';
        });
    </script>
</body>
</html>
