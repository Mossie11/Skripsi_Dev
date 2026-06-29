<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="color-scheme" content="light only">
    <title>Login – WR2School</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <style>
        *,
        *::before,
        *::after {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
        }

        body {
            font-family: 'Inter', sans-serif;
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            background: linear-gradient(135deg, #0C447C 0%, #1a5a9a 50%, #378ADD 100%);
            padding: 1rem;
        }

        body::before,
        body::after {
            content: '';
            position: fixed;
            border-radius: 50%;
            filter: blur(80px);
            opacity: 0.25;
            animation: float 8s ease-in-out infinite alternate;
            pointer-events: none;
        }

        body::before {
            width: 420px;
            height: 420px;
            background: radial-gradient(circle, #EF9F27, transparent);
            top: -80px;
            left: -80px;
        }

        body::after {
            width: 380px;
            height: 380px;
            background: radial-gradient(circle, #378ADD, transparent);
            bottom: -80px;
            right: -80px;
            animation-delay: -4s;
        }

        @keyframes float {
            from { transform: translate(0, 0) scale(1); }
            to { transform: translate(30px, 20px) scale(1.08); }
        }

        .card {
            position: relative;
            width: 100%;
            max-width: 420px;
            background: #ffffff;
            border: 1px solid #E6F1FB;
            border-radius: 20px;
            padding: 2.5rem 2.25rem;
            box-shadow: 0 25px 60px rgba(12, 68, 124, 0.3);
            animation: slideUp 0.5s cubic-bezier(0.22, 1, 0.36, 1) both;
        }

        @keyframes slideUp {
            from { opacity: 0; transform: translateY(30px); }
            to { opacity: 1; transform: translateY(0); }
        }

        .icon-wrap {
            width: 80px;
            height: 80px;
            margin: 0 auto 1.25rem;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .icon-wrap img {
            width: 100%;
            height: 100%;
            object-fit: contain;
        }

        h2 {
            color: #0C447C;
            font-size: 1.55rem;
            font-weight: 700;
            text-align: center;
            margin-bottom: 0.3rem;
            letter-spacing: -0.5px;
        }

        .subtitle {
            text-align: center;
            color: #7baada;
            font-size: 0.85rem;
            margin-bottom: 1.75rem;
        }

        .alert-error {
            display: flex;
            align-items: center;
            gap: 0.6rem;
            background: rgba(239, 68, 68, 0.08);
            border: 1px solid rgba(239, 68, 68, 0.3);
            color: #dc2626;
            padding: 0.75rem 1rem;
            border-radius: 10px;
            font-size: 0.875rem;
            margin-bottom: 1.25rem;
        }

        .alert-error svg {
            flex-shrink: 0;
            width: 17px;
            height: 17px;
            stroke: #dc2626;
            fill: none;
        }

        .alert-success {
            display: flex;
            align-items: center;
            gap: 0.6rem;
            background: rgba(40, 167, 69, 0.08);
            border: 1px solid rgba(40, 167, 69, 0.3);
            color: #28a745;
            padding: 0.75rem 1rem;
            border-radius: 10px;
            font-size: 0.875rem;
            margin-bottom: 1.25rem;
        }

        .alert-success svg {
            flex-shrink: 0;
            width: 17px;
            height: 17px;
            stroke: #28a745;
            fill: none;
        }

        .form-group {
            margin-bottom: 1.1rem;
        }

        label {
            display: block;
            color: #0C447C;
            font-size: 0.82rem;
            font-weight: 600;
            margin-bottom: 0.4rem;
            letter-spacing: 0.3px;
        }

        .input-wrap {
            position: relative;
        }

        .input-wrap svg.field-icon {
            position: absolute;
            left: 0.9rem;
            top: 50%;
            transform: translateY(-50%);
            width: 17px;
            height: 17px;
            stroke: #378ADD;
            fill: none;
            pointer-events: none;
            transition: stroke 0.2s;
        }

        .input-wrap:focus-within svg.field-icon {
            stroke: #0C447C;
        }

        .input-wrap input {
            width: 100%;
            padding: 0.72rem 0.9rem 0.72rem 2.6rem;
            background: #E6F1FB;
            border: 1.5px solid #E6F1FB;
            border-radius: 10px;
            color: #0C447C;
            font-size: 0.92rem;
            font-family: 'Inter', sans-serif;
            outline: none;
            transition: border-color 0.2s, background 0.2s, box-shadow 0.2s;
        }

        .input-wrap input::placeholder {
            color: #7baada;
        }

        .input-wrap input:focus {
            border-color: #378ADD;
            background: #fff;
            box-shadow: 0 0 0 3px rgba(55, 138, 221, 0.2);
        }

        .toggle-pw {
            position: absolute;
            right: 0.9rem;
            top: 50%;
            transform: translateY(-50%);
            background: none;
            border: none;
            cursor: pointer;
            padding: 0;
            display: flex;
            align-items: center;
        }

        .toggle-pw svg {
            width: 17px;
            height: 17px;
            stroke: #378ADD;
            fill: none;
            transition: stroke 0.2s;
        }

        .toggle-pw:hover svg {
            stroke: #0C447C;
        }

        .btn-primary {
            width: 100%;
            padding: 0.78rem;
            border: none;
            border-radius: 10px;
            background: linear-gradient(135deg, #0C447C, #378ADD);
            color: #fff;
            font-size: 0.95rem;
            font-weight: 600;
            font-family: 'Inter', sans-serif;
            cursor: pointer;
            letter-spacing: 0.3px;
            transition: opacity 0.2s, transform 0.15s, box-shadow 0.2s;
            box-shadow: 0 6px 20px rgba(12, 68, 124, 0.35);
            margin-top: 1.2rem;
        }

        .btn-primary:hover {
            opacity: 0.92;
            transform: translateY(-1px);
            box-shadow: 0 10px 28px rgba(12, 68, 124, 0.45);
        }

        .btn-primary:active {
            transform: translateY(0);
        }

        .forgot-link {
            display: block;
            text-align: center;
            margin-top: 1.2rem;
            color: #378ADD;
            font-size: 0.85rem;
            font-weight: 600;
            text-decoration: none;
            transition: color 0.2s;
        }

        .forgot-link:hover {
            color: #0C447C;
        }
    </style>
</head>

<body>
    <div class="card">
        <!-- Logo -->
        <div class="icon-wrap">
            <img src="{{ asset('logo-we2.png') }}" alt="WE2 School Logo">
        </div>

        <h2>Welcome Back</h2>
        <div class="subtitle">Masukkan username dan password untuk login</div>

        <!-- Error alert -->
        @if (session('error'))
            <div class="alert-error">
                <svg viewBox="0 0 24 24" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <circle cx="12" cy="12" r="10" />
                    <line x1="12" y1="8" x2="12" y2="12" />
                    <line x1="12" y1="16" x2="12.01" y2="16" />
                </svg>
                {{ session('error') }}
            </div>
        @endif

        @if (session('success'))
            <div class="alert-success">
                <svg viewBox="0 0 24 24" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M22 11.08V12a10 10 0 1 1-5.93-9.14" />
                    <polyline points="22 4 12 14.01 9 11.01" />
                </svg>
                {{ session('success') }}
            </div>
        @endif

        @if ($errors->any())
            <div class="alert-error">
                <svg viewBox="0 0 24 24" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <circle cx="12" cy="12" r="10" />
                    <line x1="12" y1="8" x2="12" y2="12" />
                    <line x1="12" y1="16" x2="12.01" y2="16" />
                </svg>
                {{ $errors->first() }}
            </div>
        @endif

        <form action="{{ route('login') }}" method="post">
            @csrf
            <!-- Username -->
            <div class="form-group">
                <label for="account">Username</label>
                <div class="input-wrap">
                    <svg class="field-icon" viewBox="0 0 24 24" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2" />
                        <circle cx="12" cy="7" r="4" />
                    </svg>
                    <input type="text" id="account" name="account" placeholder="Masukkan username" required value="{{ old('account') }}">
                </div>
            </div>

            <!-- Password -->
            <div class="form-group">
                <label for="password">Password</label>
                <div class="input-wrap">
                    <svg class="field-icon" viewBox="0 0 24 24" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <rect x="3" y="11" width="18" height="11" rx="2" ry="2" />
                        <path d="M7 11V7a5 5 0 0 1 10 0v4" />
                    </svg>
                    <input type="password" id="password" name="password" placeholder="Masukkan password" required>
                    <button type="button" class="toggle-pw" onclick="togglePw()" aria-label="Toggle password visibility">
                        <svg id="eyeIcon" viewBox="0 0 24 24" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z" />
                            <circle cx="12" cy="12" r="3" />
                        </svg>
                    </button>
                </div>
            </div>

            <button type="submit" class="btn-primary">Log In</button>
        </form>

        <a href="{{ route('forgot-password') }}" class="forgot-link">🔑 Lupa Password?</a>
    </div>

    <script>
        function togglePw() {
            const input = document.getElementById('password');
            const icon = document.getElementById('eyeIcon');
            if (input.type === 'password') {
                input.type = 'text';
                icon.innerHTML = `
                    <line x1="1" y1="1" x2="23" y2="23"/>
                    <path d="M17.94 17.94A10.07 10.07 0 0 1 12 20c-7 0-11-8-11-8a18.45 18.45 0 0 1 5.06-5.94"/>
                    <path d="M9.9 4.24A9.12 9.12 0 0 1 12 4c7 0 11 8 11 8a18.5 18.5 0 0 1-2.16 3.19"/>
                    <line x1="1" y1="1" x2="23" y2="23"/>`;
            } else {
                input.type = 'password';
                icon.innerHTML = `
                    <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/>
                    <circle cx="12" cy="12" r="3"/>`;
            }
        }
    </script>
</body>

</html>